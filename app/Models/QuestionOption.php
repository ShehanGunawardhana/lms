<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $question_id
 * @property string|null $option_text
 * @property string|null $image_path
 * @property bool $is_correct
 * @property string|null $match_text
 * @property string|null $match_image_path
 * @property int $order_position
 * @property array<string>|null $acceptable_answers
 * @property string|null $feedback
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Question|null $question
 */
class QuestionOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'option_text',
        'image_path',
        'is_correct',
        'match_text',
        'match_image_path',
        'order_position',
        'acceptable_answers',
        'feedback',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'order_position' => 'integer',
            'acceptable_answers' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Question, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
