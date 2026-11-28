<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuizAttemptStatus;
use App\Enums\QuizMode;
use App\Enums\QuizShowAnswersPolicy;
use App\Enums\QuizStatus;
use App\Support\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $institution_id
 * @property int $created_by
 * @property int|null $class_id
 * @property int|null $class_session_id
 * @property int|null $activity_id
 * @property string $title
 * @property string|null $description
 * @property string|null $instructions
 * @property QuizMode $mode
 * @property QuizStatus $status
 * @property int|null $time_limit_minutes
 * @property int|null $max_attempts
 * @property float $pass_percentage
 * @property float $total_marks
 * @property bool $shuffle_questions
 * @property bool $shuffle_options
 * @property bool $allow_negative_marking
 * @property QuizShowAnswersPolicy $show_answers_policy
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property array<string, mixed>|null $settings
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Institution|null $institution
 * @property-read User|null $creator
 * @property-read SchoolClass|null $schoolClass
 * @property-read ClassSession|null $classSession
 * @property-read Activity|null $activity
 * @property-read Collection<int, QuizQuestion> $quizQuestions
 * @property-read Collection<int, Question> $questions
 */
class Quiz extends Model
{
    use BelongsToInstitution;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'institution_id',
        'created_by',
        'class_id',
        'class_session_id',
        'activity_id',
        'title',
        'description',
        'instructions',
        'mode',
        'status',
        'time_limit_minutes',
        'max_attempts',
        'pass_percentage',
        'total_marks',
        'shuffle_questions',
        'shuffle_options',
        'allow_negative_marking',
        'show_answers_policy',
        'starts_at',
        'ends_at',
        'settings',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mode' => QuizMode::class,
            'status' => QuizStatus::class,
            'show_answers_policy' => QuizShowAnswersPolicy::class,
            'time_limit_minutes' => 'integer',
            'max_attempts' => 'integer',
            'pass_percentage' => 'float',
            'total_marks' => 'float',
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'allow_negative_marking' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<SchoolClass, $this>
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * @return BelongsTo<ClassSession, $this>
     */
    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class, 'class_session_id');
    }

    /**
     * @return BelongsTo<Activity, $this>
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    /**
     * @return HasMany<QuizQuestion, $this>
     */
    public function quizQuestions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id')->orderBy('order_position');
    }

    /**
     * @return BelongsToMany<Question, $this>
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'quiz_questions', 'quiz_id', 'question_id')
            ->withPivot(['id', 'order_position', 'marks', 'penalty_marks'])
            ->withTimestamps()
            ->orderByPivot('order_position');
    }

    /**
     * @return HasMany<QuizAttempt, $this>
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'quiz_id')->latest('id');
    }

    public function getAttemptsCountForUser(int $userId): int
    {
        return $this->attempts()->where('user_id', $userId)->where('status', '!=', QuizAttemptStatus::InProgress->value)->count();
    }

    public function getRemainingAttemptsForUser(int $userId): ?int
    {
        if (! $this->max_attempts) {
            return null; // Unlimited
        }

        $completed = $this->getAttemptsCountForUser($userId);

        return max(0, $this->max_attempts - $completed);
    }

    public function canUserAttempt(int $userId): bool
    {
        if (! $this->isAvailable()) {
            return false;
        }

        $remaining = $this->getRemainingAttemptsForUser($userId);
        if ($remaining !== null && $remaining <= 0) {
            return false;
        }

        return true;
    }

    public function isStandalone(): bool
    {
        return $this->class_id === null
            && $this->class_session_id === null
            && $this->activity_id === null;
    }

    public function isPublished(): bool
    {
        return $this->status === QuizStatus::Published;
    }

    public function isDraft(): bool
    {
        return $this->status === QuizStatus::Draft;
    }

    public function isArchived(): bool
    {
        return $this->status === QuizStatus::Archived;
    }

    public function isNursery(): bool
    {
        return $this->mode === QuizMode::Nursery;
    }

    public function isLive(): bool
    {
        return $this->mode === QuizMode::Live;
    }

    public function isPaper(): bool
    {
        return $this->mode === QuizMode::Paper;
    }

    public function isAvailable(): bool
    {
        if (! $this->isPublished()) {
            return false;
        }

        $now = now();
        if ($this->starts_at !== null && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at !== null && $now->gt($this->ends_at)) {
            return false;
        }

        return true;
    }

    public function recalculateTotalMarks(): float
    {
        $this->loadMissing('quizQuestions.question');

        $total = 0.0;
        foreach ($this->quizQuestions as $qq) {
            $total += $qq->getEffectiveMarks();
        }

        $this->total_marks = $total;
        $this->save();

        return $total;
    }

    public function duplicate(int $creatorId): self
    {
        $cloned = $this->replicate([
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $cloned->title = $this->title.' (Copy)';
        $cloned->status = QuizStatus::Draft;
        $cloned->created_by = $creatorId;
        $cloned->save();

        $this->loadMissing('quizQuestions');
        foreach ($this->quizQuestions as $qq) {
            QuizQuestion::create([
                'quiz_id' => $cloned->id,
                'question_id' => $qq->question_id,
                'order_position' => $qq->order_position,
                'marks' => $qq->marks,
                'penalty_marks' => $qq->penalty_marks,
            ]);
        }

        $cloned->recalculateTotalMarks();

        return $cloned;
    }

    public function getAttachmentTargetLabel(): string
    {
        if ($this->activity_id !== null) {
            $this->loadMissing('activity');

            return 'Activity: '.($this->activity->title ?? '#'.$this->activity_id);
        }

        if ($this->class_session_id !== null) {
            $this->loadMissing(['classSession.schoolClass']);

            $sessionTitle = $this->classSession?->title;
            if (! $sessionTitle && $this->classSession?->date) {
                $sessionTitle = $this->classSession->date->format('M j, Y');
            }

            return 'Session: '.($sessionTitle ?? '#'.$this->class_session_id);
        }

        if ($this->class_id !== null) {
            $this->loadMissing('schoolClass');

            return 'Class: '.($this->schoolClass->title ?? '#'.$this->class_id);
        }

        return 'Standalone';
    }
}
