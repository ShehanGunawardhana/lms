<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\Student;
use App\Models\User;

class StudentPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any students.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            RoleName::SuperAdmin->value,
            RoleName::Admin->value,
            RoleName::Teacher->value,
            RoleName::Parent->value,
        ]);
    }

    /**
     * Determine whether the user can view the student.
     */
    public function view(User $user, Student $student): bool
    {
        if (! $this->belongsToSameInstitution($user, $student)) {
            return false;
        }

        if ($user->hasAnyRole([RoleName::SuperAdmin->value, RoleName::Admin->value])) {
            return true;
        }

        if ($user->hasRole(RoleName::Teacher->value)) {
            return $student->isTaughtBy($user);
        }

        if ($user->hasRole(RoleName::Parent->value)) {
            return $student->isGuardian($user);
        }

        if ($user->hasRole(RoleName::Student->value)) {
            return (int) $student->user_id === (int) $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create students.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            RoleName::SuperAdmin->value,
            RoleName::Admin->value,
        ]);
    }

    /**
     * Determine whether the user can update the student.
     */
    public function update(User $user, Student $student): bool
    {
        if (! $this->belongsToSameInstitution($user, $student)) {
            return false;
        }

        return $user->hasAnyRole([
            RoleName::SuperAdmin->value,
            RoleName::Admin->value,
        ]);
    }

    /**
     * Determine whether the user can delete the student.
     */
    public function delete(User $user, Student $student): bool
    {
        if (! $this->belongsToSameInstitution($user, $student)) {
            return false;
        }

        return $user->hasAnyRole([
            RoleName::SuperAdmin->value,
            RoleName::Admin->value,
        ]);
    }

    /**
     * Determine whether the user can view the student's encrypted medical and allergy notes (§6.5).
     * Restricted ONLY to the child's active teachers, parents, and admins.
     */
    public function viewMedicalNotes(User $user, Student $student): bool
    {
        if (! $this->belongsToSameInstitution($user, $student)) {
            return false;
        }

        if ($user->hasAnyRole([RoleName::SuperAdmin->value, RoleName::Admin->value])) {
            return true;
        }

        if ($user->hasRole(RoleName::Teacher->value)) {
            return $student->isTaughtBy($user);
        }

        if ($user->hasRole(RoleName::Parent->value)) {
            return $student->isGuardian($user);
        }

        return false;
    }

    /**
     * Determine whether the user can update the student's medical and allergy notes (§6.5).
     */
    public function updateMedicalNotes(User $user, Student $student): bool
    {
        return $this->viewMedicalNotes($user, $student);
    }

    /**
     * Determine whether the user can view the student's emergency contacts (§6.5).
     */
    public function viewEmergencyContacts(User $user, Student $student): bool
    {
        return $this->viewMedicalNotes($user, $student);
    }

    /**
     * Determine whether the user can manage (create, edit, delete) emergency contacts (§6.5).
     */
    public function manageEmergencyContacts(User $user, Student $student): bool
    {
        return $this->viewMedicalNotes($user, $student);
    }

    /**
     * Determine whether the user can view authorized pickup persons (§6.5).
     */
    public function viewAuthorizedPickups(User $user, Student $student): bool
    {
        return $this->viewMedicalNotes($user, $student);
    }

    /**
     * Determine whether the user can manage authorized pickup persons (§6.5).
     */
    public function manageAuthorizedPickups(User $user, Student $student): bool
    {
        return $this->viewMedicalNotes($user, $student);
    }
}
