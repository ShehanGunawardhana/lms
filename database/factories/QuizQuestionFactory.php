<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizQuestion>
 */
class QuizQuestionFactory extends Factory
{
    protected $model = QuizQuestion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'question_id' => Question::factory(),
            'order_position' => 1,
            'marks' => null,
            'penalty_marks' => null,
        ];
    }
}
