<?php

declare(strict_types=1);

namespace App\Livewire\Parent;

use App\Enums\ActivityStatus;
use App\Enums\ActivitySubmissionStatus;
use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ParentChildProgressView extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public ?int $selectedChildId = null;

    public string $activeTab = 'activities'; // 'activities' | 'stickers' | 'quizzes'

    public string $statusFilter = 'all'; // 'all' | 'reviewed' | 'submitted' | 'returned' | 'not_started'

    public string $search = '';

    /**
     * @var array<string, array{label: string, emoji: string, bg: string}>
     */
    public const STICKERS = [
        'superstar' => ['label' => 'Superstar!', 'emoji' => '🌟', 'bg' => 'bg-yellow-100 text-yellow-800 border-yellow-300'],
        'star' => ['label' => 'Gold Star', 'emoji' => '⭐', 'bg' => 'bg-amber-100 text-amber-800 border-amber-300'],
        'creative' => ['label' => 'So Creative!', 'emoji' => '🎨', 'bg' => 'bg-purple-100 text-purple-800 border-purple-300'],
        'welldone' => ['label' => 'Well Done!', 'emoji' => '👏', 'bg' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
        'excellent' => ['label' => 'Out of this World!', 'emoji' => '🚀', 'bg' => 'bg-indigo-100 text-indigo-800 border-indigo-300'],
        'lovely' => ['label' => 'Lovely Work!', 'emoji' => '💖', 'bg' => 'bg-pink-100 text-pink-800 border-pink-300'],
        'thumbsup' => ['label' => 'Keep It Up!', 'emoji' => '👍', 'bg' => 'bg-blue-100 text-blue-800 border-blue-300'],
        'sparkles' => ['label' => 'Brilliant!', 'emoji' => '✨', 'bg' => 'bg-teal-100 text-teal-800 border-teal-300'],
    ];

    public function mount(?Student $student = null): void
    {
        if ($student !== null && $student->exists) {
            $this->authorize('viewProgress', $student);
            $this->selectedChildId = $student->id;
        }

        $children = $this->getChildren();

        if ($this->selectedChildId === null && $children->isNotEmpty()) {
            /** @var Student $firstChild */
            $firstChild = $children->first();
            $this->selectedChildId = $firstChild->id;
        }
    }

    public function selectChild(int $childId): void
    {
        $children = $this->getChildren();
        $child = $children->firstWhere('id', $childId);

        if ($child !== null) {
            $this->authorize('viewProgress', $child);
            $this->selectedChildId = $child->id;
            $this->resetPage();
        }
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['activities', 'stickers', 'quizzes'], true) ? $tab : 'activities';
        $this->resetPage();
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
    public function getChildren(): Collection
    {
        /** @var User|null $user */
        $user = auth()->user();

        if ($user === null) {
            return new Collection;
        }

        /** @var Guardian|null $guardian */
        $guardian = $user->guardian ?? Guardian::where('user_id', $user->id)->first();

        if ($guardian === null) {
            return new Collection;
        }

        return $guardian->students()
            ->with(['classes', 'photo'])
            ->get();
    }

    public function render(): View
    {
        $children = $this->getChildren();
        $currentChild = null;

        if ($this->selectedChildId !== null) {
            $currentChild = $children->firstWhere('id', $this->selectedChildId);
        }

        if ($currentChild === null) {
            return view('livewire.parent.parent-child-progress-view', [
                'children' => $children,
                'currentChild' => null,
                'metrics' => [
                    'total' => 0,
                    'reviewed' => 0,
                    'submitted' => 0,
                    'returned' => 0,
                    'not_started' => 0,
                    'completion_rate' => 0,
                    'total_stickers' => 0,
                ],
                'stickersSubmissions' => collect(),
                'activities' => null,
            ]);
        }

        /** @var list<int> $enrolledClassIds */
        $enrolledClassIds = $currentChild->classes()
            ->wherePivot('status', 'active')
            ->pluck('school_classes.id')
            ->all();

        // 1. Gather all activities for calculating milestone stats
        $allActivities = Activity::query()
            ->whereIn('class_id', $enrolledClassIds)
            ->whereIn('status', [ActivityStatus::Published->value, ActivityStatus::Closed->value])
            ->with(['submissions' => fn ($q) => $q->where('student_id', $currentChild->id)->with('reviewer')])
            ->get();

        $totalActivities = $allActivities->count();
        $reviewedCount = $allActivities->filter(fn ($a) => $a->submissions->isNotEmpty() && $a->submissions->first()->isReviewed())->count();
        $submittedCount = $allActivities->filter(fn ($a) => $a->submissions->isNotEmpty() && in_array($a->submissions->first()->status->value, ['submitted', 'late'], true))->count();
        $returnedCount = $allActivities->filter(fn ($a) => $a->submissions->isNotEmpty() && $a->submissions->first()->isReturned())->count();
        $notStartedCount = $allActivities->filter(fn ($a) => $a->submissions->isEmpty())->count();

        $completionRate = $totalActivities > 0 ? (int) round(($reviewedCount / $totalActivities) * 100) : 0;

        // 2. Gather all reviewed submissions with stickers
        $stickersSubmissions = ActivitySubmission::query()
            ->where('student_id', $currentChild->id)
            ->where('status', ActivitySubmissionStatus::Reviewed->value)
            ->whereNotNull('rating')
            ->where('rating', '!=', '')
            ->with(['activity', 'reviewer'])
            ->orderByDesc('reviewed_at')
            ->get();

        $totalStickers = $stickersSubmissions->count();

        $metrics = [
            'total' => $totalActivities,
            'reviewed' => $reviewedCount,
            'submitted' => $submittedCount,
            'returned' => $returnedCount,
            'not_started' => $notStartedCount,
            'completion_rate' => $completionRate,
            'total_stickers' => $totalStickers,
        ];

        // 3. Tab 1: Activities Query with search and status filters
        $activitiesQuery = Activity::query()
            ->whereIn('class_id', $enrolledClassIds)
            ->whereIn('status', [ActivityStatus::Published->value, ActivityStatus::Closed->value])
            ->with([
                'schoolClass',
                'videos',
                'materials.fileAsset',
                'submissions' => fn ($q) => $q->where('student_id', $currentChild->id)->with('reviewer'),
            ]);

        if (trim($this->search) !== '') {
            $searchTerm = '%'.trim($this->search).'%';
            $activitiesQuery->where(function (Builder $q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            });
        }

        if ($this->statusFilter === 'reviewed') {
            $activitiesQuery->whereHas('submissions', function (Builder $q) use ($currentChild) {
                $q->where('student_id', $currentChild->id)
                    ->where('status', ActivitySubmissionStatus::Reviewed->value);
            });
        } elseif ($this->statusFilter === 'submitted') {
            $activitiesQuery->whereHas('submissions', function (Builder $q) use ($currentChild) {
                $q->where('student_id', $currentChild->id)
                    ->whereIn('status', [ActivitySubmissionStatus::Submitted->value, ActivitySubmissionStatus::Late->value]);
            });
        } elseif ($this->statusFilter === 'returned') {
            $activitiesQuery->whereHas('submissions', function (Builder $q) use ($currentChild) {
                $q->where('student_id', $currentChild->id)
                    ->where('status', ActivitySubmissionStatus::Returned->value);
            });
        } elseif ($this->statusFilter === 'not_started') {
            $activitiesQuery->whereDoesntHave('submissions', function (Builder $q) use ($currentChild) {
                $q->where('student_id', $currentChild->id);
            });
        }

        $activities = $activitiesQuery
            ->orderByRaw("CASE WHEN status = 'published' THEN 0 ELSE 1 END")
            ->orderBy('deadline')
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.parent.parent-child-progress-view', [
            'children' => $children,
            'currentChild' => $currentChild,
            'metrics' => $metrics,
            'stickersSubmissions' => $stickersSubmissions,
            'activities' => $activities,
            'stickersConfig' => self::STICKERS,
        ]);
    }
}
