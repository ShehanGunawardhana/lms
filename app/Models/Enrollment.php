<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use App\Support\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $institution_id
 * @property int $student_id
 * @property int $class_id
 * @property EnrollmentStatus|string $status
 * @property string $source
 * @property string|null $notes
 * @property Carbon $enrolled_at
 * @property Carbon|null $dropped_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Student|null $student
 * @property-read SchoolClass|null $schoolClass
 * @property-read Institution|null $institution
 */
class Enrollment extends Model
{
    use BelongsToInstitution;
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'student_id',
        'class_id',
        'status',
        'source',
        'notes',
        'enrolled_at',
        'dropped_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => EnrollmentStatus::class,
            'enrolled_at' => 'datetime',
            'dropped_at' => 'datetime',
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
     * @return BelongsTo<SchoolClass, $this>
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * @param  Builder<Enrollment>  $query
     * @return Builder<Enrollment>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', EnrollmentStatus::Active->value);
    }

    /**
     * @param  Builder<Enrollment>  $query
     * @return Builder<Enrollment>
     */
    public function scopeWaitlisted(Builder $query): Builder
    {
        return $query->where('status', EnrollmentStatus::Waitlisted->value);
    }

    /**
     * @param  Builder<Enrollment>  $query
     * @return Builder<Enrollment>
     */
    public function scopeDropped(Builder $query): Builder
    {
        return $query->where('status', EnrollmentStatus::Dropped->value);
    }

    public function isActive(): bool
    {
        $statusVal = $this->status instanceof EnrollmentStatus ? $this->status->value : (string) $this->status;

        return $statusVal === EnrollmentStatus::Active->value;
    }

    public function isWaitlisted(): bool
    {
        $statusVal = $this->status instanceof EnrollmentStatus ? $this->status->value : (string) $this->status;

        return $statusVal === EnrollmentStatus::Waitlisted->value;
    }

    public function isDropped(): bool
    {
        $statusVal = $this->status instanceof EnrollmentStatus ? $this->status->value : (string) $this->status;

        return $statusVal === EnrollmentStatus::Dropped->value;
    }
}
