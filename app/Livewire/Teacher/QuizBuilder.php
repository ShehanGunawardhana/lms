<?php

declare(strict_types=1);

namespace App\Livewire\Teacher;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use App\Enums\QuizMode;
use App\Enums\QuizShowAnswersPolicy;
use App\Enums\QuizStatus;
use App\Models\Activity;
use App\Models\ClassSession;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\SchoolClass;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class QuizBuilder extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    // Filters
    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'status')]
    public string $statusFilter = 'all'; // all, draft, published, archived

    #[Url(as: 'mode')]
    public string $modeFilter = 'all'; // all, standard, nursery, live, paper

    #[Url(as: 'attachment')]
    public string $attachmentFilter = 'all'; // all, standalone, class, activity, session

    #[Url(as: 'class')]
    public string $classFilter = 'all';

    // Quiz Editor Modal State
    public bool $showQuizModal = false;

    public bool $isEditing = false;

    public ?int $editingQuizId = null;

    // Active tab in Quiz modal: 'settings', 'attachment', 'questions'
    public string $activeModalTab = 'settings';

    // Quiz Form Fields
    public string $title = '';

    public string $description = '';

    public string $instructions = '';

    public string $mode = 'standard';

    public string $status = 'draft';

    public ?int $timeLimitMinutes = 30;

    public ?int $maxAttempts = 1;

    public float $passPercentage = 50.0;

    public bool $shuffleQuestions = false;

    public bool $shuffleOptions = false;

    public bool $allowNegativeMarking = false;

    public string $showAnswersPolicy = 'after_submission';

    public ?string $startsAt = null;

    public ?string $endsAt = null;

    // Attachment target
    public string $attachmentType = 'standalone'; // standalone, class, activity, session

    public ?int $selectedClassId = null;

    public ?int $selectedActivityId = null;

    public ?int $selectedSessionId = null;

    /**
     * Attached quiz questions array:
     * [
     *   'question_id' => int,
     *   'title' => string,
     *   'type' => string,
     *   'difficulty' => string,
     *   'marks' => float,
     *   'penalty_marks' => float,
     *   'has_image' => bool,
     *   'has_audio' => bool,
     * ]
     *
     * @var array<int, array<string, mixed>>
     */
    public array $selectedQuestions = [];

    // Question Bank Picker Modal State
    public bool $showQuestionBankPicker = false;

    public string $pickerSearch = '';

    public string $pickerType = 'all';

    public string $pickerDifficulty = 'all';

    public string $pickerTag = 'all';

    /**
     * @var array<int, int>
     */
    public array $pickerCheckedQuestionIds = [];

    // Delete Modal State
    public bool $showDeleteModal = false;

    public ?int $deletingQuizId = null;

    // Preview Quiz Modal State
    public bool $showPreviewModal = false;

    public ?int $previewQuizId = null;

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        Gate::authorize('viewAny', Quiz::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingModeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingAttachmentFilter(): void
    {
        $this->resetPage();
    }

    public function updatingClassFilter(): void
    {
        $this->resetPage();
    }

    private function getInstitutionId(): int
    {
        $current = current_institution_id();
        if ($current !== null) {
            return $current;
        }

        /** @var User|null $user */
        $user = auth()->user();

        return $user ? (int) $user->institution_id : 1;
    }

    public function openCreateModal(): void
    {
        Gate::authorize('create', Quiz::class);

        $this->resetValidation();
        $this->isEditing = false;
        $this->editingQuizId = null;
        $this->activeModalTab = 'settings';

        $this->title = '';
        $this->description = '';
        $this->instructions = 'Read each question carefully before submitting your answer.';
        $this->mode = QuizMode::Standard->value;
        $this->status = QuizStatus::Draft->value;
        $this->timeLimitMinutes = 30;
        $this->maxAttempts = 1;
        $this->passPercentage = 50.0;
        $this->shuffleQuestions = false;
        $this->shuffleOptions = false;
        $this->allowNegativeMarking = false;
        $this->showAnswersPolicy = QuizShowAnswersPolicy::AfterSubmission->value;
        $this->startsAt = null;
        $this->endsAt = null;

        $this->attachmentType = 'standalone';
        $this->selectedClassId = null;
        $this->selectedActivityId = null;
        $this->selectedSessionId = null;

        $this->selectedQuestions = [];

        $this->showQuizModal = true;
    }

    public function editQuiz(int $id): void
    {
        $institutionId = $this->getInstitutionId();
        $quiz = Quiz::where('institution_id', $institutionId)
            ->with(['quizQuestions.question.tags', 'schoolClass', 'activity', 'classSession'])
            ->findOrFail($id);

        Gate::authorize('update', $quiz);

        $this->resetValidation();
        $this->isEditing = true;
        $this->editingQuizId = $quiz->id;
        $this->activeModalTab = 'settings';

        $this->title = $quiz->title;
        $this->description = $quiz->description ?? '';
        $this->instructions = $quiz->instructions ?? '';
        $this->mode = $quiz->mode->value;
        $this->status = $quiz->status->value;
        $this->timeLimitMinutes = $quiz->time_limit_minutes;
        $this->maxAttempts = $quiz->max_attempts;
        $this->passPercentage = (float) $quiz->pass_percentage;
        $this->shuffleQuestions = (bool) $quiz->shuffle_questions;
        $this->shuffleOptions = (bool) $quiz->shuffle_options;
        $this->allowNegativeMarking = (bool) $quiz->allow_negative_marking;
        $this->showAnswersPolicy = $quiz->show_answers_policy->value;
        $this->startsAt = $quiz->starts_at ? $quiz->starts_at->format('Y-m-d\TH:i') : null;
        $this->endsAt = $quiz->ends_at ? $quiz->ends_at->format('Y-m-d\TH:i') : null;

        if ($quiz->activity_id !== null) {
            $this->attachmentType = 'activity';
            $this->selectedActivityId = $quiz->activity_id;
            $this->selectedClassId = null;
            $this->selectedSessionId = null;
        } elseif ($quiz->class_session_id !== null) {
            $this->attachmentType = 'session';
            $this->selectedSessionId = $quiz->class_session_id;
            $this->selectedClassId = null;
            $this->selectedActivityId = null;
        } elseif ($quiz->class_id !== null) {
            $this->attachmentType = 'class';
            $this->selectedClassId = $quiz->class_id;
            $this->selectedActivityId = null;
            $this->selectedSessionId = null;
        } else {
            $this->attachmentType = 'standalone';
            $this->selectedClassId = null;
            $this->selectedActivityId = null;
            $this->selectedSessionId = null;
        }

        $this->selectedQuestions = [];
        foreach ($quiz->quizQuestions as $qq) {
            $q = $qq->question;
            if (! $q) {
                continue;
            }

            $this->selectedQuestions[] = [
                'question_id' => $q->id,
                'title' => $q->title,
                'question_text' => $q->question_text,
                'type' => $q->type->value,
                'type_label' => $q->type->label(),
                'type_badge' => $q->type->badgeClass(),
                'difficulty' => $q->difficulty->value,
                'difficulty_badge' => $q->difficulty->badgeClass(),
                'marks' => $qq->marks !== null ? (float) $qq->marks : (float) $q->default_marks,
                'penalty_marks' => $qq->penalty_marks !== null ? (float) $qq->penalty_marks : (float) $q->default_penalty_marks,
                'has_image' => $q->hasImage(),
                'has_audio' => $q->hasAudio(),
            ];
        }

        $this->showQuizModal = true;
    }

    public function openQuestionBankPicker(): void
    {
        $this->pickerCheckedQuestionIds = [];
        $this->pickerSearch = '';
        $this->pickerType = 'all';
        $this->pickerDifficulty = 'all';
        $this->pickerTag = 'all';
        $this->showQuestionBankPicker = true;
    }

    public function addSelectedQuestionsToQuiz(): void
    {
        if (empty($this->pickerCheckedQuestionIds)) {
            $this->showQuestionBankPicker = false;

            return;
        }

        $institutionId = $this->getInstitutionId();
        $questions = Question::where('institution_id', $institutionId)
            ->whereIn('id', $this->pickerCheckedQuestionIds)
            ->get();

        $alreadySelectedIds = array_column($this->selectedQuestions, 'question_id');

        foreach ($questions as $q) {
            if (in_array($q->id, $alreadySelectedIds, true)) {
                continue;
            }

            $this->selectedQuestions[] = [
                'question_id' => $q->id,
                'title' => $q->title,
                'question_text' => $q->question_text,
                'type' => $q->type->value,
                'type_label' => $q->type->label(),
                'type_badge' => $q->type->badgeClass(),
                'difficulty' => $q->difficulty->value,
                'difficulty_badge' => $q->difficulty->badgeClass(),
                'marks' => (float) $q->default_marks,
                'penalty_marks' => (float) $q->default_penalty_marks,
                'has_image' => $q->hasImage(),
                'has_audio' => $q->hasAudio(),
            ];
        }

        $this->pickerCheckedQuestionIds = [];
        $this->showQuestionBankPicker = false;
    }

    public function removeQuestionFromQuiz(int $index): void
    {
        unset($this->selectedQuestions[$index]);
        $this->selectedQuestions = array_values($this->selectedQuestions);
    }

    public function moveQuestionUp(int $index): void
    {
        if ($index <= 0 || ! isset($this->selectedQuestions[$index])) {
            return;
        }

        $temp = $this->selectedQuestions[$index - 1];
        $this->selectedQuestions[$index - 1] = $this->selectedQuestions[$index];
        $this->selectedQuestions[$index] = $temp;
        $this->selectedQuestions = array_values($this->selectedQuestions);
    }

    public function moveQuestionDown(int $index): void
    {
        if ($index >= count($this->selectedQuestions) - 1 || ! isset($this->selectedQuestions[$index])) {
            return;
        }

        $temp = $this->selectedQuestions[$index + 1];
        $this->selectedQuestions[$index + 1] = $this->selectedQuestions[$index];
        $this->selectedQuestions[$index] = $temp;
        $this->selectedQuestions = array_values($this->selectedQuestions);
    }

    public function getTotalMarksProperty(): float
    {
        $sum = 0.0;
        foreach ($this->selectedQuestions as $q) {
            $sum += (float) ($q['marks'] ?? 0.0);
        }

        return $sum;
    }

    public function saveQuiz(): void
    {
        $institutionId = $this->getInstitutionId();
        /** @var User $user */
        $user = auth()->user();

        if ($this->isEditing && $this->editingQuizId !== null) {
            $quiz = Quiz::where('institution_id', $institutionId)->findOrFail($this->editingQuizId);
            Gate::authorize('update', $quiz);
        } else {
            Gate::authorize('create', Quiz::class);
            $quiz = new Quiz;
            $quiz->institution_id = $institutionId;
            $quiz->created_by = $user->id;
        }

        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'instructions' => 'nullable|string|max:2000',
            'mode' => 'required|in:standard,nursery,live,paper',
            'status' => 'required|in:draft,published,archived',
            'timeLimitMinutes' => 'nullable|integer|min:0|max:1440',
            'maxAttempts' => 'nullable|integer|min:0|max:100',
            'passPercentage' => 'required|numeric|min:0|max:100',
            'showAnswersPolicy' => 'required|in:after_submission,after_closing,never',
            'startsAt' => 'nullable|date',
            'endsAt' => 'nullable|date',
            'attachmentType' => 'required|in:standalone,class,activity,session',
            'selectedClassId' => 'nullable|integer',
            'selectedActivityId' => 'nullable|integer',
            'selectedSessionId' => 'nullable|integer',
        ]);

        if ($this->startsAt && $this->endsAt) {
            $starts = Carbon::parse($this->startsAt);
            $ends = Carbon::parse($this->endsAt);
            if ($ends->lt($starts)) {
                $this->addError('endsAt', __('End date and time cannot be earlier than start date.'));
                throw ValidationException::withMessages($this->getErrorBag()->toArray());
            }
        }

        if ($this->status === QuizStatus::Published->value && empty($this->selectedQuestions)) {
            $this->addError('status', __('A quiz must have at least one question before it can be published.'));
            throw ValidationException::withMessages($this->getErrorBag()->toArray());
        }

        // Validate attachment references
        $classId = null;
        $activityId = null;
        $sessionId = null;

        if ($this->attachmentType === 'class') {
            if (! $this->selectedClassId) {
                $this->addError('selectedClassId', __('Please select a class.'));
                throw ValidationException::withMessages($this->getErrorBag()->toArray());
            }
            $classId = $this->selectedClassId;
        } elseif ($this->attachmentType === 'activity') {
            if (! $this->selectedActivityId) {
                $this->addError('selectedActivityId', __('Please select an activity.'));
                throw ValidationException::withMessages($this->getErrorBag()->toArray());
            }
            $activityId = $this->selectedActivityId;
        } elseif ($this->attachmentType === 'session') {
            if (! $this->selectedSessionId) {
                $this->addError('selectedSessionId', __('Please select a class session.'));
                throw ValidationException::withMessages($this->getErrorBag()->toArray());
            }
            $sessionId = $this->selectedSessionId;
        }

        DB::transaction(function () use ($quiz, $classId, $activityId, $sessionId) {
            $quiz->title = trim($this->title);
            $quiz->description = trim($this->description) ?: null;
            $quiz->instructions = trim($this->instructions) ?: null;
            $quiz->mode = QuizMode::from($this->mode);
            $quiz->status = QuizStatus::from($this->status);
            $quiz->time_limit_minutes = $this->timeLimitMinutes ?: null;
            $quiz->max_attempts = $this->maxAttempts ?: null;
            $quiz->pass_percentage = $this->passPercentage;
            $quiz->shuffle_questions = $this->shuffleQuestions;
            $quiz->shuffle_options = $this->shuffleOptions;
            $quiz->allow_negative_marking = $this->allowNegativeMarking;
            $quiz->show_answers_policy = QuizShowAnswersPolicy::from($this->showAnswersPolicy);
            $quiz->starts_at = $this->startsAt ? Carbon::parse($this->startsAt) : null;
            $quiz->ends_at = $this->endsAt ? Carbon::parse($this->endsAt) : null;
            $quiz->class_id = $classId;
            $quiz->activity_id = $activityId;
            $quiz->class_session_id = $sessionId;

            $quiz->save();

            // Sync Quiz Questions
            QuizQuestion::where('quiz_id', $quiz->id)->delete();

            $totalMarks = 0.0;
            foreach ($this->selectedQuestions as $index => $sq) {
                $marks = isset($sq['marks']) && is_numeric($sq['marks']) ? (float) $sq['marks'] : 1.0;
                $penalty = isset($sq['penalty_marks']) && is_numeric($sq['penalty_marks']) ? (float) $sq['penalty_marks'] : 0.0;

                QuizQuestion::create([
                    'quiz_id' => $quiz->id,
                    'question_id' => (int) $sq['question_id'],
                    'order_position' => $index + 1,
                    'marks' => $marks,
                    'penalty_marks' => $penalty,
                ]);

                $totalMarks += $marks;
            }

            $quiz->total_marks = $totalMarks;
            $quiz->save();

            if (function_exists('activity')) {
                activity()
                    ->performedOn($quiz)
                    ->log($this->isEditing ? 'quiz.updated' : 'quiz.created');
            }
        });

        $this->showQuizModal = false;
        session()->flash('success', $this->isEditing ? __('Quiz updated successfully.') : __('Quiz created successfully.'));
    }

    public function toggleStatus(int $id): void
    {
        $institutionId = $this->getInstitutionId();
        $quiz = Quiz::where('institution_id', $institutionId)->withCount('quizQuestions')->findOrFail($id);

        Gate::authorize('publish', $quiz);

        if ($quiz->status === QuizStatus::Published) {
            $quiz->status = QuizStatus::Draft;
            $quiz->save();
            session()->flash('success', __('Quiz marked as Draft.'));
        } else {
            if ($quiz->quiz_questions_count === 0) {
                session()->flash('error', __('Cannot publish a quiz with no questions. Please edit and add questions first.'));

                return;
            }
            $quiz->status = QuizStatus::Published;
            $quiz->save();
            session()->flash('success', __('Quiz published successfully.'));
        }
    }

    public function duplicateQuiz(int $id): void
    {
        $institutionId = $this->getInstitutionId();
        $quiz = Quiz::where('institution_id', $institutionId)->findOrFail($id);

        Gate::authorize('duplicate', $quiz);

        /** @var User $user */
        $user = auth()->user();
        $cloned = $quiz->duplicate($user->id);

        if (function_exists('activity')) {
            activity()
                ->performedOn($cloned)
                ->log('quiz.duplicated');
        }

        session()->flash('success', __('Quiz duplicated as Draft: :title', ['title' => $cloned->title]));
    }

    public function confirmDelete(int $id): void
    {
        $institutionId = $this->getInstitutionId();
        $quiz = Quiz::where('institution_id', $institutionId)->findOrFail($id);

        Gate::authorize('delete', $quiz);

        $this->deletingQuizId = $quiz->id;
        $this->showDeleteModal = true;
    }

    public function deleteQuiz(): void
    {
        if ($this->deletingQuizId === null) {
            return;
        }

        $institutionId = $this->getInstitutionId();
        $quiz = Quiz::where('institution_id', $institutionId)->findOrFail($this->deletingQuizId);

        Gate::authorize('delete', $quiz);

        $title = $quiz->title;
        $quiz->delete();

        if (function_exists('activity')) {
            activity()
                ->performedOn($quiz)
                ->log('quiz.deleted');
        }

        $this->showDeleteModal = false;
        $this->deletingQuizId = null;

        session()->flash('success', __('Quiz ":title" deleted successfully.', ['title' => $title]));
    }

    public function openPreview(int $id): void
    {
        $institutionId = $this->getInstitutionId();
        $quiz = Quiz::where('institution_id', $institutionId)
            ->with(['quizQuestions.question.options', 'schoolClass', 'activity', 'classSession'])
            ->findOrFail($id);

        Gate::authorize('view', $quiz);

        $this->previewQuizId = $quiz->id;
        $this->showPreviewModal = true;
    }

    public function render(): View
    {
        $institutionId = $this->getInstitutionId();

        // 1. Quizzes Query with filters
        $quizzesQuery = Quiz::query()
            ->where('institution_id', $institutionId)
            ->with(['creator', 'schoolClass', 'activity', 'classSession'])
            ->withCount('quizQuestions');

        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $quizzesQuery->where(function (Builder $q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        if ($this->statusFilter !== 'all') {
            $quizzesQuery->where('status', $this->statusFilter);
        }

        if ($this->modeFilter !== 'all') {
            $quizzesQuery->where('mode', $this->modeFilter);
        }

        if ($this->attachmentFilter === 'standalone') {
            $quizzesQuery->whereNull('class_id')
                ->whereNull('activity_id')
                ->whereNull('class_session_id');
        } elseif ($this->attachmentFilter === 'class') {
            $quizzesQuery->whereNotNull('class_id');
        } elseif ($this->attachmentFilter === 'activity') {
            $quizzesQuery->whereNotNull('activity_id');
        } elseif ($this->attachmentFilter === 'session') {
            $quizzesQuery->whereNotNull('class_session_id');
        }

        if ($this->classFilter !== 'all') {
            $quizzesQuery->where('class_id', (int) $this->classFilter);
        }

        $quizzes = $quizzesQuery->latest()->paginate(10);

        // 2. Metrics counters
        $metrics = [
            'total' => Quiz::where('institution_id', $institutionId)->count(),
            'published' => Quiz::where('institution_id', $institutionId)->where('status', QuizStatus::Published)->count(),
            'draft' => Quiz::where('institution_id', $institutionId)->where('status', QuizStatus::Draft)->count(),
            'nursery' => Quiz::where('institution_id', $institutionId)->where('mode', QuizMode::Nursery)->count(),
            'standalone' => Quiz::where('institution_id', $institutionId)->whereNull('class_id')->whereNull('activity_id')->whereNull('class_session_id')->count(),
        ];

        // 3. Question Bank Picker query if modal is open
        $availableQuestions = collect();
        $tags = collect();
        if ($this->showQuestionBankPicker || $this->showQuizModal) {
            $tags = Tag::where('institution_id', $institutionId)->orderBy('name')->get();
        }

        if ($this->showQuestionBankPicker) {
            $alreadySelectedIds = array_column($this->selectedQuestions, 'question_id');

            $pickerQuery = Question::where('institution_id', $institutionId)
                ->where('is_active', true)
                ->whereNotIn('id', $alreadySelectedIds)
                ->with(['tags', 'imageFile', 'audioFile']);

            if (trim($this->pickerSearch) !== '') {
                $term = '%'.trim($this->pickerSearch).'%';
                $pickerQuery->where(function (Builder $q) use ($term) {
                    $q->where('title', 'like', $term)
                        ->orWhere('question_text', 'like', $term);
                });
            }

            if ($this->pickerType !== 'all') {
                $pickerQuery->where('type', $this->pickerType);
            }

            if ($this->pickerDifficulty !== 'all') {
                $pickerQuery->where('difficulty', $this->pickerDifficulty);
            }

            if ($this->pickerTag !== 'all') {
                $pickerQuery->whereHas('tags', function (Builder $q) {
                    $q->where('tags.id', (int) $this->pickerTag);
                });
            }

            $availableQuestions = $pickerQuery->latest()->take(50)->get();
        }

        // 4. Attachment options
        $classes = SchoolClass::where('institution_id', $institutionId)->orderBy('title')->get();
        $activities = Activity::where('institution_id', $institutionId)->orderByDesc('created_at')->get();
        $sessions = ClassSession::where('institution_id', $institutionId)
            ->with('schoolClass')
            ->orderByDesc('date')
            ->take(30)
            ->get();

        // 5. Preview Quiz instance
        $previewQuiz = null;
        if ($this->showPreviewModal && $this->previewQuizId !== null) {
            $previewQuiz = Quiz::where('institution_id', $institutionId)
                ->with(['quizQuestions.question.options', 'schoolClass', 'activity', 'classSession', 'creator'])
                ->find($this->previewQuizId);
        }

        return view('livewire.teacher.quiz-builder', [
            'quizzes' => $quizzes,
            'metrics' => $metrics,
            'classes' => $classes,
            'activities' => $activities,
            'sessions' => $sessions,
            'availableQuestions' => $availableQuestions,
            'tags' => $tags,
            'previewQuiz' => $previewQuiz,
            'modes' => QuizMode::cases(),
            'statuses' => QuizStatus::cases(),
            'policies' => QuizShowAnswersPolicy::cases(),
            'questionTypes' => QuestionType::cases(),
            'difficulties' => QuestionDifficulty::cases(),
        ]);
    }
}
