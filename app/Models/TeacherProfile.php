<?php

namespace App\Models;

use App\Support\Traits\BelongsToInstitution;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $institution_id
 * @property int $user_id
 * @property string|null $headline
 * @property string|null $bio
 * @property string|null $qualifications
 * @property array<string>|null $subjects
 * @property list<array{title: string, description: string, url: string, type: string}>|null $sample_materials
 * @property int|null $photo_file_id
 * @property string $portfolio_status
 * @property Carbon|null $published_at
 * @property-read User $user
 * @property-read FileAsset|null $photo
 */
class TeacherProfile extends Model
{
    use BelongsToInstitution;
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'user_id',
        'headline',
        'bio',
        'qualifications',
        'subjects',
        'sample_materials',
        'photo_file_id',
        'portfolio_status',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subjects' => 'array',
            'sample_materials' => 'array',
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<FileAsset, $this>
     */
    public function photo(): BelongsTo
    {
        return $this->belongsTo(FileAsset::class, 'photo_file_id');
    }

    public function isPublished(): bool
    {
        return $this->portfolio_status === 'published';
    }

    public function isPending(): bool
    {
        return $this->portfolio_status === 'pending';
    }

    public function isDraft(): bool
    {
        return $this->portfolio_status === 'draft';
    }

    public function isRejected(): bool
    {
        return $this->portfolio_status === 'rejected';
    }
}
