<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $institution_id
 * @property int $student_id
 * @property string $name
 * @property string $relationship
 * @property string $phone
 * @property string|null $national_id
 * @property int|null $photo_file_id
 * @property bool $is_active
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Institution|null $institution
 * @property-read Student|null $student
 * @property-read FileAsset|null $photo
 */
class AuthorizedPickup extends Model
{
    use BelongsToInstitution;
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'student_id',
        'name',
        'relationship',
        'phone',
        'national_id',
        'photo_file_id',
        'is_active',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<FileAsset, $this>
     */
    public function photo(): BelongsTo
    {
        return $this->belongsTo(FileAsset::class, 'photo_file_id');
    }

    /**
     * Get photo URL if available.
     */
    public function photoUrl(): ?string
    {
        return $this->photo?->getDownloadUrl();
    }
}
