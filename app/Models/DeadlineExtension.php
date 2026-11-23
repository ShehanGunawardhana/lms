<?php

namespace App\Models;

use App\Support\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $institution_id
 * @property string $extendable_type
 * @property int $extendable_id
 * @property int|null $student_id
 * @property int $extended_by
 * @property Carbon|null $original_deadline
 * @property Carbon $extended_deadline
 * @property string $reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model|\Eloquent $extendable
 * @property-read Institution|null $institution
 * @property-read Student|null $student
 * @property-read User|null $extendedBy
 */
class DeadlineExtension extends Model
{
    use BelongsToInstitution;
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'extendable_type',
        'extendable_id',
        'student_id',
        'extended_by',
        'original_deadline',
        'extended_deadline',
        'reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'original_deadline' => 'datetime',
            'extended_deadline' => 'datetime',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function extendable(): MorphTo
    {
        return $this->morphTo();
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
    public function extendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'extended_by');
    }

    public function isForEveryone(): bool
    {
        return $this->student_id === null;
    }

    public function isForIndividual(): bool
    {
        return $this->student_id !== null;
    }

    public function targetLabel(): string
    {
        if ($this->student !== null) {
            return $this->student->full_name;
        }

        return (string) __('Everyone');
    }
}
