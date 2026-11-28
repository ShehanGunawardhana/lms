<?php

namespace App\Models;

use App\Enums\ActivityStatus;
use App\Enums\ActivitySubmissionStatus;
use App\Support\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $institution_id
 * @property int $class_id
 * @property int $created_by
 * @property string $title
 * @property string|null $description
 * @property string $instructions
 * @property string $age_group
 * @property ActivityStatus $status
 * @property Carbon|null $publish_date
 * @property Carbon|null $deadline
 * @property bool $allow_late
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Institution|null $institution
 * @property-read SchoolClass|null $schoolClass
 * @property-read User|null $creator
 * @property-read Collection<int, ActivityVideo> $videos
 * @property-read Collection<int, ActivityMaterial> $materials
 * @property-read Collection<int, ActivitySubmission> $submissions
 * @property-read Collection<int, DeadlineExtension> $extensions
 */
class Activity extends Model
{
    use BelongsToInstitution;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'institution_id',
        'class_id',
        'created_by',
        'title',
        'description',
        'instructions',
        'age_group',
        'status',
        'publish_date',
        'deadline',
        'allow_late',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ActivityStatus::class,
            'publish_date' => 'date',
            'deadline' => 'datetime',
            'allow_late' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<SchoolClass, $this>
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<ActivityVideo, $this>
     */
    public function videos(): HasMany
    {
        return $this->hasMany(ActivityVideo::class, 'activity_id')->orderBy('sort_order');
    }

    /**
     * @return HasMany<ActivityMaterial, $this>
     */
    public function materials(): HasMany
    {
        return $this->hasMany(ActivityMaterial::class, 'activity_id')->orderBy('sort_order');
    }

    /**
     * @return HasMany<ActivitySubmission, $this>
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(ActivitySubmission::class, 'activity_id');
    }

    /**
     * @return MorphMany<DeadlineExtension, $this>
     */
    public function extensions(): MorphMany
    {
        return $this->morphMany(DeadlineExtension::class, 'extendable')->orderByDesc('id');
    }

    public function effectiveDeadlineForStudent(?int $studentId = null): ?Carbon
    {
        if ($studentId !== null) {
            /** @var DeadlineExtension|null $studentExt */
            $studentExt = $this->extensions()
                ->where('student_id', $studentId)
                ->first();

            if ($studentExt !== null) {
                return $studentExt->extended_deadline;
            }
        }

        /** @var DeadlineExtension|null $classExt */
        $classExt = $this->extensions()
            ->whereNull('student_id')
            ->first();

        if ($classExt !== null) {
            return $classExt->extended_deadline;
        }

        return $this->deadline;
    }

    public function isExtendedForStudent(?int $studentId = null): bool
    {
        if ($studentId !== null) {
            $hasStudentExt = $this->extensions()
                ->where('student_id', $studentId)
                ->exists();

            if ($hasStudentExt) {
                return true;
            }
        }

        return $this->extensions()
            ->whereNull('student_id')
            ->exists();
    }

    public function latestExtensionForStudent(?int $studentId = null): ?DeadlineExtension
    {
        if ($studentId !== null) {
            /** @var DeadlineExtension|null $studentExt */
            $studentExt = $this->extensions()
                ->where('student_id', $studentId)
                ->first();

            if ($studentExt !== null) {
                return $studentExt;
            }
        }

        /** @var DeadlineExtension|null $classExt */
        $classExt = $this->extensions()
            ->whereNull('student_id')
            ->first();

        return $classExt;
    }

    public function submissionForStudent(int $studentId): ?ActivitySubmission
    {
        return $this->submissions()->where('student_id', $studentId)->latest('version')->first();
    }

    public function statusForStudent(int $studentId): ActivitySubmissionStatus
    {
        $submission = $this->submissionForStudent($studentId);

        if ($submission === null) {
            return ActivitySubmissionStatus::NotStarted;
        }

        return $submission->status;
    }

    public function isDraft(): bool
    {
        return $this->status === ActivityStatus::Draft;
    }

    public function isPublished(): bool
    {
        return $this->status === ActivityStatus::Published;
    }

    public function isClosed(): bool
    {
        return $this->status === ActivityStatus::Closed;
    }

    public function isPastDeadline(): bool
    {
        return $this->isPastDeadlineForStudent(null);
    }

    public function isPastDeadlineForStudent(?int $studentId = null): bool
    {
        $deadline = $this->effectiveDeadlineForStudent($studentId);

        if ($deadline === null) {
            return false;
        }

        return now()->isAfter($deadline);
    }

    public function canAcceptSubmissions(): bool
    {
        return $this->canAcceptSubmissionsForStudent(null);
    }

    public function canAcceptSubmissionsForStudent(?int $studentId = null): bool
    {
        if (! $this->isPublished()) {
            return false;
        }

        if ($this->isPastDeadlineForStudent($studentId) && ! $this->allow_late) {
            return false;
        }

        return true;
    }

    /**
     * @return HasMany<Quiz, $this>
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'activity_id');
    }
}
