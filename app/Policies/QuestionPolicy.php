<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\Question;
use App\Models\User;

class QuestionPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any questions.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            RoleName::SuperAdmin->value,
            RoleName::Admin->value,
            RoleName::Teacher->value,
        ]);
    }

    /**
     * Determine whether the user can view the question.
     */
    public function view(User $user, Question $question): bool
    {
        if (! $this->belongsToSameInstitution($user, $question)) {
            return false;
        }

        return $user->hasAnyRole([
            RoleName::SuperAdmin->value,
            RoleName::Admin->value,
            RoleName::Teacher->value,
        ]);
    }

    /**
     * Determine whether the user can create questions.
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
     * Determine whether the user can update the question.
     */
    public function update(User $user, Question $question): bool
    {
        if (! $this->belongsToSameInstitution($user, $question)) {
            return false;
        }

        if ($user->hasAnyRole([RoleName::SuperAdmin->value, RoleName::Admin->value])) {
            return true;
        }

        if ($user->hasRole(RoleName::Teacher->value)) {
            return $question->created_by === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the question.
     */
    public function delete(User $user, Question $question): bool
    {
        if (! $this->belongsToSameInstitution($user, $question)) {
            return false;
        }

        if ($user->hasAnyRole([RoleName::SuperAdmin->value, RoleName::Admin->value])) {
            return true;
        }

        if ($user->hasRole(RoleName::Teacher->value)) {
            return $question->created_by === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can duplicate the question.
     */
    public function duplicate(User $user, Question $question): bool
    {
        if (! $this->belongsToSameInstitution($user, $question)) {
            return false;
        }

        return $user->hasAnyRole([
            RoleName::SuperAdmin->value,
            RoleName::Admin->value,
            RoleName::Teacher->value,
        ]);
    }
}
