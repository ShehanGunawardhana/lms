<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\QuizMode;
use App\Enums\QuizShowAnswersPolicy;
use App\Enums\QuizStatus;
use App\Models\Institution;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quiz>
 */
class QuizFactory extends Factory
{
    protected $model = Quiz::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'created_by' => User::factory(),
            'class_id' => null,
            'class_session_id' => null,
            'activity_id' => null,
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'instructions' => 'Please read each question carefully and select or submit the best answer.',
            'mode' => QuizMode::Standard,
            'status' => QuizStatus::Published,
            'time_limit_minutes' => 30,
            'max_attempts' => 2,
            'pass_percentage' => 60.00,
            'total_marks' => 0.00,
            'shuffle_questions' => false,
            'shuffle_options' => false,
            'allow_negative_marking' => false,
            'show_answers_policy' => QuizShowAnswersPolicy::AfterSubmission,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(7),
            'settings' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => QuizStatus::Draft,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => QuizStatus::Published,
        ]);
    }

    public function nursery(): static
    {
        return $this->state(fn (array $attributes) => [
            'mode' => QuizMode::Nursery,
            'time_limit_minutes' => null,
            'max_attempts' => null,
        ]);
    }

    public function live(): static
    {
        return $this->state(fn (array $attributes) => [
            'mode' => QuizMode::Live,
            'time_limit_minutes' => 15,
            'max_attempts' => 1,
        ]);
    }

    public function paper(): static
    {
        return $this->state(fn (array $attributes) => [
            'mode' => QuizMode::Paper,
            'time_limit_minutes' => 120,
            'max_attempts' => 1,
        ]);
    }
}
