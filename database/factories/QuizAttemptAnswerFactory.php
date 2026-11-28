<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizAttemptAnswer>
 */
class QuizAttemptAnswerFactory extends Factory
{
    protected $model = QuizAttemptAnswer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_attempt_id' => QuizAttempt::factory(),
            'question_id' => Question::factory(),
            'selected_option_id' => null,
            'selected_option_ids' => null,
            'text_answer' => null,
            'ordering_answer' => null,
            'matching_answer' => null,
            'marks_awarded' => null,
            'is_correct' => null,
            'feedback' => null,
            'is_flagged' => false,
        ];
    }
}
