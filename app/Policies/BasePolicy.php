<?php

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

abstract class BasePolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks. Super Admins bypass general permission gates.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(RoleName::SuperAdmin->value)) {
            return true;
        }

        // Suspended or deactivated users can never perform any actions
        if ($user->isSuspended() || $user->isDeactivated()) {
            return false;
        }

        return null;
    }

    /**
     * Verify that a user belongs to the same institution as the object.
     */
    protected function belongsToSameInstitution(User $user, mixed $model): bool
    {
        if ($user->hasRole(RoleName::SuperAdmin->value)) {
            return true;
        }

        if (! isset($model->institution_id)) {
            return true;
        }

        return (int) $user->institution_id === (int) $model->institution_id;
    }

    /**
     * Verify that the user is the owner of the record.
     */
    protected function isRecordOwner(User $user, mixed $model, string $ownerKey = 'user_id'): bool
    {
        return isset($model->{$ownerKey}) && (int) $user->id === (int) $model->{$ownerKey};
    }
}
