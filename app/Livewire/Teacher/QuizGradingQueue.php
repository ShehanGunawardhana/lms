<?php

declare(strict_types=1);

namespace App\Livewire\Teacher;

use App\Enums\QuestionType;
use App\Models\Quiz;
use App\Models\QuizAttemptAnswer;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\QuizGrader;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class QuizGradingQueue extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public ?int $selectedQuizId = null;

    public ?int $selectedClassId = null;

    public string $statusFilter = 'pending'; // 'pending', 'graded', 'all'

    public string $search = '';

    // Grading modal properties
    public bool $showGradeModal = false;

    public ?int $activeAnswerId = null;

    public ?QuizAttemptAnswer $activeAnswer = null;

    public float $marksAwarded = 0.0;

    public string $teacherFeedback = '';

    public float $maxMarks = 0.0;

    public string $gradeError = '';

    public string $successMessage = '';

    /**
     * @var array<string, mixed>
     */
    protected $queryString = [
        'selectedQuizId' => ['except' => null, 'as' => 'quiz'],
        'selectedClassId' => ['except' => null, 'as' => 'class'],
        'statusFilter' => ['except' => 'pending', 'as' => 'status'],
        'search' => ['except' => ''],
    ];

    public function mount(?int $quiz = null): void
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user->hasAnyRole(['super_admin', 'admin', 'teacher'])) {
            abort(403, __('You are not authorized to access the quiz grading queue.'));
        }

        if ($quiz) {
            $this->selectedQuizId = $quiz;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedQuizId(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedClassId(): void
    {
        $this->resetPage();
    }

    public function openGradeModal(int $answerId): void
    {
        /** @var User|null $user */
        $user = auth()->user();
        $institutionId = $user ? $user->institution_id : (current_institution_id() ?? 1);

        $answer = QuizAttemptAnswer::with(['attempt.user', 'attempt.quiz.quizQuestions', 'question'])
            ->whereHas('attempt', fn ($q) => $q->where('institution_id', $institutionId))
            ->findOrFail($answerId);

        $this->activeAnswer = $answer;
        $this->activeAnswerId = $answerId;

        $maxMarks = (float) ($answer->question ? $answer->question->default_marks : 1.0);
        if ($answer->attempt?->quiz && $answer->question) {
            $qq = $answer->attempt->quiz->quizQuestions->firstWhere('question_id', $answer->question->id);
            if ($qq) {
                $maxMarks = $qq->getEffectiveMarks();
            }
        }

        $this->maxMarks = $maxMarks;
        $this->marksAwarded = (float) ($answer->marks_awarded ?? $maxMarks);
        $this->teacherFeedback = (string) ($answer->feedback ?? '');
        $this->gradeError = '';
        $this->showGradeModal = true;
    }

    public function setScoreQuick(float $factor): void
    {
        $this->marksAwarded = round($this->maxMarks * $factor, 2);
    }

    public function saveGrade(bool $andNext = false): void
    {
        if (! $this->activeAnswer) {
            return;
        }

        if ($this->marksAwarded < 0 || $this->marksAwarded > $this->maxMarks) {
            $this->gradeError = __('Awarded marks must be between 0 and :max.', ['max' => $this->maxMarks]);

            return;
        }

        /** @var User $grader */
        $grader = auth()->user();

        app(QuizGrader::class)->gradeManualAnswer(
            $this->activeAnswer,
            $this->marksAwarded,
            trim($this->teacherFeedback) !== '' ? $this->teacherFeedback : null,
            $grader
        );

        $this->successMessage = __('Answer graded successfully.');

        if ($andNext) {
            /** @var User $graderUser */
            $graderUser = auth()->user();
            $institutionId = $graderUser->institution_id;
            $next = app(QuizGrader::class)
                ->getManualGradingQueueQuery($institutionId, $this->selectedQuizId, $this->selectedClassId, 'pending')
                ->where('id', '!=', $this->activeAnswerId)
                ->first();

            if ($next) {
                $this->openGradeModal($next->id);

                return;
            }
        }

        $this->showGradeModal = false;
        $this->activeAnswer = null;
        $this->activeAnswerId = null;
    }

    public function render(): View
    {
        /** @var User|null $user */
        $user = auth()->user();
        $institutionId = $user ? $user->institution_id : (current_institution_id() ?? 1);
        $grader = app(QuizGrader::class);

        // Queue counts for metrics bar
        $pendingCount = $grader->getManualGradingQueueQuery($institutionId, null, null, 'pending')->count();
        $gradedCount = $grader->getManualGradingQueueQuery($institutionId, null, null, 'graded')->count();
        $totalCount = $pendingCount + $gradedCount;

        // Quizzes with short answer questions or completed attempts
        $quizzes = Quiz::where('institution_id', $institutionId)
            ->whereHas('questions', fn ($q) => $q->where('type', QuestionType::ShortAnswer))
            ->orderBy('title')
            ->get(['id', 'title', 'code']);

        $classes = SchoolClass::where('institution_id', $institutionId)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Query queue answers
        $query = $grader->getManualGradingQueueQuery(
            $institutionId,
            $this->selectedQuizId,
            $this->selectedClassId,
            $this->statusFilter
        )->with(['attempt.user', 'attempt.quiz', 'question', 'grader']);

        if (trim($this->search) !== '') {
            $search = '%'.trim($this->search).'%';
            $query->where(function (Builder $q) use ($search) {
                $q->whereHas('attempt.user', fn (Builder $u) => $u->where('name', 'like', $search)->orWhere('email', 'like', $search))
                    ->orWhereHas('attempt.quiz', fn (Builder $qz) => $qz->where('title', 'like', $search)->orWhere('code', 'like', $search))
                    ->orWhereHas('question', fn (Builder $qst) => $qst->where('title', 'like', $search)->orWhere('question_text', 'like', $search))
                    ->orWhere('text_answer', 'like', $search);
            });
        }

        $answers = $query->latest('id')->paginate(15);

        return view('livewire.teacher.quiz-grading-queue', [
            'answers' => $answers,
            'quizzes' => $quizzes,
            'classes' => $classes,
            'pendingCount' => $pendingCount,
            'gradedCount' => $gradedCount,
            'totalCount' => $totalCount,
        ]);
    }
}
