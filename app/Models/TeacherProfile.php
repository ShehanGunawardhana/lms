<?php

namespace App\Models;

use App\Support\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherProfile extends Model
{
    use BelongsToInstitution;
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'user_id',
        'bio',
        'qualifications',
        'subjects',
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
}
