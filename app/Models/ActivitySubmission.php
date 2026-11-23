<?php

namespace App\Models;

use App\Enums\ActivitySubmissionStatus;
use App\Support\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $institution_id
 * @property int $activity_id
 * @property int $student_id
 * @property int $guardian_id
 * @property int|null $file_asset_id
 * @property string|null $note
 * @property ActivitySubmissionStatus $status
 * @property int $version
 * @property bool $is_late
 * @property Carbon $submitted_at
 * @property Carbon|null $reviewed_at
 * @property int|null $reviewed_by
 * @property string|null $feedback
 * @property string|null $rating
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Institution|null $institution
 * @property-read Activity|null $activity
 * @property-read Student|null $student
 * @property-read Guardian|null $guardian
 * @property-read FileAsset|null $fileAsset
 * @property-read User|null $reviewer
 */
class ActivitySubmission extends Model
{
    use BelongsToInstitution;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'institution_id',
        'activity_id',
        'student_id',
        'guardian_id',
        'file_asset_id',
        'note',
        'status',
        'version',
        'is_late',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'feedback',
        'rating',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ActivitySubmissionStatus::class,
            'version' => 'integer',
            'is_late' => 'boolean',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Activity, $this>
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<Guardian, $this>
     */
    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class);
    }

    /**
     * @return BelongsTo<FileAsset, $this>
     */
    public function fileAsset(): BelongsTo
    {
        return $this->belongsTo(FileAsset::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isReviewed(): bool
    {
        return $this->status === ActivitySubmissionStatus::Reviewed;
    }

    public function isSubmitted(): bool
    {
        return in_array($this->status, [
            ActivitySubmissionStatus::Submitted,
            ActivitySubmissionStatus::Late,
        ], true);
    }

    public function isReturned(): bool
    {
        return $this->status === ActivitySubmissionStatus::Returned;
    }

    public function isLocked(): bool
    {
        if ($this->isReviewed()) {
            return true;
        }

        if ($this->activity && $this->activity->isPastDeadlineForStudent($this->student_id) && ! $this->activity->allow_late) {
            return true;
        }

        return false;
    }

    public function canBeModifiedByParent(): bool
    {
        if ($this->isReviewed()) {
            return false;
        }

        if ($this->activity && ! $this->activity->canAcceptSubmissionsForStudent($this->student_id)) {
            return false;
        }

        return true;
    }

    public function fileDownloadUrl(): ?string
    {
        return $this->fileAsset?->getDownloadUrl();
    }

    public function fileOriginalName(): ?string
    {
        return $this->fileAsset?->original_name;
    }

    public function formattedFileSize(): ?string
    {
        if (! $this->fileAsset || ! $this->fileAsset->size) {
            return null;
        }

        $bytes = (int) $this->fileAsset->size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }

        return number_format($bytes / 1024, 0).' KB';
    }

    public function markReviewed(User $reviewer, ?string $feedback = null, ?string $rating = null): void
    {
        $this->update([
            'status' => ActivitySubmissionStatus::Reviewed,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'feedback' => $feedback,
            'rating' => $rating,
        ]);

        audit(
            event: 'activity.submission_reviewed',
            description: "Submission v{$this->version} for student '{$this->student?->full_name}' marked reviewed",
            subject: $this,
            properties: [
                'activity_id' => $this->activity_id,
                'student_id' => $this->student_id,
                'version' => $this->version,
                'rating' => $rating,
            ],
            causer: $reviewer,
        );
    }

    public function markReturned(User $reviewer, string $feedback): void
    {
        $this->update([
            'status' => ActivitySubmissionStatus::Returned,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'feedback' => $feedback,
        ]);

        audit(
            event: 'activity.submission_returned',
            description: "Submission v{$this->version} for student '{$this->student?->full_name}' returned for correction",
            subject: $this,
            properties: [
                'activity_id' => $this->activity_id,
                'student_id' => $this->student_id,
                'version' => $this->version,
                'feedback' => $feedback,
            ],
            causer: $reviewer,
        );
    }

    public function reopen(User $reviewer): void
    {
        $this->update([
            'status' => ActivitySubmissionStatus::Submitted,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        audit(
            event: 'activity.submission_reopened',
            description: "Submission v{$this->version} for student '{$this->student?->full_name}' reopened",
            subject: $this,
            properties: [
                'activity_id' => $this->activity_id,
                'student_id' => $this->student_id,
                'version' => $this->version,
            ],
            causer: $reviewer,
        );
    }
}
