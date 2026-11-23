<?php

namespace App\Models;

use App\Enums\StudentKind;
use App\Enums\StudentStatus;
use App\Support\Traits\BelongsToInstitution;
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
 * @property string $student_code
 * @property int|null $user_id
 * @property string $first_name
 * @property string $last_name
 * @property Carbon $dob
 * @property string|null $gender
 * @property int|null $photo_file_id
 * @property StudentStatus|string $status
 * @property StudentKind|string $kind
 * @property int|null $registered_by
 * @property Carbon|null $registered_at
 * @property-read User|null $user
 * @property-read User|null $registeredBy
 * @property-read FileAsset|null $photo
 * @property-read Institution|null $institution
 * @property-read Collection<int, Guardian> $guardians
 * @property-read Collection<int, SchoolClass> $classes
 * @property-read Collection<int, Enrollment> $enrollments
 * @property-read string $full_name
 * @property-read int $age_years
 */
class Student extends Model
{
    use BelongsToInstitution;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'institution_id',
        'student_code',
        'user_id',
        'first_name',
        'last_name',
        'dob',
        'gender',
        'photo_file_id',
        'status',
        'kind',
        'registered_by',
        'registered_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'registered_at' => 'datetime',
            'status' => StudentStatus::class,
            'kind' => StudentKind::class,
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
     * @return BelongsTo<User, $this>
     */
    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    /**
     * @return BelongsTo<FileAsset, $this>
     */
    public function photo(): BelongsTo
    {
        return $this->belongsTo(FileAsset::class, 'photo_file_id');
    }

    /**
     * @return BelongsToMany<Guardian, $this>
     */
    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'guardian_student')
            ->withPivot(['relationship', 'is_primary'])
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<SchoolClass, $this>
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'enrollments', 'student_id', 'class_id')
            ->withPivot(['status', 'source', 'enrolled_at'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * @return HasMany<ActivitySubmission, $this>
     */
    public function activitySubmissions(): HasMany
    {
        return $this->hasMany(ActivitySubmission::class);
    }

    /**
     * @return HasMany<DeadlineExtension, $this>
     */
    public function deadlineExtensions(): HasMany
    {
        return $this->hasMany(DeadlineExtension::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getAgeYearsAttribute(): int
    {
        return $this->dob->age;
    }

    public static function generateStudentCode(int $institutionId, string $prefix = 'NUR'): string
    {
        $year = date('Y');
        $random = strtoupper(bin2hex(random_bytes(2)));
        $count = static::withoutGlobalScopes()->where('institution_id', $institutionId)->count() + 1;
        $padded = str_pad((string) $count, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$year}-{$padded}-{$random}";
    }
}
