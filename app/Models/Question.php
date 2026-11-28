<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use App\Support\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $institution_id
 * @property int $created_by
 * @property QuestionType $type
 * @property string $title
 * @property string $question_text
 * @property string|null $explanation
 * @property string|null $hint
 * @property QuestionDifficulty $difficulty
 * @property float $default_marks
 * @property float $default_penalty_marks
 * @property array<string, mixed>|null $settings
 * @property int|null $image_file_id
 * @property int|null $audio_file_id
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Institution|null $institution
 * @property-read User|null $creator
 * @property-read FileAsset|null $imageFile
 * @property-read FileAsset|null $audioFile
 * @property-read Collection<int, QuestionOption> $options
 * @property-read Collection<int, Tag> $tags
 */
class Question extends Model
{
    use BelongsToInstitution;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'institution_id',
        'created_by',
        'type',
        'title',
        'question_text',
        'explanation',
        'hint',
        'difficulty',
        'default_marks',
        'default_penalty_marks',
        'settings',
        'image_file_id',
        'audio_file_id',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => QuestionType::class,
            'difficulty' => QuestionDifficulty::class,
            'default_marks' => 'float',
            'default_penalty_marks' => 'float',
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Institution, $this>
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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

    public function hasImage(): bool
    {
        return $this->image_file_id !== null || ! empty($this->settings['image_url']);
    }

    public function hasAudio(): bool
    {
        return $this->audio_file_id !== null || ! empty($this->settings['audio_url']);
    }

    public function getImageUrl(): ?string
    {
        if ($this->imageFile) {
            return $this->imageFile->getInlineUrl();
        }

        return $this->settings['image_url'] ?? null;
    }

    public function getAudioUrl(): ?string
    {
        if ($this->audioFile) {
            return $this->audioFile->getInlineUrl();
        }

        return $this->settings['audio_url'] ?? null;
    }

    /**
     * @return HasMany<QuestionOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order_position')->orderBy('id');
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'question_tag');
    }

    /**
     * Scope to search questions by title, text, or tag name.
     *
     * @param  Builder<Question>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term || trim($term) === '') {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('question_text', 'like', "%{$term}%")
                ->orWhereHas('tags', function (Builder $tagQuery) use ($term) {
                    $tagQuery->where('name', 'like', "%{$term}%");
                });
        });
    }

    /**
     * Scope to filter by QuestionType.
     *
     * @param  Builder<Question>  $query
     */
    public function scopeOfType(Builder $query, QuestionType|string $type): Builder
    {
        $value = $type instanceof QuestionType ? $type->value : $type;

        return $query->where('type', $value);
    }

    /**
     * Scope to filter by QuestionDifficulty.
     *
     * @param  Builder<Question>  $query
     */
    public function scopeOfDifficulty(Builder $query, QuestionDifficulty|string $difficulty): Builder
    {
        $value = $difficulty instanceof QuestionDifficulty ? $difficulty->value : $difficulty;

        return $query->where('difficulty', $value);
    }

    /**
     * Scope to filter by Tag ID or Tag slug.
     *
     * @param  Builder<Question>  $query
     */
    public function scopeWithTag(Builder $query, int|string $tagIdOrSlug): Builder
    {
        return $query->whereHas('tags', function (Builder $tagQuery) use ($tagIdOrSlug) {
            if (is_numeric($tagIdOrSlug)) {
                $tagQuery->where('tags.id', (int) $tagIdOrSlug);
            } else {
                $tagQuery->where('tags.slug', $tagIdOrSlug);
            }
        });
    }

    /**
     * Scope to only active questions.
     *
     * @param  Builder<Question>  $query
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Synchronize tags given an array of tag names.
     *
     * @param  array<int, string>  $tagNames
     */
    public function syncTagNames(array $tagNames): void
    {
        $tagIds = [];
        foreach ($tagNames as $name) {
            if (trim($name) === '') {
                continue;
            }
            $tag = Tag::findOrCreateForInstitution($this->institution_id, trim($name));
            $tagIds[] = $tag->id;
        }

        $this->tags()->sync($tagIds);
    }

    /**
     * Deep duplicate this question including options and tags.
     */
    public function duplicate(int $userId): self
    {
        $newQuestion = $this->replicate();
        $newQuestion->created_by = $userId;
        $newQuestion->title = $this->title.' (Copy)';
        $newQuestion->created_at = now();
        $newQuestion->updated_at = now();
        $newQuestion->save();

        // Copy options
        foreach ($this->options as $option) {
            $newOption = $option->replicate();
            $newOption->question_id = $newQuestion->id;
            $newOption->created_at = now();
            $newOption->updated_at = now();
            $newOption->save();
        }

        // Copy tags
        $tagIds = $this->tags->pluck('id')->toArray();
        $newQuestion->tags()->sync($tagIds);

        return $newQuestion;
    }
}
