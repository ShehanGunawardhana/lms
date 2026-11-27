<?php

declare(strict_types=1);

namespace App\Livewire\Teacher;

use App\Enums\AssignmentSubmissionStatus;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Student;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Review Assignment Coursework')]
class AssignmentReviewManager extends Component
{
    use AuthorizesRequests;

    public Assignment $assignment;

    public string $search = '';

    public string $statusFilter = 'all'; // all, pending, graded, returned, missing

    public ?int $selectedStudentId = null;

    public ?int $selectedSubmissionId = null;

    // Modals
    public bool $showGradeModal = false;

    public bool $showReturnModal = false;

    public bool $showHistoryModal = false;

    // Grade form fields
    public ?int $reviewMarks = null;

    public string $reviewFeedback = '';

    // Return form fields
    public string $returnReason = '';

    public function mount(Assignment $assignment): void
    {
        $institutionId = current_institution_id() ?? 1;

        if ($assignment->institution_id !== $institutionId) {
            abort(404);
        }

        $this->authorize('reviewSubmissions', $assignment);

        $this->assignment = $assignment->load(['schoolClass', 'extensions']);
    }

    public function updatingSearch(): void
    {
        $this->resetSelected();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetSelected();
    }

    protected function resetSelected(): void
    {
        $this->selectedStudentId = null;
        $this->selectedSubmissionId = null;
    }

    public function openGradeModal(int $studentId): void
    {
        $this->resetValidation();

        $submission = AssignmentSubmission::where('assignment_id', $this->assignment->id)
            ->where('student_id', $studentId)
            ->orderByDesc('version')
            ->first();

        if (! $submission) {
            session()->flash('error', __('No active submission found for this student.'));

            return;
        }

        $this->authorize('grade', $submission);

        $this->selectedStudentId = $studentId;
        $this->selectedSubmissionId = $submission->id;
        $this->reviewMarks = $submission->marks ?? $this->assignment->total_marks;
        $this->reviewFeedback = $submission->feedback ?? '';
        $this->showGradeModal = true;
    }

    public function closeGradeModal(): void
    {
        $this->showGradeModal = false;
        $this->selectedStudentId = null;
        $this->selectedSubmissionId = null;
        $this->reviewMarks = null;
        $this->reviewFeedback = '';
        $this->resetValidation();
    }

    public function submitGrade(?NotificationService $notificationService = null): void
    {
        $notificationService ??= app(NotificationService::class);

        $maxMarks = $this->assignment->total_marks;
        $this->validate([
            'reviewMarks' => ['required', 'integer', 'min:0', "max:{$maxMarks}"],
            'reviewFeedback' => ['nullable', 'string', 'max:2000'],
        ]);

        if (! $this->selectedSubmissionId) {
            return;
        }

        /** @var AssignmentSubmission $submission */
        $submission = AssignmentSubmission::where('id', $this->selectedSubmissionId)
            ->where('assignment_id', $this->assignment->id)
            ->firstOrFail();

        $this->authorize('grade', $submission);

        /** @var User $reviewer */
        $reviewer = auth()->user();
        $marks = (int) $this->reviewMarks;
        $feedback = trim($this->reviewFeedback) !== '' ? trim($this->reviewFeedback) : null;

        $submission->markGraded($reviewer, $marks, $feedback);

        // Notify affected student user
        $studentUser = $submission->student?->user;
        if ($studentUser !== null) {
            $notificationService->send(
                user: $studentUser,
                eventType: 'submission_reviewed',
                title: __('Assignment Graded: :title', ['title' => $this->assignment->title]),
                message: __('Your coursework for ":title" has been graded: :marks/:total marks. Feedback: :feedback', [
                    'title' => $this->assignment->title,
                    'marks' => $marks,
                    'total' => $this->assignment->total_marks,
                    'feedback' => $feedback ?: __('No additional written remarks.'),
                ]),
                data: [
                    'assignment_id' => $this->assignment->id,
                    'submission_id' => $submission->id,
                    'marks' => $marks,
                    'total_marks' => $this->assignment->total_marks,
                ],
                url: route('assignments.show', ['assignment' => $this->assignment->id]),
            );
        }

        // Notify student guardians
        $submission->student?->loadMissing('guardians.user');
        if ($submission->student !== null) {
            foreach ($submission->student->guardians as $guardian) {
                if ($guardian->user !== null) {
                    $notificationService->send(
                        user: $guardian->user,
                        eventType: 'submission_reviewed',
                        title: __('Assignment Graded: :title', ['title' => $this->assignment->title]),
                        message: __('Teacher graded :student\'s submission for ":title": :marks/:total marks.', [
                            'student' => $submission->student->full_name,
                            'title' => $this->assignment->title,
                            'marks' => $marks,
                            'total' => $this->assignment->total_marks,
                        ]),
                        data: [
                            'assignment_id' => $this->assignment->id,
                            'student_id' => $submission->student_id,
                            'submission_id' => $submission->id,
                            'marks' => $marks,
                        ],
                        url: route('assignments.show', [
                            'assignment' => $this->assignment->id,
                            'student' => $submission->student_id,
                        ]),
                    );
                }
            }
        }

        session()->flash('success', __('Submission successfully graded with :marks marks and accepted.', ['marks' => $marks]));
        $this->closeGradeModal();
    }

    public function openReturnModal(int $studentId): void
    {
        $this->resetValidation();

        $submission = AssignmentSubmission::where('assignment_id', $this->assignment->id)
            ->where('student_id', $studentId)
            ->orderByDesc('version')
            ->first();

        if (! $submission) {
            session()->flash('error', __('No active submission found for this student.'));

            return;
        }

        $this->authorize('grade', $submission);

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

    public function confirmReturn(?NotificationService $notificationService = null): void
    {
        $notificationService ??= app(NotificationService::class);

        $this->validate([
            'returnReason' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        if (! $this->selectedSubmissionId) {
            return;
        }

        /** @var AssignmentSubmission $submission */
        $submission = AssignmentSubmission::where('id', $this->selectedSubmissionId)
            ->where('assignment_id', $this->assignment->id)
            ->firstOrFail();

        $this->authorize('grade', $submission);

        /** @var User $reviewer */
        $reviewer = auth()->user();
        $reason = trim($this->returnReason);

        $submission->markReturned($reviewer, $reason);

        // Notify student user
        $studentUser = $submission->student?->user;
        if ($studentUser !== null) {
            $notificationService->send(
                user: $studentUser,
                eventType: 'submission_returned',
                title: __('Assignment Returned for Revision: :title', ['title' => $this->assignment->title]),
                message: __('Your submission for ":title" was returned for correction: :reason', [
                    'title' => $this->assignment->title,
                    'reason' => $reason,
                ]),
                data: [
                    'assignment_id' => $this->assignment->id,
                    'submission_id' => $submission->id,
                    'reason' => $reason,
                ],
                url: route('assignments.show', ['assignment' => $this->assignment->id]),
            );
        }

        // Notify student guardians
        $submission->student?->loadMissing('guardians.user');
        if ($submission->student !== null) {
            foreach ($submission->student->guardians as $guardian) {
                if ($guardian->user !== null) {
                    $notificationService->send(
                        user: $guardian->user,
                        eventType: 'submission_returned',
                        title: __('Assignment Returned for Revision: :title', ['title' => $this->assignment->title]),
                        message: __('Teacher requested adjustments for :student\'s submission on ":title": :reason', [
                            'student' => $submission->student->full_name,
                            'title' => $this->assignment->title,
                            'reason' => $reason,
                        ]),
                        data: [
                            'assignment_id' => $this->assignment->id,
                            'student_id' => $submission->student_id,
                            'submission_id' => $submission->id,
                            'reason' => $reason,
                        ],
                        url: route('assignments.show', [
                            'assignment' => $this->assignment->id,
                            'student' => $submission->student_id,
                        ]),
                    );
                }
            }
        }

        session()->flash('success', __('Submission returned for correction and student notified.'));
        $this->closeReturnModal();
    }

    public function reopenSubmission(int $submissionId): void
    {
        /** @var AssignmentSubmission $submission */
        $submission = AssignmentSubmission::where('id', $submissionId)
            ->where('assignment_id', $this->assignment->id)
            ->firstOrFail();

        $this->authorize('grade', $submission);

        /** @var User $reviewer */
        $reviewer = auth()->user();
        $submission->reopen($reviewer);

        session()->flash('success', __('Submission v:ver has been reopened for review.', ['ver' => $submission->version]));
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
        // 1. Enrolled students in class
        /** @var Collection<int, Student> $enrolledStudents */
        $enrolledStudents = $this->assignment->schoolClass
            ? $this->assignment->schoolClass->students()
                ->wherePivot('status', 'active')
                ->with(['user', 'guardians.user'])
                ->orderBy('first_name')
                ->get()
            : new Collection;

        // 2. All non-deleted submissions for this assignment
        /** @var Collection<int, AssignmentSubmission> $allSubmissions */
        $allSubmissions = AssignmentSubmission::where('assignment_id', $this->assignment->id)
            ->with(['fileAsset', 'reviewer', 'submitter'])
            ->orderByDesc('version')
            ->get();

        $latestSubmissionsByStudent = $allSubmissions->groupBy('student_id')->map(fn ($group) => $group->first());

        // 3. Metrics calculation
        $totalEnrolled = $enrolledStudents->count();
        $pendingCount = 0;
        $gradedCount = 0;
        $returnedCount = 0;
        $missingCount = 0;
        $passedCount = 0;
        $totalMarksSum = 0;
        $gradedWithMarksCount = 0;

        /** @var list<array{student: Student, submission: AssignmentSubmission|null, status_key: string, is_late: bool, versions_count: int}> $roster */
        $roster = [];

        foreach ($enrolledStudents as $student) {
            /** @var AssignmentSubmission|null $sub */
            $sub = $latestSubmissionsByStudent->get($student->id);
            $statusKey = $sub !== null ? $sub->status->value : AssignmentSubmissionStatus::NotSubmitted->value;
            $isLate = $sub !== null && $sub->isLate();

            if ($sub === null) {
                $missingCount++;
            } elseif ($sub->status === AssignmentSubmissionStatus::Graded) {
                $gradedCount++;
                if ($sub->marks !== null) {
                    $totalMarksSum += $sub->marks;
                    $gradedWithMarksCount++;
                    if ($this->assignment->pass_marks !== null && $sub->marks >= $this->assignment->pass_marks) {
                        $passedCount++;
                    }
                }
            } elseif ($sub->status === AssignmentSubmissionStatus::Returned) {
                $returnedCount++;
            } else {
                $pendingCount++;
            }

            // Search filter
            if (trim($this->search) !== '') {
                $needle = mb_strtolower(trim($this->search));
                $matchesName = str_contains(mb_strtolower($student->full_name), $needle);
                $matchesCode = str_contains(mb_strtolower((string) $student->student_code), $needle);
                if (! $matchesName && ! $matchesCode) {
                    continue;
                }
            }

            // Status filter
            if ($this->statusFilter === 'pending' && (! $sub || ! in_array($sub->status, [AssignmentSubmissionStatus::Submitted, AssignmentSubmissionStatus::Late], true))) {
                continue;
            }
            if ($this->statusFilter === 'graded' && (! $sub || $sub->status !== AssignmentSubmissionStatus::Graded)) {
                continue;
            }
            if ($this->statusFilter === 'returned' && (! $sub || $sub->status !== AssignmentSubmissionStatus::Returned)) {
                continue;
            }
            if ($this->statusFilter === 'missing' && $sub !== null) {
                continue;
            }

            $roster[] = [
                'student' => $student,
                'submission' => $sub,
                'status_key' => $statusKey,
                'is_late' => $isLate,
                'versions_count' => $allSubmissions->where('student_id', $student->id)->count(),
            ];
        }

        $submittedTotal = $pendingCount + $gradedCount + $returnedCount;
        $submissionPercentage = $totalEnrolled > 0 ? (int) round(($submittedTotal / $totalEnrolled) * 100) : 0;
        $averageMarks = $gradedWithMarksCount > 0 ? round($totalMarksSum / $gradedWithMarksCount, 1) : null;

        $selectedStudent = $this->selectedStudentId
            ? $enrolledStudents->firstWhere('id', $this->selectedStudentId)
            : null;

        $selectedSubmission = $this->selectedSubmissionId
            ? $allSubmissions->firstWhere('id', $this->selectedSubmissionId)
            : null;

        /** @var Collection<int, AssignmentSubmission> $historyVersions */
        $historyVersions = ($this->showHistoryModal && $this->selectedStudentId)
            ? AssignmentSubmission::withTrashed()
                ->where('assignment_id', $this->assignment->id)
                ->where('student_id', $this->selectedStudentId)
                ->with(['fileAsset', 'reviewer', 'submitter'])
                ->orderByDesc('version')
                ->get()
            : new Collection;

        return view('livewire.teacher.assignment-review-manager', [
            'roster' => $roster,
            'totalEnrolled' => $totalEnrolled,
            'pendingCount' => $pendingCount,
            'gradedCount' => $gradedCount,
            'returnedCount' => $returnedCount,
            'missingCount' => $missingCount,
            'submissionPercentage' => $submissionPercentage,
            'passedCount' => $passedCount,
            'averageMarks' => $averageMarks,
            'selectedStudent' => $selectedStudent,
            'selectedSubmission' => $selectedSubmission,
            'historyVersions' => $historyVersions,
        ]);
    }
}
