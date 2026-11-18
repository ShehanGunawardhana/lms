<?php

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\TeacherProfile;
use App\Models\User;

class TeacherProfilePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any teacher profiles.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the teacher profile.
     */
    public function view(User $user, TeacherProfile $profile): bool
    {
        if ($profile->isPublished()) {
            return true;
        }

        if ($user->hasRole(RoleName::Admin->value) || $user->hasRole(RoleName::SuperAdmin->value)) {
            return $this->belongsToSameInstitution($user, $profile);
        }

        return $this->isRecordOwner($user, $profile, 'user_id');
    }

    /**
     * Determine whether the user can update the teacher profile.
     */
    public function update(User $user, TeacherProfile $profile): bool
    {
        if ($user->hasRole(RoleName::Admin->value) || $user->hasRole(RoleName::SuperAdmin->value)) {
            return $this->belongsToSameInstitution($user, $profile);
        }

        return $this->isRecordOwner($user, $profile, 'user_id') && $this->belongsToSameInstitution($user, $profile);
    }

    /**
     * Determine whether the user can submit the portfolio for approval.
     */
    public function submit(User $user, TeacherProfile $profile): bool
    {
        return $this->isRecordOwner($user, $profile, 'user_id') && $this->belongsToSameInstitution($user, $profile);
    }

    /**
     * Determine whether the user can publish or reject the portfolio.
     */
    public function publish(User $user, TeacherProfile $profile): bool
    {
        if (! $this->belongsToSameInstitution($user, $profile)) {
            return false;
        }

        return $user->can('portfolio.publish') || $user->hasRole(RoleName::Admin->value) || $user->hasRole(RoleName::SuperAdmin->value);
    }
}
