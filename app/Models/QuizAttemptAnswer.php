<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $quiz_attempt_id
 * @property int $question_id
 * @property int|null $selected_option_id
 * @property array<int, int>|null $selected_option_ids
 * @property string|null $text_answer
 * @property array<int, int>|null $ordering_answer
 * @property array<int|string, mixed>|null $matching_answer
 * @property float|null $marks_awarded
 * @property bool|null $is_correct
 * @property string|null $feedback
 * @property Carbon|null $graded_at
 * @property int|null $graded_by
 * @property bool $is_flagged
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read QuizAttempt|null $attempt
 * @property-read Question|null $question
 * @property-read QuestionOption|null $selectedOption
 * @property-read User|null $grader
 */
class QuizAttemptAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_attempt_id',
        'question_id',
        'selected_option_id',
        'selected_option_ids',
        'text_answer',
        'ordering_answer',
        'matching_answer',
        'marks_awarded',
        'is_correct',
        'feedback',
        'graded_at',
        'graded_by',
        'is_flagged',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'selected_option_id' => 'integer',
            'selected_option_ids' => 'array',
            'ordering_answer' => 'array',
            'matching_answer' => 'array',
            'marks_awarded' => 'float',
            'is_correct' => 'boolean',
            'graded_at' => 'datetime',
            'graded_by' => 'integer',
            'is_flagged' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<QuizAttempt, $this>
     */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id');
    }

    /**
     * @return BelongsTo<Question, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    /**
     * @return BelongsTo<QuestionOption, $this>
     */
    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'selected_option_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function isPendingManualGrading(): bool
    {
        return $this->question?->type === QuestionType::ShortAnswer && $this->marks_awarded === null;
    }

    public function isAnswered(): bool
    {
        if ($this->selected_option_id !== null) {
            return true;
        }

        if (! empty($this->selected_option_ids)) {
            return true;
        }

        if ($this->text_answer !== null && trim($this->text_answer) !== '') {
            return true;
        }

        if (! empty($this->ordering_answer)) {
            return true;
        }

        if (! empty($this->matching_answer)) {
            return true;
        }

        return false;
    }
}
