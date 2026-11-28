<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\QuizAttemptStatus;
use App\Models\Institution;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizAttempt>
 */
class QuizAttemptFactory extends Factory
{
    protected $model = QuizAttempt::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'quiz_id' => Quiz::factory(),
            'user_id' => User::factory(),
            'attempt_number' => 1,
            'status' => QuizAttemptStatus::InProgress,
            'started_at' => now(),
            'completed_at' => null,
            'score' => null,
            'total_marks' => null,
            'percentage' => null,
            'passed' => null,
            'time_spent_seconds' => 0,
            'question_order' => null,
            'options_order' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => QuizAttemptStatus::Completed,
            'completed_at' => now(),
            'score' => 8.0,
            'total_marks' => 10.0,
            'percentage' => 80.0,
            'passed' => true,
            'time_spent_seconds' => 600,
        ]);
    }
}
