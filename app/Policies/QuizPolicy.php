<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\Quiz;
use App\Models\User;

class QuizPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any quizzes.
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
     * Determine whether the user can view the quiz.
     */
    public function view(User $user, Quiz $quiz): bool
    {
        if (! $this->belongsToSameInstitution($user, $quiz)) {
            return false;
        }

        if ($user->hasAnyRole([
            RoleName::SuperAdmin->value,
            RoleName::Admin->value,
            RoleName::Teacher->value,
        ])) {
            return true;
        }

        // Students & Parents can only view published quizzes
        return $quiz->isPublished();
    }

    /**
     * Determine whether the user can create quizzes.
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
     * Determine whether the user can update the quiz.
     */
    public function update(User $user, Quiz $quiz): bool
    {
        if (! $this->belongsToSameInstitution($user, $quiz)) {
            return false;
        }

        return $user->hasAnyRole([
            RoleName::SuperAdmin->value,
            RoleName::Admin->value,
            RoleName::Teacher->value,
        ]);
    }

    /**
     * Determine whether the user can delete the quiz.
     */
    public function delete(User $user, Quiz $quiz): bool
    {
        if (! $this->belongsToSameInstitution($user, $quiz)) {
            return false;
        }

        return $user->hasAnyRole([
            RoleName::SuperAdmin->value,
            RoleName::Admin->value,
            RoleName::Teacher->value,
        ]);
    }

    /**
     * Determine whether the user can duplicate the quiz.
     */
    public function duplicate(User $user, Quiz $quiz): bool
    {
        if (! $this->belongsToSameInstitution($user, $quiz)) {
            return false;
        }

        return $user->hasAnyRole([
            RoleName::SuperAdmin->value,
            RoleName::Admin->value,
            RoleName::Teacher->value,
        ]);
    }

    /**
     * Determine whether the user can publish the quiz.
     */
    public function publish(User $user, Quiz $quiz): bool
    {
        if (! $this->belongsToSameInstitution($user, $quiz)) {
            return false;
        }

        return $user->hasAnyRole([
            RoleName::SuperAdmin->value,
            RoleName::Admin->value,
            RoleName::Teacher->value,
        ]);
    }
}
