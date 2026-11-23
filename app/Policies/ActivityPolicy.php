<?php

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\Activity;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;

class ActivityPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any activities.
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
     * Determine whether the user can view the activity.
     */
    public function view(User $user, Activity $activity): bool
    {
        if (! $this->belongsToSameInstitution($user, $activity)) {
            return false;
        }

        if ($user->hasAnyRole([RoleName::SuperAdmin->value, RoleName::Admin->value])) {
            return true;
        }

        if ($user->hasRole(RoleName::Teacher->value)) {
            if ($activity->created_by === $user->id) {
                return true;
            }

            if ($activity->schoolClass && $activity->schoolClass->teacher_id === $user->id) {
                return true;
            }

            return true;
        }

        if ($user->hasRole(RoleName::Parent->value)) {
            if ($activity->isDraft()) {
                return false;
            }

            /** @var Guardian|null $guardian */
            $guardian = $user->guardian ?? Guardian::where('user_id', $user->id)->first();
            if (! $guardian) {
                return false;
            }

            return $guardian->students()
                ->whereHas('enrollments', function ($query) use ($activity) {
                    $query->where('class_id', $activity->class_id)
                        ->where('status', 'active');
                })
                ->exists();
        }

        if ($user->hasRole(RoleName::Student->value)) {
            if ($activity->isDraft()) {
                return false;
            }

            /** @var Student|null $student */
            $student = $user->student ?? Student::where('user_id', $user->id)->first();
            if (! $student) {
                return false;
            }

            return $student->enrollments()
                ->where('class_id', $activity->class_id)
                ->where('status', 'active')
                ->exists();
        }

        // Other authorized users can only see published or closed activities
        return ! $activity->isDraft();
    }

    /**
     * Determine whether the user can create activities.
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
     * Determine whether the user can update the activity.
     */
    public function update(User $user, Activity $activity): bool
    {
        if (! $this->belongsToSameInstitution($user, $activity)) {
            return false;
        }

        if ($user->hasAnyRole([RoleName::SuperAdmin->value, RoleName::Admin->value])) {
            return true;
        }

        if ($user->hasRole(RoleName::Teacher->value)) {
            return $activity->created_by === $user->id
                || ($activity->schoolClass && $activity->schoolClass->teacher_id === $user->id);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the activity.
     */
    public function delete(User $user, Activity $activity): bool
    {
        if (! $this->belongsToSameInstitution($user, $activity)) {
            return false;
        }

        if ($user->hasAnyRole([RoleName::SuperAdmin->value, RoleName::Admin->value])) {
            return true;
        }

        if ($user->hasRole(RoleName::Teacher->value)) {
            return $activity->created_by === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can publish the activity.
     */
    public function publish(User $user, Activity $activity): bool
    {
        return $this->update($user, $activity);
    }

    /**
     * Determine whether the user can close the activity.
     */
    public function close(User $user, Activity $activity): bool
    {
        return $this->update($user, $activity);
    }
}
