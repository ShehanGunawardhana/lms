<?php

namespace App\Models;

use App\Support\Traits\BelongsToInstitution;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * @property int $id
 * @property int $institution_id
 * @property int $class_id
 * @property int|null $schedule_id
 * @property string|null $title
 * @property Carbon $date
 * @property string $starts_at
 * @property string $ends_at
 * @property string|null $room
 * @property string|null $online_url
 * @property string $status
 * @property string|null $cancel_reason
 * @property int|null $rescheduled_from_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Institution|null $institution
 * @property-read SchoolClass|null $schoolClass
 * @property-read ClassSchedule|null $schedule
 * @property-read ClassSession|null $rescheduledFrom
 */
class ClassSession extends Model
{
    use BelongsToInstitution;
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'class_id',
        'schedule_id',
        'title',
        'date',
        'starts_at',
        'ends_at',
        'room',
        'online_url',
        'status',
        'cancel_reason',
        'rescheduled_from_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Institution, $this>
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * @return BelongsTo<SchoolClass, $this>
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * @return BelongsTo<ClassSchedule, $this>
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ClassSchedule::class, 'schedule_id');
    }

    /**
     * @return BelongsTo<ClassSession, $this>
     */
    public function rescheduledFrom(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class, 'rescheduled_from_id');
    }

    /**
     * Check if this session has any attached operational data
     * (e.g. attendance records, learning materials, quiz attempts).
     * Sessions with data must never be deleted during regeneration (§7.1, T-3.3).
     */
    public function hasAttachedData(): bool
    {
        // 1. Check attendance records if table exists
        if (Schema::hasTable('attendance_records')) {
            $hasAttendance = DB::table('attendance_records')
                ->where('session_id', $this->id)
                ->exists();

            if ($hasAttendance) {
                return true;
            }
        }

        // 2. Check scan logs if table exists
        if (Schema::hasTable('scan_logs')) {
            $hasScan = DB::table('scan_logs')
                ->where('session_id', $this->id)
                ->exists();

            if ($hasScan) {
                return true;
            }
        }

        // 3. Check learning materials if polymorphic table exists
        if (Schema::hasTable('learning_materials')) {
            $hasMaterials = DB::table('learning_materials')
                ->where('materialable_type', self::class)
                ->where('materialable_id', $this->id)
                ->exists();

            if ($hasMaterials) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return HasMany<Quiz, $this>
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'class_session_id');
    }
}
