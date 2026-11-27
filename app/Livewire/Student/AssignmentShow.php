<?php

declare(strict_types=1);

namespace App\Livewire\Student;

use App\Enums\AssignmentSubmissionStatus;
use App\Enums\RoleName;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use App\Services\FileService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class AssignmentShow extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public Assignment $assignment;

    public ?int $selectedStudentId = null;

    // Submission form
    public mixed $submissionFile = null;

    public string $submissionNote = '';

    public string $submissionError = '';

    // Replace modal
    public bool $showReplaceModal = false;

    public mixed $replacementFile = null;

    public string $replacementNote = '';

    // Edit note modal
    public bool $showEditNoteModal = false;

    public string $editingNote = '';

    // Delete modal
    public bool $showDeleteModal = false;

    // History modal
    public bool $showHistoryModal = false;

    public function mount(Assignment $assignment, ?int $student = null): void
    {
        $institutionId = current_institution_id() ?? 1;

        if ($assignment->institution_id !== $institutionId) {
            abort(404);
        }

        $this->assignment = $assignment;

        /** @var User $user */
        $user = auth()->user();

        if ($user->hasRole(RoleName::Student->value)) {
            $studentRecord = Student::where('institution_id', $institutionId)
                ->where('user_id', $user->id)
                ->first();

            if (! $studentRecord) {
                abort(403, __('No active student record linked to your account.'));
            }

            // Verify student is enrolled in class
            $isEnrolled = $studentRecord->classes()
                ->where('school_classes.id', $this->assignment->class_id)
                ->wherePivot('status', 'active')
                ->exists();

            if (! $isEnrolled) {
                abort(403, __('You are not enrolled in the class for this assignment.'));
            }

            $this->selectedStudentId = $studentRecord->id;
        } elseif ($user->hasRole(RoleName::Parent->value)) {
            $guardian = Guardian::where('institution_id', $institutionId)
                ->where('user_id', $user->id)
                ->first();

            if (! $guardian) {
                abort(403, __('No guardian profile found.'));
            }

            $children = $guardian->students()
                ->whereHas('classes', function ($q) {
                    $q->where('school_classes.id', $this->assignment->class_id)
                        ->where('enrollments.status', 'active');
                })
                ->get();

            if ($children->isEmpty()) {
                abort(403, __('None of your children are enrolled in this assignment\'s class.'));
            }

            if ($student && $children->contains('id', $student)) {
                $this->selectedStudentId = $student;
            } else {
                $this->selectedStudentId = $children->first()->id;
            }
        } elseif ($user->hasAnyRole([RoleName::SuperAdmin->value, RoleName::Admin->value, RoleName::Teacher->value])) {
            // Admin or Teacher previewing
            $firstEnrolledStudent = Student::where('institution_id', $institutionId)
                ->whereHas('classes', function ($q) {
                    $q->where('school_classes.id', $this->assignment->class_id)
                        ->where('enrollments.status', 'active');
                })
                ->first();

            $this->selectedStudentId = $student ?? $firstEnrolledStudent?->id;
        } else {
            abort(403);
        }
    }

    public function selectStudent(int $studentId): void
    {
        $this->resetValidation();
        $this->submissionError = '';
        $this->submissionFile = null;
        $this->submissionNote = '';

        /** @var User $user */
        $user = auth()->user();

        if ($user->hasRole(RoleName::Parent->value)) {
            $guardian = Guardian::where('user_id', $user->id)->first();
            $hasChild = $guardian && $guardian->students()->where('students.id', $studentId)->exists();
            if ($hasChild) {
                $this->selectedStudentId = $studentId;
            }
        } else {
            $this->selectedStudentId = $studentId;
        }
    }

    public function submitWork(): void
    {
        $this->submissionError = '';
        $student = $this->getStudent();

        if (! $student) {
            $this->submissionError = __('Student record not found.');

            return;
        }

        $this->authorize('create', [AssignmentSubmission::class, $this->assignment, $student]);

        // Check if assignment can accept submissions
        if (! $this->assignment->isPublished()) {
            $this->submissionError = __('This assignment is not currently accepting submissions.');

            return;
        }

        if ($this->assignment->isPastDeadlineForStudent($student->id) && ! $this->assignment->allow_late) {
            $this->submissionError = __('The deadline has passed and late submissions are not allowed for this assignment.');

            return;
        }

        if ($this->assignment->resubmissionLimitExceededForStudent($student->id)) {
            $this->submissionError = __('Maximum resubmission limit reached for this assignment.');

            return;
        }

        $maxBytes = $this->assignment->max_file_size_mb * 1024;
        $this->validate([
            'submissionFile' => ['required', 'file', "max:{$maxBytes}"],
            'submissionNote' => ['nullable', 'string', 'max:2000'],
        ]);

        /** @var User $user */
        $user = auth()->user();
        $fileService = app(FileService::class);

        try {
            $fileAsset = $fileService->store(
                $this->submissionFile,
                'assignment_submission',
                $this->assignment->allowed_file_types ?: null,
                $user
            );
        } catch (\Throwable $e) {
            $this->submissionError = $e->getMessage();

            return;
        }

        $isLate = $this->assignment->isPastDeadlineForStudent($student->id);
        $status = $isLate ? AssignmentSubmissionStatus::Late : AssignmentSubmissionStatus::Submitted;

        $version = $this->assignment->resubmissionCountForStudent($student->id) + 1;

        $submission = AssignmentSubmission::create([
            'institution_id' => $this->assignment->institution_id,
            'assignment_id' => $this->assignment->id,
            'student_id' => $student->id,
            'submitted_by' => $user->id,
            'file_asset_id' => $fileAsset->id,
            'note' => trim($this->submissionNote) !== '' ? trim($this->submissionNote) : null,
            'status' => $status,
            'version' => $version,
            'is_late' => $isLate,
            'submitted_at' => now(),
        ]);

        audit(
            event: 'assignment.submitted',
            description: "Assignment '{$this->assignment->title}' submitted v{$version} for student '{$student->full_name}'",
            subject: $submission,
            properties: [
                'assignment_id' => $this->assignment->id,
                'student_id' => $student->id,
                'version' => $version,
                'is_late' => $isLate,
            ]
        );

        $this->submissionFile = null;
        $this->submissionNote = '';
        $this->submissionError = '';
        session()->flash('success', __('Your assignment submission (version :ver) was uploaded successfully.', ['ver' => $version]));
    }

    public function openReplaceModal(): void
    {
        $submission = $this->getCurrentSubmission();

        if (! $submission || ! $submission->canBeModified()) {
            abort(403);
        }

        $this->authorize('update', $submission);

        $this->replacementNote = $submission->note ?? '';
        $this->replacementFile = null;
        $this->submissionError = '';
        $this->showReplaceModal = true;
    }

    public function replaceWork(): void
    {
        $this->submissionError = '';
        $submission = $this->getCurrentSubmission();

        if (! $submission || ! $submission->canBeModified()) {
            abort(403);
        }

        $this->authorize('update', $submission);

        $student = $this->getStudent();
        if (! $student) {
            return;
        }

        if ($this->assignment->resubmissionLimitExceededForStudent($student->id)) {
            $this->submissionError = __('Maximum resubmission limit reached for this assignment.');

            return;
        }

        if ($this->assignment->isPastDeadlineForStudent($student->id) && ! $this->assignment->allow_late) {
            $this->submissionError = __('The deadline has passed and late submissions are not allowed for this assignment.');

            return;
        }

        $maxBytes = $this->assignment->max_file_size_mb * 1024;
        $this->validate([
            'replacementFile' => ['required', 'file', "max:{$maxBytes}"],
            'replacementNote' => ['nullable', 'string', 'max:2000'],
        ]);

        /** @var User $user */
        $user = auth()->user();
        $fileService = app(FileService::class);

        try {
            $fileAsset = $fileService->store(
                $this->replacementFile,
                'assignment_submission',
                $this->assignment->allowed_file_types ?: null,
                $user
            );
        } catch (\Throwable $e) {
            $this->submissionError = $e->getMessage();

            return;
        }

        $isLate = $this->assignment->isPastDeadlineForStudent($student->id);
        $status = $isLate ? AssignmentSubmissionStatus::Late : AssignmentSubmissionStatus::Submitted;
        $newVersion = $submission->version + 1;

        $newSubmission = AssignmentSubmission::create([
            'institution_id' => $this->assignment->institution_id,
            'assignment_id' => $this->assignment->id,
            'student_id' => $student->id,
            'submitted_by' => $user->id,
            'file_asset_id' => $fileAsset->id,
            'note' => trim($this->replacementNote) !== '' ? trim($this->replacementNote) : null,
            'status' => $status,
            'version' => $newVersion,
            'is_late' => $isLate,
            'submitted_at' => now(),
        ]);

        audit(
            event: 'assignment.submission_replaced',
            description: "Assignment '{$this->assignment->title}' replaced with v{$newVersion} for student '{$student->full_name}'",
            subject: $newSubmission,
            properties: [
                'assignment_id' => $this->assignment->id,
                'student_id' => $student->id,
                'version' => $newVersion,
                'previous_submission_id' => $submission->id,
            ]
        );

        $this->replacementFile = null;
        $this->replacementNote = '';
        $this->showReplaceModal = false;
        session()->flash('success', __('Assignment submission replaced with version :ver.', ['ver' => $newVersion]));
    }

    public function openEditNoteModal(): void
    {
        $submission = $this->getCurrentSubmission();

        if (! $submission || ! $submission->canBeModified()) {
            abort(403);
        }

        $this->authorize('update', $submission);

        $this->editingNote = $submission->note ?? '';
        $this->showEditNoteModal = true;
    }

    public function saveNote(): void
    {
        $submission = $this->getCurrentSubmission();

        if (! $submission || ! $submission->canBeModified()) {
            abort(403);
        }

        $this->authorize('update', $submission);

        $this->validate([
            'editingNote' => ['nullable', 'string', 'max:2000'],
        ]);

        $submission->update([
            'note' => trim($this->editingNote) !== '' ? trim($this->editingNote) : null,
        ]);

        audit(
            event: 'assignment.submission_note_updated',
            description: "Submission note updated for v{$submission->version}",
            subject: $submission
        );

        $this->showEditNoteModal = false;
        session()->flash('success', __('Submission note updated successfully.'));
    }

    public function confirmDelete(): void
    {
        $submission = $this->getCurrentSubmission();

        if (! $submission || ! $submission->canBeModified()) {
            abort(403);
        }

        $this->authorize('delete', $submission);
        $this->showDeleteModal = true;
    }

    public function deleteSubmission(): void
    {
        $submission = $this->getCurrentSubmission();

        if (! $submission || ! $submission->canBeModified()) {
            abort(403);
        }

        $this->authorize('delete', $submission);

        $version = $submission->version;
        $submission->delete();

        audit(
            event: 'assignment.submission_deleted',
            description: "Deleted assignment submission v{$version} for '{$this->assignment->title}'",
            subject: $submission
        );

        $this->showDeleteModal = false;
        session()->flash('success', __('Submission version :ver has been removed.', ['ver' => $version]));
    }

    public function getStudent(): ?Student
    {
        if (! $this->selectedStudentId) {
            return null;
        }

        return Student::find($this->selectedStudentId);
    }

    public function getCurrentSubmission(): ?AssignmentSubmission
    {
        if (! $this->selectedStudentId) {
            return null;
        }

        return $this->assignment->submissionForStudent($this->selectedStudentId);
    }

    /**
     * @return Collection<int, Student>
     */
    public function getChildrenProperty(): Collection
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user->hasRole(RoleName::Parent->value)) {
            return new Collection;
        }

        $guardian = Guardian::where('user_id', $user->id)->first();
        if (! $guardian) {
            return new Collection;
        }

        return $guardian->students()
            ->whereHas('classes', function ($q) {
                $q->where('school_classes.id', $this->assignment->class_id)
                    ->where('enrollments.status', 'active');
            })
            ->get();
    }

    public function render(): View
    {
        $student = $this->getStudent();
        $submission = $this->getCurrentSubmission();
        $history = $student ? $this->assignment->allSubmissionsForStudent($student->id) : collect();

        $deadline = $student ? $this->assignment->effectiveDeadlineForStudent($student->id) : $this->assignment->deadline;
        $isPastDeadline = $student ? $this->assignment->isPastDeadlineForStudent($student->id) : $this->assignment->deadline->isPast();
        $isExtended = $student ? $this->assignment->isExtendedForStudent($student->id) : false;
        $latestExtension = $student ? $this->assignment->latestExtensionForStudent($student->id) : null;
        $status = $student ? $this->assignment->statusForStudent($student->id) : AssignmentSubmissionStatus::NotSubmitted;
        $resubmissionCount = $student ? $this->assignment->resubmissionCountForStudent($student->id) : 0;
        $canSubmit = $student ? $this->assignment->canAcceptSubmissionsForStudent($student->id) : false;

        return view('livewire.student.assignment-show', [
            'student' => $student,
            'submission' => $submission,
            'history' => $history,
            'deadline' => $deadline,
            'isPastDeadline' => $isPastDeadline,
            'isExtended' => $isExtended,
            'latestExtension' => $latestExtension,
            'status' => $status,
            'resubmissionCount' => $resubmissionCount,
            'canSubmit' => $canSubmit,
            'children' => $this->getChildrenProperty(),
        ]);
    }
}
