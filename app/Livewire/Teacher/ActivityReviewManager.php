<?php

declare(strict_types=1);

namespace App\Livewire\Teacher;

use App\Enums\ActivitySubmissionStatus;
use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Student;
use App\Services\NotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Review Activity Submissions')]
class ActivityReviewManager extends Component
{
    public Activity $activity;

    public string $search = '';

    public string $statusFilter = 'all'; // all, pending, reviewed, returned, missing

    public ?int $selectedStudentId = null;

    public ?int $selectedSubmissionId = null;

    public bool $showReviewModal = false;

    public bool $showReturnModal = false;

    public bool $showHistoryModal = false;

    public string $reviewRating = 'superstar';

    public string $reviewFeedback = '';

    public string $returnReason = '';

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

    public function mount(Activity $activity): void
    {
        $this->authorize('reviewSubmissions', $activity);
        $this->activity = $activity->load(['schoolClass', 'extensions']);
    }

    public function openReviewModal(int $studentId): void
    {
        $submission = ActivitySubmission::where('activity_id', $this->activity->id)
            ->where('student_id', $studentId)
            ->orderByDesc('version')
            ->first();

        if (! $submission) {
            session()->flash('error', __('No submission found for this student.'));

            return;
        }

        $this->selectedStudentId = $studentId;
        $this->selectedSubmissionId = $submission->id;
        $this->reviewRating = $submission->rating ?: 'superstar';
        $this->reviewFeedback = $submission->feedback ?: '';
        $this->showReviewModal = true;
    }

    public function closeReviewModal(): void
    {
        $this->showReviewModal = false;
        $this->selectedStudentId = null;
        $this->selectedSubmissionId = null;
        $this->reviewFeedback = '';
        $this->resetValidation();
    }

    public function setSticker(string $sticker): void
    {
        if (array_key_exists($sticker, self::STICKERS)) {
            $this->reviewRating = $sticker;
        }
    }

    public function submitReview(): void
    {
        $this->validate([
            'reviewRating' => ['required', 'string'],
            'reviewFeedback' => ['nullable', 'string', 'max:2000'],
        ]);

        /** @var ActivitySubmission $submission */
        $submission = ActivitySubmission::where('id', $this->selectedSubmissionId)
            ->where('activity_id', $this->activity->id)
            ->firstOrFail();

        $this->authorize('review', $submission);

        $submission->markReviewed(auth()->user(), $this->reviewFeedback ?: null, $this->reviewRating);

        // Dispatch notification to parent guardian
        $guardianUser = $submission->guardian?->user;
        if ($guardianUser) {
            $stickerLabel = self::STICKERS[$this->reviewRating]['label'] ?? $this->reviewRating;
            app(NotificationService::class)->send(
                user: $guardianUser,
                eventType: 'submission_reviewed',
                title: __('Activity Reviewed: :title', ['title' => $this->activity->title]),
                message: __('Teacher reviewed :child\'s submission for ":title" with a :sticker rating!', [
                    'child' => $submission->student !== null ? $submission->student->full_name : __('your child'),
                    'title' => $this->activity->title,
                    'sticker' => $stickerLabel,
                ]),
                data: [
                    'activity_id' => $this->activity->id,
                    'student_id' => $submission->student_id,
                    'submission_id' => $submission->id,
                    'rating' => $this->reviewRating,
                ],
                url: route('parent.activities.show', $this->activity),
            );
        }

        session()->flash('success', __('Submission successfully reviewed and feedback saved!'));
        $this->closeReviewModal();
    }

    public function openReturnModal(int $studentId): void
    {
        $submission = ActivitySubmission::where('activity_id', $this->activity->id)
            ->where('student_id', $studentId)
            ->orderByDesc('version')
            ->first();

        if (! $submission) {
            session()->flash('error', __('No submission found for this student.'));

            return;
        }

        $this->selectedStudentId = $studentId;
        $this->selectedSubmissionId = $submission->id;
        $this->returnReason = '';
        $this->showReturnModal = true;
    }

    public function closeReturnModal(): void
    {
        $this->showReturnModal = false;
        $this->selectedStudentId = null;
        $this->selectedSubmissionId = null;
        $this->returnReason = '';
        $this->resetValidation();
    }

    public function confirmReturn(): void
    {
        $this->validate([
            'returnReason' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        /** @var ActivitySubmission $submission */
        $submission = ActivitySubmission::where('id', $this->selectedSubmissionId)
            ->where('activity_id', $this->activity->id)
            ->firstOrFail();

        $this->authorize('review', $submission);

        $submission->markReturned(auth()->user(), $this->returnReason);

        // Dispatch notification to guardian
        $guardianUser = $submission->guardian?->user;
        if ($guardianUser) {
            app(NotificationService::class)->send(
                user: $guardianUser,
                eventType: 'submission_returned',
                title: __('Submission Returned for Revision: :title', ['title' => $this->activity->title]),
                message: __('Teacher requested adjustments for :child\'s submission on ":title": :reason', [
                    'child' => $submission->student !== null ? $submission->student->full_name : __('your child'),
                    'title' => $this->activity->title,
                    'reason' => $this->returnReason,
                ]),
                data: [
                    'activity_id' => $this->activity->id,
                    'student_id' => $submission->student_id,
                    'submission_id' => $submission->id,
                    'feedback' => $this->returnReason,
                ],
                url: route('parent.activities.show', $this->activity),
            );
        }

        session()->flash('success', __('Submission returned to parent for correction.'));
        $this->closeReturnModal();
    }

    public function reopenSubmission(int $submissionId): void
    {
        /** @var ActivitySubmission $submission */
        $submission = ActivitySubmission::where('id', $submissionId)
            ->where('activity_id', $this->activity->id)
            ->firstOrFail();

        $this->authorize('review', $submission);

        $submission->reopen(auth()->user());

        session()->flash('success', __('Submission reopened. Status set back to Submitted.'));
    }

    public function openHistoryModal(int $studentId): void
    {
        $this->selectedStudentId = $studentId;
        $this->showHistoryModal = true;
    }

    public function closeHistoryModal(): void
    {
        $this->showHistoryModal = false;
        $this->selectedStudentId = null;
    }

    public function render(): View
    {
        /** @var Collection<int, Student> $enrolledStudents */
        $enrolledStudents = $this->activity->schoolClass
            ? $this->activity->schoolClass->students()
                ->wherePivot('status', 'active')
                ->with(['guardians.user'])
                ->get()
            : collect();

        // Load all active non-deleted submissions for this activity
        /** @var Collection<int, ActivitySubmission> $allSubmissions */
        $allSubmissions = ActivitySubmission::where('activity_id', $this->activity->id)
            ->with(['fileAsset', 'reviewer', 'guardian.user'])
            ->orderByDesc('version')
            ->get();

        $latestSubmissionsByStudent = $allSubmissions->groupBy('student_id')->map->first();

        // Calculate summary counters
        $totalEnrolled = $enrolledStudents->count();
        $pendingCount = 0;
        $reviewedCount = 0;
        $returnedCount = 0;
        $missingCount = 0;

        /** @var list<array{student: Student, submission: ActivitySubmission|null, status_key: string, versions_count: int}> $roster */
        $roster = [];

        foreach ($enrolledStudents as $student) {
            /** @var ActivitySubmission|null $sub */
            $sub = $latestSubmissionsByStudent->get($student->id);
            $statusKey = $sub !== null ? $sub->status->value : 'not_started';

            if ($sub === null) {
                $missingCount++;
            } elseif ($sub->status === ActivitySubmissionStatus::Reviewed) {
                $reviewedCount++;
            } elseif ($sub->status === ActivitySubmissionStatus::Returned) {
                $returnedCount++;
            } else {
                $pendingCount++;
            }

            // Search filter
            if ($this->search !== '') {
                $needle = mb_strtolower(trim($this->search));
                $matchesName = str_contains(mb_strtolower($student->full_name), $needle);
                $matchesCode = str_contains(mb_strtolower($student->student_code), $needle);
                if (! $matchesName && ! $matchesCode) {
                    continue;
                }
            }

            // Status filter
            if ($this->statusFilter === 'pending' && (! $sub || ! in_array($sub->status, [ActivitySubmissionStatus::Submitted, ActivitySubmissionStatus::Late], true))) {
                continue;
            }
            if ($this->statusFilter === 'reviewed' && (! $sub || $sub->status !== ActivitySubmissionStatus::Reviewed)) {
                continue;
            }
            if ($this->statusFilter === 'returned' && (! $sub || $sub->status !== ActivitySubmissionStatus::Returned)) {
                continue;
            }
            if ($this->statusFilter === 'missing' && $sub !== null) {
                continue;
            }

            $roster[] = [
                'student' => $student,
                'submission' => $sub,
                'status_key' => $statusKey,
                'versions_count' => $allSubmissions->where('student_id', $student->id)->count(),
            ];
        }

        $submittedTotal = $pendingCount + $reviewedCount + $returnedCount;
        $submissionPercentage = $totalEnrolled > 0 ? (int) round(($submittedTotal / $totalEnrolled) * 100) : 0;

        $selectedStudent = $this->selectedStudentId
            ? $enrolledStudents->firstWhere('id', $this->selectedStudentId)
            : null;

        $selectedSubmission = $this->selectedSubmissionId
            ? $allSubmissions->firstWhere('id', $this->selectedSubmissionId)
            : null;

        $historyVersions = ($this->showHistoryModal && $this->selectedStudentId)
            ? ActivitySubmission::withTrashed()
                ->where('activity_id', $this->activity->id)
                ->where('student_id', $this->selectedStudentId)
                ->with(['fileAsset', 'reviewer'])
                ->orderByDesc('version')
                ->get()
            : collect();

        return view('livewire.teacher.activity-review-manager', [
            'roster' => $roster,
            'totalEnrolled' => $totalEnrolled,
            'pendingCount' => $pendingCount,
            'reviewedCount' => $reviewedCount,
            'returnedCount' => $returnedCount,
            'missingCount' => $missingCount,
            'submissionPercentage' => $submissionPercentage,
            'selectedStudent' => $selectedStudent,
            'selectedSubmission' => $selectedSubmission,
            'historyVersions' => $historyVersions,
            'stickers' => self::STICKERS,
        ]);
    }
}
