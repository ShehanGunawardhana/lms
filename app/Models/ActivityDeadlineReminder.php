<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $institution_id
 * @property int $activity_id
 * @property int $student_id
 * @property int $guardian_id
 * @property Carbon $reminded_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Institution|null $institution
 * @property-read Activity|null $activity
 * @property-read Student|null $student
 * @property-read Guardian|null $guardian
 */
class ActivityDeadlineReminder extends Model
{
    use BelongsToInstitution;
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'activity_id',
        'student_id',
        'guardian_id',
        'reminded_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reminded_at' => 'datetime',
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
}
