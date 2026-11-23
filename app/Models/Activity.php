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
        if ($this->deadline === null) {
            return false;
        }

        return now()->isAfter($this->deadline);
    }

    public function canAcceptSubmissions(): bool
    {
        if (! $this->isPublished()) {
            return false;
        }

        if ($this->isPastDeadline() && ! $this->allow_late) {
            return false;
        }

        return true;
    }
}
