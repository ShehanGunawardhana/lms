<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;

class QuizAttemptPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view the quiz attempt.
     */
    public function view(User $user, QuizAttempt $attempt): bool
    {
        if (! $this->belongsToSameInstitution($user, $attempt)) {
            return false;
        }

        // Taker can view their own attempt
        if ((int) $user->id === (int) $attempt->user_id) {
            return true;
        }

        // Faculty & Admins can view attempts
        return $user->hasAnyRole([
            RoleName::SuperAdmin->value,
            RoleName::Admin->value,
            RoleName::Teacher->value,
        ]);
    }

    /**
     * Determine whether the user can start an attempt on the quiz.
     */
    public function start(User $user, Quiz $quiz): bool
    {
        if (! $this->belongsToSameInstitution($user, $quiz)) {
            return false;
        }

        // Must be published and available
        if (! $quiz->isAvailable()) {
            return false;
        }

        // Must have remaining attempts
        return $quiz->canUserAttempt($user->id);
    }

    /**
     * Determine whether the user can update/submit their attempt.
     */
    public function submit(User $user, QuizAttempt $attempt): bool
    {
        if (! $this->belongsToSameInstitution($user, $attempt)) {
            return false;
        }

        return (int) $user->id === (int) $attempt->user_id && $attempt->isInProgress();
    }

    /**
     * Determine whether the user can delete the attempt.
     */
    public function delete(User $user, QuizAttempt $attempt): bool
    {
        if (! $this->belongsToSameInstitution($user, $attempt)) {
            return false;
        }

        return $user->hasAnyRole([
            RoleName::SuperAdmin->value,
            RoleName::Admin->value,
            RoleName::Teacher->value,
        ]);
    }
}
