<?php

namespace App\Livewire\Parent;

use App\Enums\ActivitySubmissionStatus;
use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * @property-read Collection<int, Student> $eligibleChildren
 * @property-read Student|null $selectedChild
 */
#[Layout('layouts.app')]
class ParentActivityShow extends Component
{
    public Activity $activity;

    #[Url(as: 'child')]
    public ?int $selectedChildId = null;

    public ?string $watchVideoId = null;

    public ?string $watchVideoTitle = null;

    public function mount(Activity $activity, ?int $child = null): void
    {
        $this->authorize('view', $activity);

        $this->activity = $activity->load([
            'schoolClass',
            'videos',
            'materials.fileAsset',
            'creator',
        ]);

        $eligibleChildren = $this->eligibleChildren;

        if ($child !== null && $eligibleChildren->contains('id', $child)) {
            $this->selectedChildId = $child;
        } else {
            $this->selectedChildId = $eligibleChildren->first()?->id;
        }

        // If parent has no children enrolled in this activity's class, block
        if ($this->selectedChildId === null) {
            abort(403, __('You do not have a child enrolled in this activity class.'));
        }
    }

    public function selectChild(int $childId): void
    {
        if ($this->eligibleChildren->contains('id', $childId)) {
            $this->selectedChildId = $childId;
        }
    }

    public function openVideo(string $videoId, ?string $title = null): void
    {
        $this->watchVideoId = $videoId;
        $this->watchVideoTitle = $title;
    }

    public function closeVideo(): void
    {
        $this->watchVideoId = null;
        $this->watchVideoTitle = null;
    }

    /**
     * @return Collection<int, Student>
     */
    public function getEligibleChildrenProperty(): Collection
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
            ->whereHas('enrollments', function ($query) {
                $query->where('class_id', $this->activity->class_id)
                    ->where('status', 'active');
            })
            ->with(['photo', 'enrollments.schoolClass'])
            ->get();
    }

    public function getSelectedChildProperty(): ?Student
    {
        return $this->eligibleChildren->firstWhere('id', $this->selectedChildId) ?? $this->eligibleChildren->first();
    }

    public function render(): View
    {
        $selectedChild = $this->selectedChild;
        /** @var ActivitySubmission|null $submission */
        $submission = $selectedChild !== null
            ? $this->activity->submissionForStudent($selectedChild->id)
            : null;

        $submissionStatus = $selectedChild !== null
            ? $this->activity->statusForStudent($selectedChild->id)
            : ActivitySubmissionStatus::NotStarted;

        return view('livewire.parent.parent-activity-show', [
            'activity' => $this->activity,
            'eligibleChildren' => $this->eligibleChildren,
            'selectedChild' => $selectedChild,
            'submission' => $submission,
            'submissionStatus' => $submissionStatus,
        ]);
    }
}
