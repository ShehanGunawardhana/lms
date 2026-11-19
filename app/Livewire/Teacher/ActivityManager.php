<?php

namespace App\Livewire\Teacher;

use App\Enums\ActivityStatus;
use App\Enums\ClassKind;
use App\Enums\RoleName;
use App\Models\Activity;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ActivityManager extends Component
{
    use AuthorizesRequests, WithPagination;

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'status')]
    public string $statusFilter = 'all'; // 'all', 'draft', 'published', 'closed'

    #[Url(as: 'class')]
    public string $classFilter = 'all';

    // Modal state
    public bool $showFormModal = false;

    public bool $showDeleteModal = false;

    public ?int $editingActivityId = null;

    public ?int $activityToDeleteId = null;

    // Form fields
    public ?int $classId = null;

    public string $title = '';

    public string $description = '';

    public string $instructions = '';

    public string $ageGroup = '3-4 years';

    public string $publishDate = '';

    public string $deadline = '';

    public bool $allowLate = true;

    public string $status = 'draft';

    /**
     * @var list<string>
     */
    public array $ageGroupPresets = [
        '1-2 years',
        '2-3 years',
        '3-4 years',
        '4-5 years',
        'All Nursery',
    ];

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'classId' => ['required', 'integer', 'exists:school_classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'instructions' => ['required', 'string', 'min:5'],
            'ageGroup' => ['required', 'string', 'max:50'],
            'publishDate' => ['required', 'date'],
            'deadline' => ['nullable', 'date', 'after_or_equal:publishDate'],
            'allowLate' => ['boolean'],
            'status' => ['required', 'in:draft,published,closed'],
        ];
    }

    public function mount(): void
    {
        $this->publishDate = now()->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingClassFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->editingActivityId = null;

        /** @var User $user */
        $user = auth()->user();
        $institutionId = current_institution_id() ?? 1;

        // Pick default class (prefer first assigned nursery group)
        $firstClass = SchoolClass::where('institution_id', $institutionId)
            ->when(! $user->hasAnyRole([RoleName::SuperAdmin->value, RoleName::Admin->value]), function (Builder $q) use ($user) {
                $q->where('teacher_id', $user->id);
            })
            ->first();

        $this->classId = $firstClass?->id;
        $this->title = '';
        $this->description = '';
        $this->instructions = '';
        $this->ageGroup = '3-4 years';
        $this->publishDate = now()->toDateString();
        $this->deadline = '';
        $this->allowLate = true;
        $this->status = 'draft';

        $this->showFormModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $institutionId = current_institution_id() ?? 1;

        /** @var Activity $activity */
        $activity = Activity::where('institution_id', $institutionId)->findOrFail($id);
        $this->authorize('update', $activity);

        $this->editingActivityId = $activity->id;
        $this->classId = $activity->class_id;
        $this->title = $activity->title;
        $this->description = $activity->description ?? '';
        $this->instructions = $activity->instructions;
        $this->ageGroup = $activity->age_group;
        $this->publishDate = $activity->publish_date ? $activity->publish_date->toDateString() : now()->toDateString();
        $this->deadline = $activity->deadline ? $activity->deadline->format('Y-m-d\TH:i') : '';
        $this->allowLate = $activity->allow_late;
        $this->status = $activity->status->value;

        $this->showFormModal = true;
    }

    public function saveActivity(): void
    {
        $validated = $this->validate();
        $institutionId = current_institution_id() ?? 1;
        /** @var User $user */
        $user = auth()->user();

        // Verify target class belongs to same institution
        $class = SchoolClass::where('institution_id', $institutionId)->findOrFail($this->classId);

        $data = [
            'institution_id' => $institutionId,
            'class_id' => $class->id,
            'title' => trim($this->title),
            'description' => trim($this->description) !== '' ? trim($this->description) : null,
            'instructions' => trim($this->instructions),
            'age_group' => trim($this->ageGroup),
            'publish_date' => $this->publishDate ? Carbon::parse($this->publishDate)->toDateString() : now()->toDateString(),
            'deadline' => $this->deadline ? Carbon::parse($this->deadline) : null,
            'allow_late' => (bool) $this->allowLate,
            'status' => $this->status,
        ];

        if ($this->editingActivityId) {
            /** @var Activity $activity */
            $activity = Activity::where('institution_id', $institutionId)->findOrFail($this->editingActivityId);
            $this->authorize('update', $activity);
            $activity->update($data);
            session()->flash('success', __('Activity updated successfully.'));
        } else {
            $this->authorize('create', Activity::class);
            $data['created_by'] = $user->id;
            Activity::create($data);
            session()->flash('success', __('New activity created successfully.'));
        }

        $this->showFormModal = false;
        $this->resetPage();
    }

    public function publishActivity(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;
        /** @var Activity $activity */
        $activity = Activity::where('institution_id', $institutionId)->findOrFail($id);
        $this->authorize('publish', $activity);

        $activity->update(['status' => ActivityStatus::Published->value]);
        session()->flash('success', __("Activity ':title' published.", ['title' => $activity->title]));
    }

    public function closeActivity(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;
        /** @var Activity $activity */
        $activity = Activity::where('institution_id', $institutionId)->findOrFail($id);
        $this->authorize('close', $activity);

        $activity->update(['status' => ActivityStatus::Closed->value]);
        session()->flash('success', __("Activity ':title' closed.", ['title' => $activity->title]));
    }

    public function reopenActivity(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;
        /** @var Activity $activity */
        $activity = Activity::where('institution_id', $institutionId)->findOrFail($id);
        $this->authorize('publish', $activity);

        $activity->update(['status' => ActivityStatus::Published->value]);
        session()->flash('success', __("Activity ':title' reopened.", ['title' => $activity->title]));
    }

    public function confirmDelete(int $id): void
    {
        $institutionId = current_institution_id() ?? 1;
        /** @var Activity $activity */
        $activity = Activity::where('institution_id', $institutionId)->findOrFail($id);
        $this->authorize('delete', $activity);

        $this->activityToDeleteId = $activity->id;
        $this->showDeleteModal = true;
    }

    public function deleteActivity(): void
    {
        if (! $this->activityToDeleteId) {
            return;
        }

        $institutionId = current_institution_id() ?? 1;
        /** @var Activity $activity */
        $activity = Activity::where('institution_id', $institutionId)->findOrFail($this->activityToDeleteId);
        $this->authorize('delete', $activity);

        $title = $activity->title;
        $activity->delete();

        $this->showDeleteModal = false;
        $this->activityToDeleteId = null;

        session()->flash('success', __("Activity ':title' deleted.", ['title' => $title]));
    }

    public function render(): View
    {
        $institutionId = current_institution_id() ?? 1;
        /** @var User $user */
        $user = auth()->user();

        // Available classes for dropdown and filter
        $classes = SchoolClass::where('institution_id', $institutionId)
            ->when(! $user->hasAnyRole([RoleName::SuperAdmin->value, RoleName::Admin->value]), function (Builder $q) use ($user) {
                $q->where(function (Builder $sub) use ($user) {
                    $sub->where('teacher_id', $user->id)
                        ->orWhere('kind', ClassKind::NurseryGroup);
                });
            })
            ->orderBy('title')
            ->get();

        // Query activities
        $query = Activity::where('institution_id', $institutionId)
            ->with(['schoolClass', 'creator', 'videos', 'materials']);

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->classFilter !== 'all') {
            $query->where('class_id', (int) $this->classFilter);
        }

        if (! empty(trim($this->search))) {
            $term = '%'.trim($this->search).'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('instructions', 'like', $term);
            });
        }

        $activities = $query->orderByDesc('created_at')->paginate(10);

        // Stats summary
        $stats = [
            'total' => Activity::where('institution_id', $institutionId)->count(),
            'published' => Activity::where('institution_id', $institutionId)->where('status', ActivityStatus::Published->value)->count(),
            'draft' => Activity::where('institution_id', $institutionId)->where('status', ActivityStatus::Draft->value)->count(),
            'closed' => Activity::where('institution_id', $institutionId)->where('status', ActivityStatus::Closed->value)->count(),
        ];

        return view('livewire.teacher.activity-manager', [
            'activities' => $activities,
            'classes' => $classes,
            'stats' => $stats,
        ]);
    }
}
