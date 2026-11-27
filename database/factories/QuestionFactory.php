<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use App\Models\Institution;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'created_by' => User::factory(),
            'type' => QuestionType::SingleChoice,
            'title' => fake()->sentence(4),
            'question_text' => fake()->paragraph(),
            'explanation' => fake()->sentence(),
            'hint' => fake()->sentence(),
            'difficulty' => QuestionDifficulty::Medium,
            'default_marks' => 1.0,
            'default_penalty_marks' => 0.0,
            'settings' => null,
            'is_active' => true,
        ];
    }

    public function singleChoice(): static
    {
        return $this->state(fn () => [
            'type' => QuestionType::SingleChoice,
        ]);
    }

    public function multipleChoice(): static
    {
        return $this->state(fn () => [
            'type' => QuestionType::MultipleChoice,
        ]);
    }

    public function trueFalse(): static
    {
        return $this->state(fn () => [
            'type' => QuestionType::TrueFalse,
        ]);
    }

    public function matching(): static
    {
        return $this->state(fn () => [
            'type' => QuestionType::Matching,
        ]);
    }

    public function pictureChoice(): static
    {
        return $this->state(fn () => [
            'type' => QuestionType::PictureChoice,
        ]);
    }

    public function fillInTheBlank(): static
    {
        return $this->state(fn () => [
            'type' => QuestionType::FillInTheBlank,
        ]);
    }

    public function ordering(): static
    {
        return $this->state(fn () => [
            'type' => QuestionType::Ordering,
        ]);
    }

    public function shortAnswer(): static
    {
        return $this->state(fn () => [
            'type' => QuestionType::ShortAnswer,
        ]);
    }
}
