<?php

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\ActivitySubmission;
use App\Models\User;

class ActivitySubmissionPolicy extends BasePolicy
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
            RoleName::Parent->value,
        ]);
    }

    /**
     * Determine whether the user can view the submission.
     */
    public function view(User $user, ActivitySubmission $submission): bool
    {
        if (! $this->belongsToSameInstitution($user, $submission)) {
            return false;
        }

        if ($user->hasAnyRole([RoleName::SuperAdmin->value, RoleName::Admin->value, RoleName::Teacher->value])) {
            return true;
        }

        if ($user->hasRole(RoleName::Parent->value)) {
            return $submission->guardian !== null && (int) $submission->guardian->user_id === (int) $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create submissions.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            RoleName::SuperAdmin->value,
            RoleName::Admin->value,
            RoleName::Parent->value,
        ]);
    }

    /**
     * Determine whether the user can update/replace the submission.
     */
    public function update(User $user, ActivitySubmission $submission): bool
    {
        if (! $this->belongsToSameInstitution($user, $submission)) {
            return false;
        }

        if ($user->hasAnyRole([RoleName::SuperAdmin->value, RoleName::Admin->value, RoleName::Teacher->value])) {
            return true;
        }

        if ($user->hasRole(RoleName::Parent->value)) {
            if ($submission->guardian === null || (int) $submission->guardian->user_id !== (int) $user->id) {
                return false;
            }

            return $submission->canBeModifiedByParent();
        }

        return false;
    }

    /**
     * Determine whether the user can delete the submission.
     */
    public function delete(User $user, ActivitySubmission $submission): bool
    {
        if (! $this->belongsToSameInstitution($user, $submission)) {
            return false;
        }

        if ($user->hasAnyRole([RoleName::SuperAdmin->value, RoleName::Admin->value])) {
            return true;
        }

        if ($user->hasRole(RoleName::Parent->value)) {
            if ($submission->guardian === null || (int) $submission->guardian->user_id !== (int) $user->id) {
                return false;
            }

            return $submission->canBeModifiedByParent();
        }

        return false;
    }
}
