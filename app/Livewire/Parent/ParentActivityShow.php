<?php

namespace App\Livewire\Parent;

use App\Enums\ActivitySubmissionStatus;
use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use App\Services\FileService;
use App\Services\NotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * @property-read Collection<int, Student> $eligibleChildren
 * @property-read Student|null $selectedChild
 * @property-read ActivitySubmission|null $currentSubmission
 */
#[Layout('layouts.app')]
class ParentActivityShow extends Component
{
    use WithFileUploads;

    public Activity $activity;

    #[Url(as: 'child')]
    public ?int $selectedChildId = null;

    public ?string $watchVideoId = null;

    public ?string $watchVideoTitle = null;

    // Submission Modals State (§6.3, T-4.6)
    public bool $showSubmitModal = false;

    public bool $showEditNoteModal = false;

    public bool $showReplaceModal = false;

    public bool $showDeleteModal = false;

    public bool $showHistoryModal = false;

    /**
     * @var mixed
     */
    public $submissionFile = null;

    public string $submissionNote = '';

    public string $editingNote = '';

    /**
     * @var mixed
     */
    public $replacementFile = null;

    public string $replacementNote = '';

    public string $submissionError = '';

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

    // --- Parent Submissions Workflows (§6.3, T-4.6) ---

    public function openSubmitModal(): void
    {
        if (! $this->activity->canAcceptSubmissionsForStudent($this->selectedChildId)) {
            $this->submissionError = __('Submissions are currently locked for this activity.');

            return;
        }

        $this->submissionFile = null;
        $this->submissionNote = '';
        $this->submissionError = '';
        $this->showSubmitModal = true;
    }

    public function closeSubmitModal(): void
    {
        $this->showSubmitModal = false;
        $this->submissionFile = null;
        $this->submissionNote = '';
        $this->submissionError = '';
    }

    public function submitWork(
        ?FileService $fileService = null,
        ?NotificationService $notificationService = null
    ): void {
        $fileService ??= app(FileService::class);
        $notificationService ??= app(NotificationService::class);

        /** @var User $currentUser */
        $currentUser = auth()->user();

        if (! $this->activity->canAcceptSubmissionsForStudent($this->selectedChildId)) {
            $this->submissionError = __('The deadline has passed and late submissions are not allowed for this activity.');

            return;
        }

        $this->validate([
            'submissionFile' => ['required', 'file', 'max:51200'], // 50MB
            'submissionNote' => ['nullable', 'string', 'max:1000'],
        ]);

        /** @var Guardian|null $guardian */
        $guardian = $currentUser->guardian ?? Guardian::where('user_id', $currentUser->id)->first();
        if ($guardian === null) {
            abort(403, __('No valid guardian record found for your user.'));
        }

        try {
            $fileAsset = $fileService->store(
                file: $this->submissionFile,
                purpose: 'activity_submission',
                allowedTypes: ['mp4', 'mov', 'webm', 'pdf', 'jpg', 'jpeg', 'png', 'webp'],
                uploader: $currentUser
            );

            $isLate = $this->activity->isPastDeadlineForStudent($this->selectedChildId);
            $status = $isLate ? ActivitySubmissionStatus::Late : ActivitySubmissionStatus::Submitted;

            /** @var int $maxVersion */
            $maxVersion = ActivitySubmission::withTrashed()
                ->where('activity_id', $this->activity->id)
                ->where('student_id', $this->selectedChildId)
                ->max('version') ?? 0;

            $version = $maxVersion + 1;

            $submission = ActivitySubmission::create([
                'institution_id' => $this->activity->institution_id,
                'activity_id' => $this->activity->id,
                'student_id' => $this->selectedChildId,
                'guardian_id' => $guardian->id,
                'file_asset_id' => $fileAsset->id,
                'note' => trim($this->submissionNote) ?: null,
                'status' => $status,
                'version' => $version,
                'is_late' => $isLate,
                'submitted_at' => now(),
            ]);

            audit(
                event: 'activity.submitted',
                description: "Parent submitted work for child '{$this->selectedChild?->full_name}' on '{$this->activity->title}' (v{$version})",
                subject: $submission,
                properties: [
                    'activity_id' => $this->activity->id,
                    'student_id' => $this->selectedChildId,
                    'version' => $version,
                    'is_late' => $isLate,
                ],
                causer: $currentUser,
            );

            // Notify teacher of new submission
            $teacher = $this->activity->creator ?? $this->activity->schoolClass?->teacher;
            if ($teacher !== null) {
                $notificationService->send(
                    user: $teacher,
                    eventType: 'activity_submission',
                    title: __('New Activity Submission: :title', ['title' => $this->activity->title]),
                    message: __(':parent submitted work for :child (v:ver).', [
                        'parent' => $currentUser->name,
                        'child' => $this->selectedChild?->full_name,
                        'ver' => $version,
                    ]),
                    data: [
                        'activity_id' => $this->activity->id,
                        'submission_id' => $submission->id,
                        'student_id' => $this->selectedChildId,
                        'version' => $version,
                    ]
                );
            }

            session()->flash('success', __('Work submitted successfully! Your child’s teacher will review it.'));
            $this->closeSubmitModal();
        } catch (\Throwable $e) {
            $this->submissionError = $e->getMessage();
        }
    }

    public function openEditNoteModal(): void
    {
        $submission = $this->currentSubmission;
        if (! $submission || ! $submission->canBeModifiedByParent()) {
            return;
        }

        $this->editingNote = $submission->note ?? '';
        $this->submissionError = '';
        $this->showEditNoteModal = true;
    }

    public function closeEditNoteModal(): void
    {
        $this->showEditNoteModal = false;
        $this->editingNote = '';
        $this->submissionError = '';
    }

    public function saveNote(): void
    {
        $submission = $this->currentSubmission;
        if (! $submission || ! $submission->canBeModifiedByParent()) {
            abort(403, __('This submission cannot be edited because it is locked or reviewed.'));
        }

        $this->validate([
            'editingNote' => ['nullable', 'string', 'max:1000'],
        ]);

        $submission->update([
            'note' => trim($this->editingNote) ?: null,
        ]);

        audit(
            event: 'activity.submission_note_updated',
            description: "Submission note updated for child '{$this->selectedChild?->full_name}'",
            subject: $submission,
            properties: [
                'activity_id' => $this->activity->id,
                'student_id' => $this->selectedChildId,
                'version' => $submission->version,
            ],
            causer: auth()->user(),
        );

        session()->flash('success', __('Submission note updated successfully.'));
        $this->closeEditNoteModal();
    }

    public function openReplaceModal(): void
    {
        $submission = $this->currentSubmission;
        if (! $submission || ! $submission->canBeModifiedByParent()) {
            return;
        }

        $this->replacementFile = null;
        $this->replacementNote = $submission->note ?? '';
        $this->submissionError = '';
        $this->showReplaceModal = true;
    }

    public function closeReplaceModal(): void
    {
        $this->showReplaceModal = false;
        $this->replacementFile = null;
        $this->replacementNote = '';
        $this->submissionError = '';
    }

    public function replaceWork(
        ?FileService $fileService = null,
        ?NotificationService $notificationService = null
    ): void {
        $fileService ??= app(FileService::class);
        $notificationService ??= app(NotificationService::class);

        $submission = $this->currentSubmission;
        if (! $submission || ! $submission->canBeModifiedByParent()) {
            abort(403, __('This submission cannot be replaced because it is locked or reviewed.'));
        }

        $this->validate([
            'replacementFile' => ['required', 'file', 'max:51200'],
            'replacementNote' => ['nullable', 'string', 'max:1000'],
        ]);

        /** @var User $currentUser */
        $currentUser = auth()->user();

        try {
            $fileAsset = $fileService->store(
                file: $this->replacementFile,
                purpose: 'activity_submission',
                allowedTypes: ['mp4', 'mov', 'webm', 'pdf', 'jpg', 'jpeg', 'png', 'webp'],
                uploader: $currentUser
            );

            $isLate = $this->activity->isPastDeadlineForStudent($this->selectedChildId);
            $status = $isLate ? ActivitySubmissionStatus::Late : ActivitySubmissionStatus::Submitted;
            $newVersion = $submission->version + 1;

            $newSubmission = ActivitySubmission::create([
                'institution_id' => $this->activity->institution_id,
                'activity_id' => $this->activity->id,
                'student_id' => $this->selectedChildId,
                'guardian_id' => $submission->guardian_id,
                'file_asset_id' => $fileAsset->id,
                'note' => trim($this->replacementNote) ?: $submission->note,
                'status' => $status,
                'version' => $newVersion,
                'is_late' => $isLate,
                'submitted_at' => now(),
            ]);

            audit(
                event: 'activity.submission_replaced',
                description: "Submission replaced for child '{$this->selectedChild?->full_name}' (v{$newVersion})",
                subject: $newSubmission,
                properties: [
                    'activity_id' => $this->activity->id,
                    'student_id' => $this->selectedChildId,
                    'previous_version' => $submission->version,
                    'new_version' => $newVersion,
                    'is_late' => $isLate,
                ],
                causer: $currentUser,
            );

            session()->flash('success', __('New submission version (:ver) uploaded successfully.', ['ver' => "v{$newVersion}"]));
            $this->closeReplaceModal();
        } catch (\Throwable $e) {
            $this->submissionError = $e->getMessage();
        }
    }

    public function confirmDelete(): void
    {
        $submission = $this->currentSubmission;
        if (! $submission || ! $submission->canBeModifiedByParent()) {
            return;
        }

        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
    }

    public function deleteSubmission(): void
    {
        $submission = $this->currentSubmission;
        if (! $submission || ! $submission->canBeModifiedByParent()) {
            abort(403, __('This submission cannot be deleted because it is locked or reviewed.'));
        }

        /** @var User $currentUser */
        $currentUser = auth()->user();

        audit(
            event: 'activity.submission_deleted',
            description: "Submission v{$submission->version} deleted for child '{$this->selectedChild?->full_name}'",
            subject: $submission,
            properties: [
                'activity_id' => $this->activity->id,
                'student_id' => $this->selectedChildId,
                'version' => $submission->version,
            ],
            causer: $currentUser,
        );

        $submission->delete();

        session()->flash('success', __('Submission deleted. You can submit fresh work anytime before the deadline.'));
        $this->closeDeleteModal();
    }

    public function openHistoryModal(): void
    {
        $this->showHistoryModal = true;
    }

    public function closeHistoryModal(): void
    {
        $this->showHistoryModal = false;
    }

    public function getCurrentSubmissionProperty(): ?ActivitySubmission
    {
        if ($this->selectedChildId === null) {
            return null;
        }

        return $this->activity->submissionForStudent($this->selectedChildId);
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

        $submissionHistory = $selectedChild !== null
            ? ActivitySubmission::where('activity_id', $this->activity->id)
                ->where('student_id', $selectedChild->id)
                ->orderByDesc('version')
                ->with(['fileAsset', 'reviewer'])
                ->get()
            : collect();

        return view('livewire.parent.parent-activity-show', [
            'activity' => $this->activity,
            'eligibleChildren' => $this->eligibleChildren,
            'selectedChild' => $selectedChild,
            'submission' => $submission,
            'submissionStatus' => $submissionStatus,
            'submissionHistory' => $submissionHistory,
        ]);
    }
}
