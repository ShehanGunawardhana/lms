<?php

namespace App\Livewire\Parent;

use App\Enums\ActivityStatus;
use App\Models\Activity;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * @property-read Collection<int, Student> $children
 * @property-read Student|null $selectedChild
 */
#[Layout('layouts.app')]
class ParentActivityFeed extends Component
{
    use WithPagination;

    #[Url(as: 'child')]
    public ?int $selectedChildId = null;

    #[Url(as: 'status')]
    public string $statusFilter = 'all';

    #[Url(as: 'q')]
    public string $search = '';

    public function mount(?int $child = null): void
    {
        if ($child !== null) {
            $this->selectedChildId = $child;
        }

        // Validate selected child belongs to parent
        $children = $this->children;
        if ($this->selectedChildId !== null && ! $children->contains('id', $this->selectedChildId)) {
            $this->selectedChildId = $children->first()?->id;
        } elseif ($this->selectedChildId === null && $children->isNotEmpty()) {
            $this->selectedChildId = $children->first()->id;
        }
    }

    public function selectChild(int $childId): void
    {
        $children = $this->children;
        if ($children->contains('id', $childId)) {
            $this->selectedChildId = $childId;
            $this->resetPage();
        }
    }

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return Collection<int, Student>
     */
    public function getChildrenProperty(): Collection
    {
        /** @var User|null $user */
        $user = auth()->user();
        if ($user === null) {
            return collect();
        }

        /** @var Guardian|null $guardian */
        $guardian = $user->guardian ?? Guardian::where('user_id', $user->id)->first();
        if ($guardian === null) {
            return collect();
        }

        return $guardian->students()
            ->with(['enrollments.schoolClass', 'photo'])
            ->get();
    }

    public function getSelectedChildProperty(): ?Student
    {
        $children = $this->children;

        if ($this->selectedChildId !== null) {
            return $children->firstWhere('id', $this->selectedChildId) ?? $children->first();
        }

        return $children->first();
    }

    public function render(): View
    {
        $selectedChild = $this->selectedChild;
        $children = $this->children;

        $metrics = [
            'total' => 0,
            'todo' => 0,
            'submitted' => 0,
            'reviewed' => 0,
        ];

        if ($selectedChild === null) {
            return view('livewire.parent.parent-activity-feed', [
                'activities' => null,
                'children' => $children,
                'selectedChild' => null,
                'metrics' => $metrics,
            ]);
        }

        /** @var list<int> $enrolledClassIds */
        $enrolledClassIds = $selectedChild->enrollments()
            ->where('status', 'active')
            ->pluck('class_id')
            ->all();

        // Calculate summary metrics across all activities for enrolled classes
        if (! empty($enrolledClassIds)) {
            $allChildActivities = Activity::query()
                ->whereIn('class_id', $enrolledClassIds)
                ->whereIn('status', [ActivityStatus::Published->value, ActivityStatus::Closed->value])
                ->with(['submissions' => fn ($q) => $q->where('student_id', $selectedChild->id)])
                ->get();

            $metrics['total'] = $allChildActivities->count();
            $metrics['todo'] = $allChildActivities->filter(fn ($a) => $a->submissions->isEmpty())->count();
            $metrics['submitted'] = $allChildActivities->filter(fn ($a) => $a->submissions->isNotEmpty() && in_array($a->submissions->first()?->status->value ?? '', ['submitted', 'late'], true))->count();
            $metrics['reviewed'] = $allChildActivities->filter(fn ($a) => $a->submissions->isNotEmpty() && ($a->submissions->first()?->status->value ?? '') === 'reviewed')->count();
        }

        $activitiesQuery = Activity::query()
            ->whereIn('class_id', $enrolledClassIds)
            ->whereIn('status', [ActivityStatus::Published->value, ActivityStatus::Closed->value])
            ->with([
                'schoolClass',
                'videos',
                'materials.fileAsset',
                'submissions' => fn ($q) => $q->where('student_id', $selectedChild->id),
            ]);

        if (trim($this->search) !== '') {
            $searchTerm = '%'.trim($this->search).'%';
            $activitiesQuery->where(function (Builder $q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            });
        }

        if ($this->statusFilter === 'not_started' || $this->statusFilter === 'todo') {
            $activitiesQuery->whereDoesntHave('submissions', function (Builder $q) use ($selectedChild) {
                $q->where('student_id', $selectedChild->id);
            });
        } elseif ($this->statusFilter === 'submitted') {
            $activitiesQuery->whereHas('submissions', function (Builder $q) use ($selectedChild) {
                $q->where('student_id', $selectedChild->id)
                    ->whereIn('status', ['submitted', 'late']);
            });
        } elseif ($this->statusFilter === 'reviewed') {
            $activitiesQuery->whereHas('submissions', function (Builder $q) use ($selectedChild) {
                $q->where('student_id', $selectedChild->id)
                    ->where('status', 'reviewed');
            });
        } elseif ($this->statusFilter === 'returned') {
            $activitiesQuery->whereHas('submissions', function (Builder $q) use ($selectedChild) {
                $q->where('student_id', $selectedChild->id)
                    ->where('status', 'returned');
            });
        }

        $activities = $activitiesQuery
            ->orderByRaw("CASE WHEN status = 'published' THEN 0 ELSE 1 END")
            ->orderBy('deadline')
            ->orderByDesc('id')
            ->paginate(9);

        return view('livewire.parent.parent-activity-feed', [
            'activities' => $activities,
            'children' => $children,
            'selectedChild' => $selectedChild,
            'metrics' => $metrics,
        ]);
    }
}
