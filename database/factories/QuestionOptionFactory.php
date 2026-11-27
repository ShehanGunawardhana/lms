<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuestionOption>
 */
class QuestionOptionFactory extends Factory
{
    protected $model = QuestionOption::class;

    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'option_text' => fake()->sentence(3),
            'image_path' => null,
            'is_correct' => false,
            'match_text' => null,
            'match_image_path' => null,
            'order_position' => 0,
            'acceptable_answers' => null,
            'feedback' => null,
        ];
    }
}
