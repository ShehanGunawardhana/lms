<?php

declare(strict_types=1);

namespace App\Livewire\Teacher;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class QuestionBank extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'type')]
    public string $typeFilter = 'all';

    #[Url(as: 'diff')]
    public string $difficultyFilter = 'all';

    #[Url(as: 'tag')]
    public string $tagFilter = 'all';

    #[Url(as: 'status')]
    public string $statusFilter = 'all';

    // Modal state
    public bool $showModal = false;

    public bool $isEditing = false;

    public ?int $editingQuestionId = null;

    // Form fields
    public string $type = 'single_choice';

    public string $title = '';

    public string $question_text = '';

    public string $explanation = '';

    public string $hint = '';

    public string $difficulty = 'medium';

    public float $default_marks = 1.0;

    public float $default_penalty_marks = 0.0;

    public bool $is_active = true;

    public string $tagsInput = '';

    /**
     * Dynamic options array
     *
     * @var array<int, array<string, mixed>>
     */
    public array $options = [];

    // Settings (e.g. case sensitivity for blanks, shuffle)
    public bool $caseSensitive = false;

    // Preview Modal state
    public bool $showPreviewModal = false;

    public ?int $previewQuestionId = null;

    public bool $showAnswersInPreview = false;

    // Delete Modal state
    public bool $showDeleteModal = false;

    public ?int $deletingQuestionId = null;

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        Gate::authorize('viewAny', Question::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDifficultyFilter(): void
    {
        $this->resetPage();
    }

    public function updatingTagFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedType(string $newType): void
    {
        $this->initOptionsForType($newType);
    }

    public function openCreateModal(): void
    {
        Gate::authorize('create', Question::class);

        $this->resetValidation();
        $this->isEditing = false;
        $this->editingQuestionId = null;
        $this->type = QuestionType::SingleChoice->value;
        $this->title = '';
        $this->question_text = '';
        $this->explanation = '';
        $this->hint = '';
        $this->difficulty = QuestionDifficulty::Medium->value;
        $this->default_marks = 1.0;
        $this->default_penalty_marks = 0.0;
        $this->is_active = true;
        $this->tagsInput = '';
        $this->caseSensitive = false;

        $this->initOptionsForType($this->type);
        $this->showModal = true;
    }

    public function initOptionsForType(string $type): void
    {
        switch ($type) {
            case QuestionType::SingleChoice->value:
                $this->options = [
                    ['text' => '', 'is_correct' => true],
                    ['text' => '', 'is_correct' => false],
                    ['text' => '', 'is_correct' => false],
                    ['text' => '', 'is_correct' => false],
                ];
                break;

            case QuestionType::MultipleChoice->value:
                $this->options = [
                    ['text' => '', 'is_correct' => true],
                    ['text' => '', 'is_correct' => false],
                    ['text' => '', 'is_correct' => false],
                    ['text' => '', 'is_correct' => false],
                ];
                break;

            case QuestionType::TrueFalse->value:
                $this->options = [
                    ['text' => 'True', 'is_correct' => true],
                    ['text' => 'False', 'is_correct' => false],
                ];
                break;

            case QuestionType::Matching->value:
                $this->options = [
                    ['left' => '', 'right' => ''],
                    ['left' => '', 'right' => ''],
                    ['left' => '', 'right' => ''],
                ];
                break;

            case QuestionType::PictureChoice->value:
                $this->options = [
                    ['image_path' => '', 'text' => 'Option 1', 'is_correct' => true],
                    ['image_path' => '', 'text' => 'Option 2', 'is_correct' => false],
                    ['image_path' => '', 'text' => 'Option 3', 'is_correct' => false],
                ];
                break;

            case QuestionType::FillInTheBlank->value:
                $this->options = [
                    ['acceptable_answers' => '', 'feedback' => ''],
                ];
                break;

            case QuestionType::Ordering->value:
                $this->options = [
                    ['text' => '', 'order_position' => 1],
                    ['text' => '', 'order_position' => 2],
                    ['text' => '', 'order_position' => 3],
                ];
                break;

            case QuestionType::ShortAnswer->value:
                $this->options = [
                    ['sample_answer' => '', 'feedback' => ''],
                ];
                break;

            default:
                $this->options = [];
        }
    }

    public function addOption(): void
    {
        switch ($this->type) {
            case QuestionType::SingleChoice->value:
            case QuestionType::MultipleChoice->value:
                $this->options[] = ['text' => '', 'is_correct' => false];
                break;

            case QuestionType::Matching->value:
                $this->options[] = ['left' => '', 'right' => ''];
                break;

            case QuestionType::PictureChoice->value:
                $this->options[] = ['image_path' => '', 'text' => 'Option '.(count($this->options) + 1), 'is_correct' => false];
                break;

            case QuestionType::FillInTheBlank->value:
                $this->options[] = ['acceptable_answers' => '', 'feedback' => ''];
                break;

            case QuestionType::Ordering->value:
                $this->options[] = ['text' => '', 'order_position' => count($this->options) + 1];
                break;
        }
    }

    public function removeOption(int $index): void
    {
        if (count($this->options) <= 1) {
            return;
        }

        unset($this->options[$index]);
        $this->options = array_values($this->options);

        // If single choice and removed correct one, ensure at least one remains correct
        if ($this->type === QuestionType::SingleChoice->value) {
            $hasCorrect = false;
            foreach ($this->options as $opt) {
                if (! empty($opt['is_correct'])) {
                    $hasCorrect = true;
                    break;
                }
            }
            if (! $hasCorrect && isset($this->options[0])) {
                $this->options[0]['is_correct'] = true;
            }
        }

        // Recalculate order positions for ordering type
        if ($this->type === QuestionType::Ordering->value) {
            foreach ($this->options as $i => &$opt) {
                $opt['order_position'] = $i + 1;
            }
        }
    }

    public function setSingleCorrect(int $index): void
    {
        foreach ($this->options as $i => &$opt) {
            $opt['is_correct'] = ($i === $index);
        }
    }

    public function setTrueFalseCorrect(bool $isTrue): void
    {
        if (count($this->options) >= 2) {
            $this->options[0]['is_correct'] = $isTrue;
            $this->options[1]['is_correct'] = ! $isTrue;
        }
    }

    public function moveOptionUp(int $index): void
    {
        if ($index <= 0 || ! isset($this->options[$index])) {
            return;
        }

        $temp = $this->options[$index - 1];
        $this->options[$index - 1] = $this->options[$index];
        $this->options[$index] = $temp;

        if ($this->type === QuestionType::Ordering->value) {
            foreach ($this->options as $i => &$opt) {
                $opt['order_position'] = $i + 1;
            }
        }
    }

    public function moveOptionDown(int $index): void
    {
        if ($index >= count($this->options) - 1 || ! isset($this->options[$index])) {
            return;
        }

        $temp = $this->options[$index + 1];
        $this->options[$index + 1] = $this->options[$index];
        $this->options[$index] = $temp;

        if ($this->type === QuestionType::Ordering->value) {
            foreach ($this->options as $i => &$opt) {
                $opt['order_position'] = $i + 1;
            }
        }
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

    public function editQuestion(int $id): void
    {
        $institutionId = $this->getInstitutionId();
        $question = Question::where('institution_id', $institutionId)
            ->with(['options', 'tags'])
            ->findOrFail($id);

        Gate::authorize('update', $question);

        $this->resetValidation();
        $this->isEditing = true;
        $this->editingQuestionId = $question->id;
        $this->type = $question->type->value;
        $this->title = $question->title;
        $this->question_text = $question->question_text;
        $this->explanation = $question->explanation ?? '';
        $this->hint = $question->hint ?? '';
        $this->difficulty = $question->difficulty->value;
        $this->default_marks = (float) $question->default_marks;
        $this->default_penalty_marks = (float) $question->default_penalty_marks;
        $this->is_active = (bool) $question->is_active;
        $this->caseSensitive = ! empty($question->settings['case_sensitive']);
        $this->tagsInput = $question->tags->pluck('name')->implode(', ');

        // Populate options based on question type
        $this->options = [];
        switch ($question->type->value) {
            case QuestionType::SingleChoice->value:
            case QuestionType::MultipleChoice->value:
                foreach ($question->options as $opt) {
                    $this->options[] = [
                        'id' => $opt->id,
                        'text' => $opt->option_text ?? '',
                        'is_correct' => (bool) $opt->is_correct,
                    ];
                }
                break;

            case QuestionType::TrueFalse->value:
                foreach ($question->options as $opt) {
                    $this->options[] = [
                        'id' => $opt->id,
                        'text' => $opt->option_text ?? '',
                        'is_correct' => (bool) $opt->is_correct,
                    ];
                }
                break;

            case QuestionType::Matching->value:
                foreach ($question->options as $opt) {
                    $this->options[] = [
                        'id' => $opt->id,
                        'left' => $opt->option_text ?? '',
                        'right' => $opt->match_text ?? '',
                    ];
                }
                break;

            case QuestionType::PictureChoice->value:
                foreach ($question->options as $opt) {
                    $this->options[] = [
                        'id' => $opt->id,
                        'image_path' => $opt->image_path ?? '',
                        'text' => $opt->option_text ?? '',
                        'is_correct' => (bool) $opt->is_correct,
                    ];
                }
                break;

            case QuestionType::FillInTheBlank->value:
                foreach ($question->options as $opt) {
                    $acceptable = is_array($opt->acceptable_answers)
                        ? implode(', ', $opt->acceptable_answers)
                        : ($opt->option_text ?? '');

                    $this->options[] = [
                        'id' => $opt->id,
                        'acceptable_answers' => $acceptable,
                        'feedback' => $opt->feedback ?? '',
                    ];
                }
                break;

            case QuestionType::Ordering->value:
                foreach ($question->options as $i => $opt) {
                    $this->options[] = [
                        'id' => $opt->id,
                        'text' => $opt->option_text ?? '',
                        'order_position' => $opt->order_position ?: ($i + 1),
                    ];
                }
                break;

            case QuestionType::ShortAnswer->value:
                $first = $question->options->first();
                $this->options[] = [
                    'id' => $first?->id,
                    'sample_answer' => $first !== null ? ($first->option_text ?? '') : '',
                    'feedback' => $first !== null ? ($first->feedback ?? '') : '',
                ];
                break;
        }

        if (empty($this->options)) {
            $this->initOptionsForType($this->type);
        }

        $this->showModal = true;
    }

    public function saveQuestion(): void
    {
        $institutionId = $this->getInstitutionId();

        $rules = [
            'type' => 'required|string',
            'title' => 'required|string|max:255',
            'question_text' => 'required|string',
            'difficulty' => 'required|string',
            'default_marks' => 'required|numeric|min:0.25|max:1000',
            'default_penalty_marks' => 'required|numeric|min:0|max:1000',
            'explanation' => 'nullable|string|max:2000',
            'hint' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ];

        $this->validate($rules);

        // Custom validation based on Question Type
        $this->validateOptions();

        DB::transaction(function () use ($institutionId) {
            $settings = [];
            if ($this->type === QuestionType::FillInTheBlank->value) {
                $settings['case_sensitive'] = $this->caseSensitive;
            }

            $data = [
                'institution_id' => $institutionId,
                'type' => $this->type,
                'title' => trim($this->title),
                'question_text' => trim($this->question_text),
                'explanation' => trim($this->explanation) !== '' ? trim($this->explanation) : null,
                'hint' => trim($this->hint) !== '' ? trim($this->hint) : null,
                'difficulty' => $this->difficulty,
                'default_marks' => $this->default_marks,
                'default_penalty_marks' => $this->default_penalty_marks,
                'settings' => ! empty($settings) ? $settings : null,
                'is_active' => $this->is_active,
            ];

            if ($this->isEditing && $this->editingQuestionId) {
                $question = Question::where('institution_id', $institutionId)->findOrFail($this->editingQuestionId);
                Gate::authorize('update', $question);
                $question->update($data);

                // Remove existing options to re-insert freshly
                $question->options()->delete();
            } else {
                Gate::authorize('create', Question::class);
                $data['created_by'] = auth()->id();
                $question = Question::create($data);
            }

            // Save Question Options
            $this->saveOptionsForQuestion($question);

            // Sync Tags
            $tagNames = array_filter(array_map('trim', explode(',', $this->tagsInput)));
            $question->syncTagNames($tagNames);

            // Spatie Activity Log
            if (function_exists('activity')) {
                activity()
                    ->performedOn($question)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'title' => $question->title,
                        'type' => $question->type->value,
                        'difficulty' => $question->difficulty->value,
                    ])
                    ->log($this->isEditing ? 'question_updated' : 'question_created');
            }
        });

        session()->flash('success', $this->isEditing ? __('Question updated successfully.') : __('Question created in Question Bank.'));
        $this->showModal = false;
        $this->resetPage();
    }

    private function validateOptions(): void
    {
        switch ($this->type) {
            case QuestionType::SingleChoice->value:
                if (count($this->options) < 2) {
                    $this->addError('options', __('At least 2 options are required for single choice.'));
                }
                $hasCorrect = false;
                $allHaveText = true;
                foreach ($this->options as $opt) {
                    if (trim((string) ($opt['text'] ?? '')) === '') {
                        $allHaveText = false;
                    }
                    if (! empty($opt['is_correct'])) {
                        $hasCorrect = true;
                    }
                }
                if (! $allHaveText) {
                    $this->addError('options', __('All options must have text.'));
                }
                if (! $hasCorrect) {
                    $this->addError('options', __('Please select one correct option.'));
                }
                break;

            case QuestionType::MultipleChoice->value:
                if (count($this->options) < 2) {
                    $this->addError('options', __('At least 2 options are required for multiple choice.'));
                }
                $hasCorrect = false;
                $allHaveText = true;
                foreach ($this->options as $opt) {
                    if (trim((string) ($opt['text'] ?? '')) === '') {
                        $allHaveText = false;
                    }
                    if (! empty($opt['is_correct'])) {
                        $hasCorrect = true;
                    }
                }
                if (! $allHaveText) {
                    $this->addError('options', __('All options must have text.'));
                }
                if (! $hasCorrect) {
                    $this->addError('options', __('Please select at least one correct option.'));
                }
                break;

            case QuestionType::TrueFalse->value:
                $hasCorrect = false;
                foreach ($this->options as $opt) {
                    if (! empty($opt['is_correct'])) {
                        $hasCorrect = true;
                        break;
                    }
                }
                if (! $hasCorrect) {
                    $this->addError('options', __('Please select whether True or False is the correct answer.'));
                }
                break;

            case QuestionType::Matching->value:
                if (count($this->options) < 2) {
                    $this->addError('options', __('At least 2 matching pairs are required.'));
                }
                foreach ($this->options as $opt) {
                    if (trim((string) ($opt['left'] ?? '')) === '' || trim((string) ($opt['right'] ?? '')) === '') {
                        $this->addError('options', __('Every matching pair must have both left premise and right target text.'));
                        break;
                    }
                }
                break;

            case QuestionType::PictureChoice->value:
                if (count($this->options) < 2) {
                    $this->addError('options', __('At least 2 picture options are required.'));
                }
                $hasCorrect = false;
                foreach ($this->options as $opt) {
                    if (! empty($opt['is_correct'])) {
                        $hasCorrect = true;
                        break;
                    }
                }
                if (! $hasCorrect) {
                    $this->addError('options', __('Please select at least one correct picture answer.'));
                }
                break;

            case QuestionType::FillInTheBlank->value:
                if (empty($this->options)) {
                    $this->addError('options', __('At least one blank answer is required.'));
                }
                foreach ($this->options as $opt) {
                    if (trim((string) ($opt['acceptable_answers'] ?? '')) === '') {
                        $this->addError('options', __('Please provide accepted answers for the blank.'));
                        break;
                    }
                }
                break;

            case QuestionType::Ordering->value:
                if (count($this->options) < 2) {
                    $this->addError('options', __('At least 2 items are required for ordering.'));
                }
                foreach ($this->options as $opt) {
                    if (trim((string) ($opt['text'] ?? '')) === '') {
                        $this->addError('options', __('All ordering items must have text.'));
                        break;
                    }
                }
                break;

            case QuestionType::ShortAnswer->value:
                // Optional rubric/sample answer
                break;
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            throw ValidationException::withMessages($this->getErrorBag()->toArray());
        }
    }

    private function saveOptionsForQuestion(Question $question): void
    {
        switch ($this->type) {
            case QuestionType::SingleChoice->value:
            case QuestionType::MultipleChoice->value:
                foreach ($this->options as $index => $opt) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => trim((string) ($opt['text'] ?? '')),
                        'is_correct' => ! empty($opt['is_correct']),
                        'order_position' => $index + 1,
                    ]);
                }
                break;

            case QuestionType::TrueFalse->value:
                foreach ($this->options as $index => $opt) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $index === 0 ? 'True' : 'False',
                        'is_correct' => ! empty($opt['is_correct']),
                        'order_position' => $index + 1,
                    ]);
                }
                break;

            case QuestionType::Matching->value:
                foreach ($this->options as $index => $opt) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => trim((string) ($opt['left'] ?? '')),
                        'match_text' => trim((string) ($opt['right'] ?? '')),
                        'is_correct' => true,
                        'order_position' => $index + 1,
                    ]);
                }
                break;

            case QuestionType::PictureChoice->value:
                foreach ($this->options as $index => $opt) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => trim((string) ($opt['text'] ?? '')),
                        'image_path' => trim((string) ($opt['image_path'] ?? '')) ?: null,
                        'is_correct' => ! empty($opt['is_correct']),
                        'order_position' => $index + 1,
                    ]);
                }
                break;

            case QuestionType::FillInTheBlank->value:
                foreach ($this->options as $index => $opt) {
                    $acceptableList = array_values(array_filter(array_map('trim', explode(',', (string) ($opt['acceptable_answers'] ?? '')))));
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $acceptableList[0] ?? '',
                        'acceptable_answers' => $acceptableList,
                        'feedback' => trim((string) ($opt['feedback'] ?? '')) ?: null,
                        'is_correct' => true,
                        'order_position' => $index + 1,
                    ]);
                }
                break;

            case QuestionType::Ordering->value:
                foreach ($this->options as $index => $opt) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => trim((string) ($opt['text'] ?? '')),
                        'order_position' => $index + 1,
                        'is_correct' => true,
                    ]);
                }
                break;

            case QuestionType::ShortAnswer->value:
                $first = $this->options[0] ?? [];
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => trim((string) ($first['sample_answer'] ?? '')) ?: null,
                    'feedback' => trim((string) ($first['feedback'] ?? '')) ?: null,
                    'order_position' => 1,
                ]);
                break;
        }
    }

    public function duplicateQuestion(int $id): void
    {
        $institutionId = $this->getInstitutionId();
        $question = Question::where('institution_id', $institutionId)
            ->with(['options', 'tags'])
            ->findOrFail($id);

        Gate::authorize('duplicate', $question);

        /** @var User $user */
        $user = auth()->user();
        $cloned = $question->duplicate($user->id);

        if (function_exists('activity')) {
            activity()
                ->performedOn($cloned)
                ->causedBy($user)
                ->withProperties(['original_id' => $question->id])
                ->log('question_duplicated');
        }

        session()->flash('success', __('Question ":title" duplicated successfully.', ['title' => $cloned->title]));
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $institutionId = $this->getInstitutionId();
        $question = Question::where('institution_id', $institutionId)->findOrFail($id);

        Gate::authorize('delete', $question);

        $this->deletingQuestionId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteQuestion(): void
    {
        if (! $this->deletingQuestionId) {
            return;
        }

        $institutionId = $this->getInstitutionId();
        $question = Question::where('institution_id', $institutionId)->findOrFail($this->deletingQuestionId);

        Gate::authorize('delete', $question);

        $title = $question->title;
        $question->delete();

        if (function_exists('activity')) {
            activity()
                ->performedOn($question)
                ->causedBy(auth()->user())
                ->log('question_deleted');
        }

        session()->flash('success', __('Question ":title" deleted successfully.', ['title' => $title]));
        $this->showDeleteModal = false;
        $this->deletingQuestionId = null;
        $this->resetPage();
    }

    public function toggleActive(int $id): void
    {
        $institutionId = $this->getInstitutionId();
        $question = Question::where('institution_id', $institutionId)->findOrFail($id);

        Gate::authorize('update', $question);

        $question->is_active = ! $question->is_active;
        $question->save();

        session()->flash('success', __('Question status updated.'));
    }

    public function openPreview(int $id): void
    {
        $institutionId = $this->getInstitutionId();
        $question = Question::where('institution_id', $institutionId)
            ->with(['options', 'tags'])
            ->findOrFail($id);

        Gate::authorize('view', $question);

        $this->previewQuestionId = $question->id;
        $this->showAnswersInPreview = false;
        $this->showPreviewModal = true;
    }

    public function togglePreviewAnswers(): void
    {
        $this->showAnswersInPreview = ! $this->showAnswersInPreview;
    }

    public function render(): View
    {
        $institutionId = $this->getInstitutionId();

        $query = Question::where('institution_id', $institutionId)
            ->with(['creator', 'options', 'tags'])
            ->latest('id');

        // Search
        if (trim($this->search) !== '') {
            $query->search($this->search);
        }

        // Type filter
        if ($this->typeFilter !== 'all') {
            $query->ofType($this->typeFilter);
        }

        // Difficulty filter
        if ($this->difficultyFilter !== 'all') {
            $query->ofDifficulty($this->difficultyFilter);
        }

        // Tag filter
        if ($this->tagFilter !== 'all') {
            $query->withTag($this->tagFilter);
        }

        // Status filter
        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        $questions = $query->paginate(12);

        // Metrics
        $totalQuestions = Question::where('institution_id', $institutionId)->count();
        $autoMarkedCount = Question::where('institution_id', $institutionId)
            ->where('type', '!=', QuestionType::ShortAnswer->value)
            ->count();
        $manualMarkedCount = Question::where('institution_id', $institutionId)
            ->where('type', QuestionType::ShortAnswer->value)
            ->count();
        $totalTagsCount = Tag::where('institution_id', $institutionId)->count();

        // Available tags for filter
        $availableTags = Tag::where('institution_id', $institutionId)
            ->withCount('questions')
            ->orderBy('name')
            ->get();

        $previewQuestion = null;
        if ($this->previewQuestionId) {
            $previewQuestion = Question::where('institution_id', $institutionId)
                ->with(['options', 'tags'])
                ->find($this->previewQuestionId);
        }

        return view('livewire.teacher.question-bank', [
            'questions' => $questions,
            'totalQuestions' => $totalQuestions,
            'autoMarkedCount' => $autoMarkedCount,
            'manualMarkedCount' => $manualMarkedCount,
            'totalTagsCount' => $totalTagsCount,
            'availableTags' => $availableTags,
            'previewQuestion' => $previewQuestion,
            'questionTypes' => QuestionType::cases(),
            'difficulties' => QuestionDifficulty::cases(),
        ]);
    }
}
