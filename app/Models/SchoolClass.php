<?php

namespace App\Models;

use App\Enums\ClassKind;
use App\Support\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $institution_id
 * @property int|null $academic_year_id
 * @property int|null $teacher_id
 * @property string $title
 * @property string|null $subject
 * @property ClassKind|string $kind
 * @property float $fee_amount
 * @property int $capacity
 * @property string|null $room
 * @property string|null $online_url
 * @property string|null $description
 * @property string $status
 * @property-read User|null $teacher
 * @property-read Institution|null $institution
 * @property-read Collection<int, Student> $students
 * @property-read Collection<int, Enrollment> $enrollments
 */
class SchoolClass extends Model
{
    use BelongsToInstitution;
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'academic_year_id',
        'teacher_id',
        'title',
        'subject',
        'kind',
        'fee_amount',
        'capacity',
        'room',
        'online_url',
        'description',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => ClassKind::class,
            'fee_amount' => 'decimal:2',
            'capacity' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * @return BelongsToMany<Student, $this>
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'enrollments', 'class_id', 'student_id')
            ->withPivot(['status', 'source', 'enrolled_at'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'class_id');
    }

    public function availableSeats(): int
    {
        $activeCount = $this->enrollments()->where('status', 'active')->count();

        return max(0, $this->capacity - $activeCount);
    }

    public function isFull(): bool
    {
        return $this->availableSeats() === 0;
    }
}
