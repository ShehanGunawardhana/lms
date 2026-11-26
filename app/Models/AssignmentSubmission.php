<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AssignmentSubmissionStatus;
use App\Support\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $institution_id
 * @property int $assignment_id
 * @property int $student_id
 * @property int $submitted_by
 * @property int|null $file_asset_id
 * @property string|null $note
 * @property AssignmentSubmissionStatus $status
 * @property int $version
 * @property bool $is_late
 * @property Carbon $submitted_at
 * @property Carbon|null $reviewed_at
 * @property int|null $reviewed_by
 * @property string|null $feedback
 * @property int|null $marks
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Institution|null $institution
 * @property-read Assignment|null $assignment
 * @property-read Student|null $student
 * @property-read User|null $submitter
 * @property-read FileAsset|null $fileAsset
 * @property-read User|null $reviewer
 */
class AssignmentSubmission extends Model
{
    use BelongsToInstitution;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'institution_id',
        'assignment_id',
        'student_id',
        'submitted_by',
        'file_asset_id',
        'note',
        'status',
        'version',
        'is_late',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'feedback',
        'marks',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => AssignmentSubmissionStatus::class,
            'version' => 'integer',
            'is_late' => 'boolean',
            'marks' => 'integer',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Assignment, $this>
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
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

    public function isGraded(): bool
    {
        return $this->status === AssignmentSubmissionStatus::Graded;
    }

    public function isSubmitted(): bool
    {
        return in_array($this->status, [
            AssignmentSubmissionStatus::Submitted,
            AssignmentSubmissionStatus::Late,
        ], true);
    }

    public function isReturned(): bool
    {
        return $this->status === AssignmentSubmissionStatus::Returned;
    }

    public function isLate(): bool
    {
        return $this->status === AssignmentSubmissionStatus::Late || $this->is_late;
    }

    public function isLocked(): bool
    {
        if ($this->isGraded()) {
            return true;
        }

        if ($this->assignment && $this->assignment->isPastDeadlineForStudent($this->student_id) && ! $this->assignment->allow_late) {
            return true;
        }

        return false;
    }

    public function canBeModified(): bool
    {
        if ($this->isGraded()) {
            return false;
        }

        if ($this->assignment && ! $this->assignment->canAcceptSubmissionsForStudent($this->student_id)) {
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

    public function markGraded(User $reviewer, int $marks, ?string $feedback = null): void
    {
        $this->update([
            'status' => AssignmentSubmissionStatus::Graded,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'marks' => $marks,
            'feedback' => $feedback,
        ]);

        audit(
            event: 'assignment.submission_graded',
            description: "Submission v{$this->version} for student '{$this->student?->full_name}' graded with {$marks} marks",
            subject: $this,
            properties: [
                'assignment_id' => $this->assignment_id,
                'student_id' => $this->student_id,
                'version' => $this->version,
                'marks' => $marks,
            ]
        );
    }

    public function markReturned(User $reviewer, ?string $feedback = null): void
    {
        $this->update([
            'status' => AssignmentSubmissionStatus::Returned,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'feedback' => $feedback,
        ]);

        audit(
            event: 'assignment.submission_returned',
            description: "Submission v{$this->version} for student '{$this->student?->full_name}' returned for correction",
            subject: $this,
            properties: [
                'assignment_id' => $this->assignment_id,
                'student_id' => $this->student_id,
                'version' => $this->version,
            ]
        );
    }

    public function reopen(User $reviewer): void
    {
        $this->update([
            'status' => $this->is_late ? AssignmentSubmissionStatus::Late : AssignmentSubmissionStatus::Submitted,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        audit(
            event: 'assignment.submission_reopened',
            description: "Submission v{$this->version} for student '{$this->student?->full_name}' reopened by {$reviewer->name}",
            subject: $this,
            properties: [
                'assignment_id' => $this->assignment_id,
                'student_id' => $this->student_id,
            ]
        );
    }
}
