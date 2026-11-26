<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AssignmentStatus;
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
 * @property string|null $instructions
 * @property AssignmentStatus $status
 * @property array<string> $allowed_file_types
 * @property int $max_file_size_mb
 * @property Carbon $deadline
 * @property bool $allow_late
 * @property int $resubmission_limit
 * @property int $total_marks
 * @property int|null $pass_marks
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Institution|null $institution
 * @property-read SchoolClass|null $schoolClass
 * @property-read User|null $creator
 * @property-read Collection<int, AssignmentAttachment> $attachments
 * @property-read Collection<int, DeadlineExtension> $extensions
 */
class Assignment extends Model
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
        'status',
        'allowed_file_types',
        'max_file_size_mb',
        'deadline',
        'allow_late',
        'resubmission_limit',
        'total_marks',
        'pass_marks',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => AssignmentStatus::class,
            'allowed_file_types' => 'array',
            'max_file_size_mb' => 'integer',
            'deadline' => 'datetime',
            'allow_late' => 'boolean',
            'resubmission_limit' => 'integer',
            'total_marks' => 'integer',
            'pass_marks' => 'integer',
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
     * @return HasMany<AssignmentAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(AssignmentAttachment::class, 'assignment_id')->orderBy('sort_order');
    }

    /**
     * @return MorphMany<DeadlineExtension, $this>
     */
    public function extensions(): MorphMany
    {
        return $this->morphMany(DeadlineExtension::class, 'extendable');
    }

    public function isDraft(): bool
    {
        return $this->status === AssignmentStatus::Draft;
    }

    public function isPublished(): bool
    {
        return $this->status === AssignmentStatus::Published;
    }

    public function isClosed(): bool
    {
        return $this->status === AssignmentStatus::Closed;
    }

    public function effectiveDeadlineForStudent(?int $studentId = null): Carbon
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

    public function isPastDeadlineForStudent(?int $studentId = null): bool
    {
        return $this->effectiveDeadlineForStudent($studentId)->isPast();
    }

    public function isFileTypeAllowed(string $extension): bool
    {
        $normalized = strtolower(trim($extension, '. '));
        $allowed = array_map(fn ($ext) => strtolower(trim((string) $ext, '. ')), $this->allowed_file_types ?: []);

        return in_array($normalized, $allowed, true);
    }

    public function formattedAllowedFileTypes(): string
    {
        $types = array_map(fn ($ext) => strtoupper(ltrim((string) $ext, '.')), $this->allowed_file_types ?: []);

        return implode(', ', $types);
    }
}
