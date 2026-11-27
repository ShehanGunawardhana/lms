<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $institution_id
 * @property string $name
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Institution|null $institution
 * @property-read Collection<int, Question> $questions
 */
class Tag extends Model
{
    use BelongsToInstitution;
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'name',
        'slug',
    ];

    /**
     * @return BelongsTo<Institution, $this>
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * @return BelongsToMany<Question, $this>
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'question_tag');
    }

    /**
     * Find or create a tag for an institution by its name.
     */
    public static function findOrCreateForInstitution(int $institutionId, string $name): self
    {
        $cleanName = trim($name);
        $slug = Str::slug($cleanName);

        if ($slug === '') {
            $slug = 'tag-'.md5($cleanName);
        }

        /** @var self $tag */
        $tag = static::firstOrCreate(
            [
                'institution_id' => $institutionId,
                'slug' => $slug,
            ],
            [
                'name' => $cleanName,
            ]
        );

        return $tag;
    }
}
