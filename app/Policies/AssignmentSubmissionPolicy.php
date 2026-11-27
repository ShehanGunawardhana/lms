<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Student;
use App\Models\User;

class AssignmentSubmissionPolicy
{
    /**
     * Determine whether the user can view any submissions.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            RoleName::SuperAdmin->value,
            RoleName::Admin->value,
            RoleName::Teacher->value,
            RoleName::Student->value,
            RoleName::Parent->value,
        ]);
    }

    /**
     * Determine whether the user can view the specific submission.
     */
    public function view(User $user, AssignmentSubmission $submission): bool
    {
        if ($user->institution_id !== $submission->institution_id) {
            return false;
        }

        if ($user->hasAnyRole([RoleName::SuperAdmin->value, RoleName::Admin->value])) {
            return true;
        }

        // Teacher of the class
        if ($user->hasRole(RoleName::Teacher->value)) {
            return $submission->assignment?->schoolClass?->teacher_id === $user->id;
        }

        // Student owner
        if ($submission->submitted_by === $user->id || $submission->student?->user_id === $user->id) {
            return true;
        }

        // Parent / Guardian of the student
        if ($submission->student && $submission->student->isGuardian($user)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create a submission for an assignment and student.
     */
    public function create(User $user, Assignment $assignment, ?Student $student = null): bool
    {
        if ($user->institution_id !== $assignment->institution_id) {
            return false;
        }

        if (! $assignment->isPublished()) {
            return false;
        }

        if ($student !== null) {
            if ($student->institution_id !== $assignment->institution_id) {
                return false;
            }

            // Student must be actively enrolled in class
            $isEnrolled = $student->classes()
                ->where('school_classes.id', $assignment->class_id)
                ->wherePivot('status', 'active')
                ->exists();

            if (! $isEnrolled) {
                return false;
            }

            // User must be the student or student's guardian
            if ($student->user_id !== $user->id && ! $student->isGuardian($user)) {
                return false;
            }

            return $assignment->canAcceptSubmissionsForStudent($student->id);
        }

        return false;
    }

    /**
     * Determine whether the user can update / replace the submission.
     */
    public function update(User $user, AssignmentSubmission $submission): bool
    {
        if ($user->institution_id !== $submission->institution_id) {
            return false;
        }

        if ($submission->isGraded()) {
            return false;
        }

        // Student owner or parent
        $isOwner = $submission->submitted_by === $user->id || $submission->student?->user_id === $user->id;
        $isGuardian = $submission->student && $submission->student->isGuardian($user);

        if (! $isOwner && ! $isGuardian) {
            return false;
        }

        return $submission->canBeModified();
    }

    /**
     * Determine whether the user can soft-delete the submission.
     */
    public function delete(User $user, AssignmentSubmission $submission): bool
    {
        if ($user->institution_id !== $submission->institution_id) {
            return false;
        }

        if ($submission->isGraded()) {
            return false;
        }

        $isOwner = $submission->submitted_by === $user->id || $submission->student?->user_id === $user->id;
        $isGuardian = $submission->student && $submission->student->isGuardian($user);

        if (! $isOwner && ! $isGuardian) {
            return false;
        }

        return $submission->canBeModified();
    }

    /**
     * Determine whether the user can grade / review the submission.
     */
    public function grade(User $user, AssignmentSubmission $submission): bool
    {
        if ($user->institution_id !== $submission->institution_id) {
            return false;
        }

        if ($user->hasAnyRole([RoleName::SuperAdmin->value, RoleName::Admin->value])) {
            return true;
        }

        if ($user->hasRole(RoleName::Teacher->value)) {
            return $submission->assignment?->created_by === $user->id
                || $submission->assignment?->schoolClass?->teacher_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can review the submission.
     */
    public function review(User $user, AssignmentSubmission $submission): bool
    {
        return $this->grade($user, $submission);
    }
}
