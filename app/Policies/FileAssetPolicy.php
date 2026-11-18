<?php

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\FileAsset;
use App\Models\User;

class FileAssetPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view/download the file asset.
     */
    public function view(User $user, FileAsset $fileAsset): bool
    {
        if (! $this->belongsToSameInstitution($user, $fileAsset)) {
            return false;
        }

        // 1. The original uploader can always download their file
        if ($fileAsset->uploaded_by && (int) $user->id === (int) $fileAsset->uploaded_by) {
            return true;
        }

        // 2. Administrators, Cashiers, and Teachers of the institution
        if ($user->hasRole([RoleName::Admin->value, RoleName::Teacher->value, RoleName::Cashier->value])) {
            return true;
        }

        // 3. For public/general materials (we will enhance in later academic tasks)
        if ($fileAsset->purpose === 'profile_photo') {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the file asset.
     */
    public function delete(User $user, FileAsset $fileAsset): bool
    {
        if (! $this->belongsToSameInstitution($user, $fileAsset)) {
            return false;
        }

        if ($fileAsset->uploaded_by && (int) $user->id === (int) $fileAsset->uploaded_by) {
            return true;
        }

        return $user->hasRole(RoleName::Admin->value);
    }
}
