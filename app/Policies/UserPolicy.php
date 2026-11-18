<?php

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\User;

class UserPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->can('users.view') && $this->belongsToSameInstitution($user, $model);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Cannot update super admins unless super admin
        if ($model->hasRole(RoleName::SuperAdmin->value) && ! $user->hasRole(RoleName::SuperAdmin->value)) {
            return false;
        }

        return $user->can('users.update') && $this->belongsToSameInstitution($user, $model);
    }

    /**
     * Determine whether the user can suspend the target user.
     */
    public function suspend(User $user, User $model): bool
    {
        // Cannot suspend self
        if ($user->id === $model->id) {
            return false;
        }

        // Cannot suspend super admin unless super admin
        if ($model->hasRole(RoleName::SuperAdmin->value) && ! $user->hasRole(RoleName::SuperAdmin->value)) {
            return false;
        }

        return $user->can('users.suspend') && $this->belongsToSameInstitution($user, $model);
    }

    /**
     * Determine whether the user can reactivate the target user.
     */
    public function reactivate(User $user, User $model): bool
    {
        // Cannot reactivate self (if self was somehow suspended)
        if ($user->id === $model->id) {
            return false;
        }

        // Cannot reactivate super admin unless super admin
        if ($model->hasRole(RoleName::SuperAdmin->value) && ! $user->hasRole(RoleName::SuperAdmin->value)) {
            return false;
        }

        return ($user->can('users.suspend') || $user->can('users.update')) && $this->belongsToSameInstitution($user, $model);
    }

    /**
     * Determine whether the user can deactivate the target user.
     */
    public function deactivate(User $user, User $model): bool
    {
        // Cannot deactivate self
        if ($user->id === $model->id) {
            return false;
        }

        // Cannot deactivate super admin unless super admin
        if ($model->hasRole(RoleName::SuperAdmin->value) && ! $user->hasRole(RoleName::SuperAdmin->value)) {
            return false;
        }

        return ($user->can('users.delete') || $user->can('users.update')) && $this->belongsToSameInstitution($user, $model);
    }
}
