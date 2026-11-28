<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $quiz_id
 * @property int $question_id
 * @property int $order_position
 * @property float|null $marks
 * @property float|null $penalty_marks
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Quiz|null $quiz
 * @property-read Question|null $question
 */
class QuizQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'question_id',
        'order_position',
        'marks',
        'penalty_marks',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order_position' => 'integer',
            'marks' => 'float',
            'penalty_marks' => 'float',
        ];
    }

    /**
     * @return BelongsTo<Quiz, $this>
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    /**
     * @return BelongsTo<Question, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function getEffectiveMarks(): float
    {
        if ($this->marks !== null) {
            return (float) $this->marks;
        }

        return (float) ($this->question->default_marks ?? 1.0);
    }

    public function getEffectivePenalty(): float
    {
        if ($this->penalty_marks !== null) {
            return (float) $this->penalty_marks;
        }

        return (float) ($this->question->default_penalty_marks ?? 0.0);
    }
}
