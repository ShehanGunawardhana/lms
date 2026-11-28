<?php

declare(strict_types=1);

namespace App\Livewire\Student;

use App\Enums\QuestionType;
use App\Enums\QuizAttemptStatus;
use App\Enums\QuizShowAnswersPolicy;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class QuizAttemptRunner extends Component
{
    use AuthorizesRequests;

    public Quiz $quiz;

    public ?QuizAttempt $attempt = null;

    /**
     * Active question index in the sequence (0-based).
     */
    public int $activeQuestionIndex = 0;

    /**
     * Active question currently viewed by the student.
     */
    public ?Question $currentQuestion = null;

    /**
     * Active question's current answer state in the UI.
     */
    public ?int $selectedOptionId = null;

    /**
     * @var array<int, int>
     */
    public array $selectedOptionIds = [];

    public string $textAnswer = '';

    /**
     * @var array<int, int>
     */
    public array $orderingAnswer = [];

    /**
     * @var array<int, int>
     */
    public array $matchingAnswer = [];

    public ?int $selectedMatchingLeftId = null;

    public bool $isFlagged = false;

    /**
     * Modals and view states
     */
    public bool $showFinishModal = false;

    public bool $showQuitModal = false;

    public string $errorMessage = '';

    public string $successMessage = '';

    /**
     * Mount component with Quiz and optional existing Attempt.
     */
    public function mount(Quiz $quiz, ?QuizAttempt $attempt = null): void
    {
        /** @var User $user */
        $user = auth()->user();

        $institutionId = current_institution_id() ?? $user->institution_id;

        if ($quiz->institution_id !== (int) $user->institution_id || $quiz->institution_id !== (int) $institutionId) {
            abort(404);
        }

        $this->quiz = $quiz;

        if ($attempt && $attempt->exists) {
            if ($attempt->quiz_id !== $quiz->id || $attempt->institution_id !== $institutionId) {
                abort(404);
            }

            // Verify user can view this attempt
            if ((int) $attempt->user_id !== (int) $user->id && ! $user->hasAnyRole(['super_admin', 'admin', 'teacher'])) {
                abort(403, __('You are not authorized to view this quiz attempt.'));
            }

            $this->attempt = $attempt;
        } else {
            // Check if student has an existing in-progress attempt
            $activeAttempt = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('user_id', $user->id)
                ->where('status', QuizAttemptStatus::InProgress)
                ->latest()
                ->first();

            if ($activeAttempt) {
                if ($activeAttempt->hasTimeExpired()) {
                    $activeAttempt->finalizeAttempt(true);
                } else {
                    $this->attempt = $activeAttempt;
                }
            }
        }

        if ($this->attempt && $this->attempt->isInProgress()) {
            $this->loadQuestionAt($this->activeQuestionIndex);
        }
    }

    /**
     * Start a new attempt or resume an active one.
     */
    public function startAttempt(): void
    {
        $this->errorMessage = '';

        /** @var User $user */
        $user = auth()->user();

        // 1. Availability check
        if (! $this->quiz->isAvailable()) {
            $this->errorMessage = __('This quiz is currently not available for attempts.');

            return;
        }

        // 2. Resume check
        if ($this->attempt && $this->attempt->isInProgress()) {
            if ($this->attempt->hasTimeExpired()) {
                $this->attempt->finalizeAttempt(true);
                $this->errorMessage = __('Your previous attempt time expired.');

                return;
            }

            $this->loadQuestionAt(0);

            return;
        }

        // 3. Max attempt limit check
        if (! $this->quiz->canUserAttempt($user->id)) {
            $this->errorMessage = __('You have reached the maximum allowed attempts (:max) for this quiz.', [
                'max' => $this->quiz->max_attempts ?? 1,
            ]);

            return;
        }

        // 4. Determine questions and build sequence
        $quizQuestions = $this->quiz->quizQuestions()
            ->with(['question.options'])
            ->orderBy('order_position')
            ->get();

        if ($quizQuestions->isEmpty()) {
            $this->errorMessage = __('This quiz does not have any questions assigned yet.');

            return;
        }

        $questionIds = $quizQuestions->pluck('question_id')->all();

        // Shuffling questions if configured
        if ($this->quiz->shuffle_questions) {
            shuffle($questionIds);
        }

        // Shuffling options if configured & shuffling matching targets
        $optionsOrder = [];
        foreach ($quizQuestions as $qq) {
            if (! $qq->question || $qq->question->options->isEmpty()) {
                continue;
            }

            if ($qq->question->type === QuestionType::Matching) {
                $leftIds = $qq->question->options->pluck('id')->map(fn ($id) => (int) $id)->all();
                if ($this->quiz->shuffle_options) {
                    shuffle($leftIds);
                }
                $rightIds = $qq->question->options->pluck('id')->map(fn ($id) => (int) $id)->all();
                if (count($rightIds) > 1) {
                    $attempts = 0;
                    do {
                        shuffle($rightIds);
                        $attempts++;
                    } while ($rightIds === $leftIds && $attempts < 5);
                }
                $optionsOrder[(string) $qq->question->id] = [
                    'left' => $leftIds,
                    'right' => $rightIds,
                ];
            } elseif ($this->quiz->shuffle_options) {
                $optIds = $qq->question->options->pluck('id')->all();
                shuffle($optIds);
                $optionsOrder[(string) $qq->question->id] = $optIds;
            }
        }

        $attemptNumber = $this->quiz->getAttemptsCountForUser($user->id) + 1;

        $newAttempt = QuizAttempt::create([
            'institution_id' => $this->quiz->institution_id,
            'quiz_id' => $this->quiz->id,
            'user_id' => $user->id,
            'attempt_number' => $attemptNumber,
            'status' => QuizAttemptStatus::InProgress,
            'started_at' => now(),
            'question_order' => $questionIds,
            'options_order' => $optionsOrder,
            'time_spent_seconds' => 0,
        ]);

        // Pre-create answer records for the attempt
        foreach ($quizQuestions as $qq) {
            $q = $qq->question;
            if (! $q) {
                continue;
            }

            $initialOrdering = null;
            if ($q->type === QuestionType::Ordering) {
                // Initialize ordering answer with the options sequence (shuffled or default)
                $opts = $q->options;
                if (isset($optionsOrder[(string) $q->id])) {
                    $initialOrdering = $optionsOrder[(string) $q->id];
                } else {
                    $initialOrdering = $opts->sortBy('order_position')->pluck('id')->all();
                }
            }

            QuizAttemptAnswer::create([
                'quiz_attempt_id' => $newAttempt->id,
                'question_id' => $q->id,
                'ordering_answer' => $initialOrdering,
            ]);
        }

        $this->attempt = $newAttempt->fresh(['answers', 'quiz']);
        $this->activeQuestionIndex = 0;
        $this->loadQuestionAt(0);
    }

    /**
     * Load the question at given index into active runner state.
     */
    public function loadQuestionAt(int $index): void
    {
        if (! $this->attempt || ! $this->attempt->isInProgress()) {
            return;
        }

        // Check if time expired
        if ($this->attempt->hasTimeExpired()) {
            $this->autoSubmitTimedOut();

            return;
        }

        $sequence = $this->attempt->getQuestionsSequence();
        if ($sequence->isEmpty()) {
            return;
        }

        if ($index < 0) {
            $index = 0;
        } elseif ($index >= $sequence->count()) {
            $index = $sequence->count() - 1;
        }

        $this->activeQuestionIndex = $index;
        $this->currentQuestion = $sequence->values()->get($index);

        if (! $this->currentQuestion) {
            return;
        }

        $answer = QuizAttemptAnswer::where('quiz_attempt_id', $this->attempt->id)
            ->where('question_id', $this->currentQuestion->id)
            ->first();

        if ($answer) {
            $this->selectedOptionId = $answer->selected_option_id;
            $this->selectedOptionIds = (array) ($answer->selected_option_ids ?? []);
            $this->textAnswer = (string) ($answer->text_answer ?? '');
            $this->orderingAnswer = (array) ($answer->ordering_answer ?? []);
            $this->isFlagged = (bool) $answer->is_flagged;

            $rawMatching = (array) ($answer->matching_answer ?? []);
            $normalized = [];
            foreach ($rawMatching as $k => $v) {
                if ($v !== null && $v !== '') {
                    $normalized[(int) $k] = (int) $v;
                }
            }
            $this->matchingAnswer = $normalized;
            $this->selectedMatchingLeftId = null;

            // Ensure ordering options initialized
            if ($this->currentQuestion->type === QuestionType::Ordering && empty($this->orderingAnswer)) {
                $this->orderingAnswer = $this->getOrderedOptionIds($this->currentQuestion);
                $answer->ordering_answer = $this->orderingAnswer;
                $answer->save();
            }
        } else {
            $this->selectedOptionId = null;
            $this->selectedOptionIds = [];
            $this->textAnswer = '';
            $this->orderingAnswer = $this->currentQuestion->type === QuestionType::Ordering
                ? $this->getOrderedOptionIds($this->currentQuestion)
                : [];
            $this->matchingAnswer = [];
            $this->selectedMatchingLeftId = null;
            $this->isFlagged = false;
        }
    }

    /**
     * Retrieve ordered options for the question taking options_order into account.
     *
     * @return Collection<int, QuestionOption>
     */
    public function getQuestionOptionsProperty(): Collection
    {
        if (! $this->currentQuestion) {
            return new Collection;
        }

        $options = $this->currentQuestion->options;
        if ($options->isEmpty()) {
            return new Collection;
        }

        $optionsOrderMap = $this->attempt ? $this->attempt->options_order : null;
        $qId = $this->currentQuestion->id;
        if (is_array($optionsOrderMap) && isset($optionsOrderMap[$qId]) && is_array($optionsOrderMap[$qId])) {
            $rawOrder = $optionsOrderMap[$qId];
            /** @var array<int, int> $optionsOrder */
            $optionsOrder = isset($rawOrder['left']) && is_array($rawOrder['left'])
                ? $rawOrder['left']
                : $rawOrder;

            $map = $options->keyBy('id');
            $sorted = new Collection;
            foreach ($optionsOrder as $optId) {
                if ($map->has($optId)) {
                    $sorted->push($map->get($optId));
                }
            }

            return $sorted;
        }

        return $options->sortBy('order_position')->values();
    }

    /**
     * @return array<int, int>
     */
    private function getOrderedOptionIds(Question $question): array
    {
        $optionsOrderMap = $this->attempt ? $this->attempt->options_order : null;
        $qId = $question->id;
        if (is_array($optionsOrderMap) && isset($optionsOrderMap[$qId]) && is_array($optionsOrderMap[$qId])) {
            $rawOrder = $optionsOrderMap[$qId];
            if (isset($rawOrder['left']) && is_array($rawOrder['left'])) {
                return array_map('intval', $rawOrder['left']);
            }

            return array_map('intval', $rawOrder);
        }

        return $question->options->sortBy('order_position')->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * Select single option (Single Choice, True/False, Picture Choice).
     */
    public function selectSingleOption(int $optionId): void
    {
        if (! $this->attempt || ! $this->attempt->isInProgress() || ! $this->currentQuestion) {
            return;
        }

        if ($this->attempt->hasTimeExpired()) {
            $this->autoSubmitTimedOut();

            return;
        }

        $this->selectedOptionId = $optionId;

        $answer = QuizAttemptAnswer::firstOrNew([
            'quiz_attempt_id' => $this->attempt->id,
            'question_id' => $this->currentQuestion->id,
        ]);

        $answer->selected_option_id = $optionId;
        $answer->save();
    }

    /**
     * Toggle option in multiple choice.
     */
    public function toggleMultipleOption(int $optionId): void
    {
        if (! $this->attempt || ! $this->attempt->isInProgress() || ! $this->currentQuestion) {
            return;
        }

        if ($this->attempt->hasTimeExpired()) {
            $this->autoSubmitTimedOut();

            return;
        }

        if (in_array($optionId, $this->selectedOptionIds, true)) {
            $this->selectedOptionIds = array_values(array_filter(
                $this->selectedOptionIds,
                fn (int $id) => $id !== $optionId
            ));
        } else {
            $this->selectedOptionIds[] = $optionId;
        }

        $answer = QuizAttemptAnswer::firstOrNew([
            'quiz_attempt_id' => $this->attempt->id,
            'question_id' => $this->currentQuestion->id,
        ]);

        $answer->selected_option_ids = $this->selectedOptionIds;
        $answer->save();
    }

    /**
     * Save text answer (Fill in the Blank, Short Answer).
     */
    public function saveTextAnswer(): void
    {
        if (! $this->attempt || ! $this->attempt->isInProgress() || ! $this->currentQuestion) {
            return;
        }

        if ($this->attempt->hasTimeExpired()) {
            $this->autoSubmitTimedOut();

            return;
        }

        $answer = QuizAttemptAnswer::firstOrNew([
            'quiz_attempt_id' => $this->attempt->id,
            'question_id' => $this->currentQuestion->id,
        ]);

        $answer->text_answer = $this->textAnswer;
        $answer->save();
    }

    /**
     * Move ordering item up.
     */
    public function moveOrderItemUp(int $index): void
    {
        if (! $this->attempt || ! $this->attempt->isInProgress() || ! $this->currentQuestion) {
            return;
        }

        if ($index <= 0 || ! isset($this->orderingAnswer[$index]) || ! isset($this->orderingAnswer[$index - 1])) {
            return;
        }

        $temp = $this->orderingAnswer[$index - 1];
        $this->orderingAnswer[$index - 1] = $this->orderingAnswer[$index];
        $this->orderingAnswer[$index] = $temp;

        $this->saveOrderingAnswer();
    }

    /**
     * Move ordering item down.
     */
    public function moveOrderItemDown(int $index): void
    {
        if (! $this->attempt || ! $this->attempt->isInProgress() || ! $this->currentQuestion) {
            return;
        }

        $count = count($this->orderingAnswer);
        if ($index >= $count - 1 || ! isset($this->orderingAnswer[$index]) || ! isset($this->orderingAnswer[$index + 1])) {
            return;
        }

        $temp = $this->orderingAnswer[$index + 1];
        $this->orderingAnswer[$index + 1] = $this->orderingAnswer[$index];
        $this->orderingAnswer[$index] = $temp;

        $this->saveOrderingAnswer();
    }

    private function saveOrderingAnswer(): void
    {
        $answer = QuizAttemptAnswer::firstOrNew([
            'quiz_attempt_id' => $this->attempt->id,
            'question_id' => $this->currentQuestion->id,
        ]);

        $answer->ordering_answer = array_values($this->orderingAnswer);
        $answer->save();
    }

    /**
     * Pair a left premise with a right target.
     */
    public function pairMatching(int $leftId, int $rightId): void
    {
        if (! $this->attempt || ! $this->attempt->isInProgress() || ! $this->currentQuestion) {
            return;
        }

        if ($this->attempt->hasTimeExpired()) {
            $this->autoSubmitTimedOut();

            return;
        }

        $this->matchingAnswer[$leftId] = $rightId;
        $this->selectedMatchingLeftId = null;

        $answer = QuizAttemptAnswer::firstOrNew([
            'quiz_attempt_id' => $this->attempt->id,
            'question_id' => $this->currentQuestion->id,
        ]);

        $answer->matching_answer = $this->matchingAnswer;
        $answer->save();
    }

    /**
     * Unpair a left premise.
     */
    public function unpairMatching(int $leftId): void
    {
        if (! $this->attempt || ! $this->attempt->isInProgress() || ! $this->currentQuestion) {
            return;
        }

        if ($this->attempt->hasTimeExpired()) {
            $this->autoSubmitTimedOut();

            return;
        }

        if (isset($this->matchingAnswer[$leftId])) {
            unset($this->matchingAnswer[$leftId]);
        }

        if ($this->selectedMatchingLeftId === $leftId) {
            $this->selectedMatchingLeftId = null;
        }

        $answer = QuizAttemptAnswer::firstOrNew([
            'quiz_attempt_id' => $this->attempt->id,
            'question_id' => $this->currentQuestion->id,
        ]);

        $answer->matching_answer = $this->matchingAnswer;
        $answer->save();
    }

    /**
     * Select a left premise for tap-to-pair mode.
     */
    public function selectMatchingLeft(int $leftId): void
    {
        if ($this->selectedMatchingLeftId === $leftId) {
            $this->selectedMatchingLeftId = null;
        } else {
            $this->selectedMatchingLeftId = $leftId;
        }
    }

    /**
     * Select a right target in tap-to-pair mode (pairs if left is selected).
     */
    public function selectMatchingRight(int $rightId): void
    {
        if ($this->selectedMatchingLeftId !== null) {
            $this->pairMatching($this->selectedMatchingLeftId, $rightId);
        }
    }

    /**
     * Reset/clear all matching connections for the question.
     */
    public function resetMatching(): void
    {
        if (! $this->attempt || ! $this->attempt->isInProgress() || ! $this->currentQuestion) {
            return;
        }

        if ($this->attempt->hasTimeExpired()) {
            $this->autoSubmitTimedOut();

            return;
        }

        $this->matchingAnswer = [];
        $this->selectedMatchingLeftId = null;

        $answer = QuizAttemptAnswer::firstOrNew([
            'quiz_attempt_id' => $this->attempt->id,
            'question_id' => $this->currentQuestion->id,
        ]);

        $answer->matching_answer = [];
        $answer->save();
    }

    /**
     * Toggle flagged status for review.
     */
    public function toggleFlag(): void
    {
        if (! $this->attempt || ! $this->attempt->isInProgress() || ! $this->currentQuestion) {
            return;
        }

        $this->isFlagged = ! $this->isFlagged;

        $answer = QuizAttemptAnswer::firstOrNew([
            'quiz_attempt_id' => $this->attempt->id,
            'question_id' => $this->currentQuestion->id,
        ]);

        $answer->is_flagged = $this->isFlagged;
        $answer->save();
    }

    /**
     * Navigate to previous question.
     */
    public function previousQuestion(): void
    {
        if ($this->activeQuestionIndex > 0) {
            $this->loadQuestionAt($this->activeQuestionIndex - 1);
        }
    }

    /**
     * Navigate to next question.
     */
    public function nextQuestion(): void
    {
        $sequence = $this->attempt?->getQuestionsSequence() ?? new Collection;
        if ($this->activeQuestionIndex < $sequence->count() - 1) {
            $this->loadQuestionAt($this->activeQuestionIndex + 1);
        } else {
            $this->showFinishModal = true;
        }
    }

    /**
     * Jump to specific question index.
     */
    public function goToQuestion(int $index): void
    {
        $this->loadQuestionAt($index);
    }

    /**
     * Submit and finalize attempt.
     */
    public function submitAttempt(): void
    {
        if (! $this->attempt || ! $this->attempt->isInProgress()) {
            return;
        }

        $isTimedOut = $this->attempt->hasTimeExpired();

        $this->attempt->finalizeAttempt($isTimedOut);
        $this->attempt = $this->attempt->fresh(['answers.question.options', 'quiz']);
        $this->showFinishModal = false;

        $this->successMessage = __('Your quiz attempt has been submitted successfully.');
    }

    /**
     * Triggered by client countdown when remaining seconds hit 0.
     */
    public function autoSubmitTimedOut(): void
    {
        if (! $this->attempt || ! $this->attempt->isInProgress()) {
            return;
        }

        $this->attempt->finalizeAttempt(true);
        $this->attempt = $this->attempt->fresh(['answers.question.options', 'quiz']);
        $this->showFinishModal = false;

        $this->errorMessage = __('Time has expired! Your attempt was automatically submitted.');
    }

    /**
     * Get summary metrics for the attempt questions (answered, flagged, total).
     *
     * @return array{total: int, answered: int, unanswered: int, flagged: int, items: array<int, array{index: int, question_id: int, is_answered: bool, is_flagged: bool, is_active: bool}>}
     */
    public function getNavigatorDataProperty(): array
    {
        if (! $this->attempt) {
            return ['total' => 0, 'answered' => 0, 'unanswered' => 0, 'flagged' => 0, 'items' => []];
        }

        $sequence = $this->attempt->getQuestionsSequence();
        $answers = $this->attempt->answers->keyBy('question_id');

        $total = $sequence->count();
        $answeredCount = 0;
        $flaggedCount = 0;
        $items = [];

        foreach ($sequence->values() as $idx => $question) {
            $ans = $answers->get($question->id);
            $isAnswered = $ans ? $ans->isAnswered() : false;
            $isFlagged = $ans ? (bool) $ans->is_flagged : false;

            if ($isAnswered) {
                $answeredCount++;
            }
            if ($isFlagged) {
                $flaggedCount++;
            }

            $items[] = [
                'index' => $idx,
                'question_id' => $question->id,
                'is_answered' => $isAnswered,
                'is_flagged' => $isFlagged,
                'is_active' => $idx === $this->activeQuestionIndex,
            ];
        }

        return [
            'total' => $total,
            'answered' => $answeredCount,
            'unanswered' => max(0, $total - $answeredCount),
            'flagged' => $flaggedCount,
            'items' => $items,
        ];
    }

    /**
     * @return array{left: array<int, array{id: int, text: string, image_url: ?string}>, right: array<int, array{id: int, text: string, image_url: ?string}>}
     */
    public function getMatchingPairsProperty(): array
    {
        if (! $this->currentQuestion || $this->currentQuestion->type !== QuestionType::Matching) {
            return ['left' => [], 'right' => []];
        }

        $options = $this->currentQuestion->options;
        if ($options->isEmpty()) {
            return ['left' => [], 'right' => []];
        }

        $optionsOrderMap = $this->attempt ? $this->attempt->options_order : null;
        $qId = (string) $this->currentQuestion->id;
        /** @var array{left?: array<int, int>, right?: array<int, int>}|null $customOrder */
        $customOrder = is_array($optionsOrderMap) && isset($optionsOrderMap[$qId]) && is_array($optionsOrderMap[$qId])
            ? $optionsOrderMap[$qId]
            : null;

        $optionsKeyed = $options->keyBy('id');

        // Left order
        $leftIds = [];
        if ($customOrder && isset($customOrder['left'])) {
            $leftIds = array_map('intval', $customOrder['left']);
        } else {
            $leftIds = $options->sortBy('order_position')->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        // Right order
        $rightIds = [];
        if ($customOrder && isset($customOrder['right'])) {
            $rightIds = array_map('intval', $customOrder['right']);
        } else {
            $rightIds = $options->sortBy('order_position')->pluck('id')->map(fn ($id) => (int) $id)->all();
            if (count($rightIds) > 1) {
                $first = array_shift($rightIds);
                $rightIds[] = (int) $first;
            }
        }

        $left = [];
        foreach ($leftIds as $id) {
            $opt = $optionsKeyed->get($id);
            if ($opt) {
                $left[] = [
                    'id' => (int) $opt->id,
                    'text' => (string) ($opt->option_text ?? ''),
                    'image_url' => $opt->getImageUrl(),
                ];
            }
        }

        $right = [];
        foreach ($rightIds as $id) {
            $opt = $optionsKeyed->get($id);
            if ($opt) {
                $right[] = [
                    'id' => (int) $opt->id,
                    'text' => (string) ($opt->match_text ?? ''),
                    'image_url' => $opt->getMatchImageUrl(),
                ];
            }
        }

        return ['left' => $left, 'right' => $right];
    }

    /**
     * Check if results breakdown should be shown based on show_answers_policy.
     */
    public function shouldShowDetailedAnswers(): bool
    {
        if (! $this->attempt || ! $this->attempt->isFinished()) {
            return false;
        }

        $policy = $this->quiz->show_answers_policy ?? QuizShowAnswersPolicy::AfterSubmission;

        return match ($policy) {
            QuizShowAnswersPolicy::AfterSubmission => true,
            QuizShowAnswersPolicy::AfterClosing => $this->quiz->ends_at !== null && now()->greaterThan($this->quiz->ends_at),
            QuizShowAnswersPolicy::Never => false,
        };
    }

    public function render(): View
    {
        /** @var User $user */
        $user = auth()->user();

        $previousAttempts = $this->quiz->attempts()
            ->where('user_id', $user->id)
            ->whereIn('status', [QuizAttemptStatus::Completed, QuizAttemptStatus::TimedOut])
            ->latest('started_at')
            ->get();

        $remainingSeconds = $this->attempt?->getRemainingSeconds();

        return view('livewire.student.quiz-attempt-runner', [
            'previousAttempts' => $previousAttempts,
            'remainingSeconds' => $remainingSeconds,
            'navigatorData' => $this->getNavigatorDataProperty(),
            'options' => $this->getQuestionOptionsProperty(),
            'matchingPairs' => $this->getMatchingPairsProperty(),
            'showAnswers' => $this->shouldShowDetailedAnswers(),
        ]);
    }
}
