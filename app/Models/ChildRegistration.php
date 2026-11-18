<?php

namespace App\Models;

use App\Support\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $institution_id
 * @property int $parent_profile_id
 * @property string $first_name
 * @property string $last_name
 * @property Carbon $dob
 * @property string|null $gender
 * @property string $nursery_group
 * @property string|null $medical_notes
 * @property string|null $dietary_needs
 * @property string|null $special_notes
 * @property-read ParentProfile|null $parentProfile
 * @property-read Institution|null $institution
 * @property-read string $full_name
 * @property-read int $age_years
 */
class ChildRegistration extends Model
{
    use BelongsToInstitution;
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'parent_profile_id',
        'first_name',
        'last_name',
        'dob',
        'gender',
        'nursery_group',
        'medical_notes',
        'dietary_needs',
        'special_notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'medical_notes' => 'encrypted',
        ];
    }

    /**
     * @return BelongsTo<ParentProfile, $this>
     */
    public function parentProfile(): BelongsTo
    {
        return $this->belongsTo(ParentProfile::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getAgeYearsAttribute(): int
    {
        return $this->dob->age;
    }
}
