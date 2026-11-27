<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\Assignment;
use App\Models\Guardian;
use App\Models\User;

class AssignmentPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any assignments.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            RoleName::SuperAdmin->value,
            RoleName::Admin->value,
            RoleName::Teacher->value,
            RoleName::Parent->value,
            RoleName::Student->value,
        ]);
    }

    /**
     * Determine whether the user can view the assignment.
     */
    public function view(User $user, Assignment $assignment): bool
    {
        if (! $this->belongsToSameInstitution($user, $assignment)) {
            return false;
        }

        if ($user->hasAnyRole([RoleName::SuperAdmin->value, RoleName::Admin->value])) {
            return true;
        }

        if ($user->hasRole(RoleName::Teacher->value)) {
            if ($assignment->created_by === $user->id) {
                return true;
            }

            if ($assignment->schoolClass && $assignment->schoolClass->teacher_id === $user->id) {
                return true;
            }

            return true;
        }

        if ($user->hasRole(RoleName::Student->value)) {
            if ($assignment->isDraft()) {
                return false;
            }

            return $assignment->schoolClass !== null && $assignment->schoolClass->students()
                ->where('students.user_id', $user->id)
                ->wherePivot('status', 'active')
                ->exists();
        }

        if ($user->hasRole(RoleName::Parent->value)) {
            if ($assignment->isDraft()) {
                return false;
            }

            /** @var Guardian|null $guardian */
            $guardian = $user->guardian ?? Guardian::where('user_id', $user->id)->first();
            if (! $guardian) {
                return false;
            }

            return $guardian->students()
                ->whereHas('classes', function ($query) use ($assignment) {
                    $query->where('school_classes.id', $assignment->class_id)
                        ->wherePivot('status', 'active');
                })
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create assignments.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            RoleName::SuperAdmin->value,
            RoleName::Admin->value,
            RoleName::Teacher->value,
        ]);
    }

    /**
     * Determine whether the user can update the assignment.
     */
    public function update(User $user, Assignment $assignment): bool
    {
        if (! $this->belongsToSameInstitution($user, $assignment)) {
            return false;
        }

        if ($user->hasAnyRole([RoleName::SuperAdmin->value, RoleName::Admin->value])) {
            return true;
        }

        if ($user->hasRole(RoleName::Teacher->value)) {
            return $assignment->created_by === $user->id
                || ($assignment->schoolClass && $assignment->schoolClass->teacher_id === $user->id);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the assignment.
     */
    public function delete(User $user, Assignment $assignment): bool
    {
        return $this->update($user, $assignment);
    }

    /**
     * Determine whether the user can publish or close the assignment.
     */
    public function publish(User $user, Assignment $assignment): bool
    {
        return $this->update($user, $assignment);
    }

    /**
     * Determine whether the user can review student submissions for the assignment.
     */
    public function reviewSubmissions(User $user, Assignment $assignment): bool
    {
        return $this->update($user, $assignment);
    }
}
