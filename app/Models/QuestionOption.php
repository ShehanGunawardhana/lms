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
 * @property int|null $image_file_id
 * @property int|null $audio_file_id
 * @property bool $is_correct
 * @property string|null $match_text
 * @property string|null $match_image_path
 * @property int|null $match_image_file_id
 * @property int $order_position
 * @property array<string>|null $acceptable_answers
 * @property string|null $feedback
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Question|null $question
 * @property-read FileAsset|null $imageFile
 * @property-read FileAsset|null $audioFile
 * @property-read FileAsset|null $matchImageFile
 */
class QuestionOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'option_text',
        'image_path',
        'image_file_id',
        'audio_file_id',
        'is_correct',
        'match_text',
        'match_image_path',
        'match_image_file_id',
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

    /**
     * @return BelongsTo<FileAsset, $this>
     */
    public function imageFile(): BelongsTo
    {
        return $this->belongsTo(FileAsset::class, 'image_file_id');
    }

    /**
     * @return BelongsTo<FileAsset, $this>
     */
    public function audioFile(): BelongsTo
    {
        return $this->belongsTo(FileAsset::class, 'audio_file_id');
    }

    /**
     * @return BelongsTo<FileAsset, $this>
     */
    public function matchImageFile(): BelongsTo
    {
        return $this->belongsTo(FileAsset::class, 'match_image_file_id');
    }

    public function hasImage(): bool
    {
        return $this->image_file_id !== null || ! empty($this->image_path);
    }

    public function hasAudio(): bool
    {
        return $this->audio_file_id !== null;
    }

    public function hasMatchImage(): bool
    {
        return $this->match_image_file_id !== null || ! empty($this->match_image_path);
    }

    public function getImageUrl(): ?string
    {
        if ($this->imageFile) {
            return $this->imageFile->getInlineUrl();
        }

        return $this->image_path;
    }

    public function getAudioUrl(): ?string
    {
        if ($this->audioFile) {
            return $this->audioFile->getInlineUrl();
        }

        return null;
    }

    public function getMatchImageUrl(): ?string
    {
        if ($this->matchImageFile) {
            return $this->matchImageFile->getInlineUrl();
        }

        return $this->match_image_path;
    }
}
