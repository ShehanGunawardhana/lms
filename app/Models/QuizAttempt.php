<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuestionType;
use App\Enums\QuizAttemptStatus;
use App\Services\QuizGrader;
use App\Support\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $institution_id
 * @property int $quiz_id
 * @property int $user_id
 * @property int $attempt_number
 * @property QuizAttemptStatus $status
 * @property Carbon $started_at
 * @property Carbon|null $completed_at
 * @property float|null $score
 * @property float|null $total_marks
 * @property float|null $percentage
 * @property bool|null $passed
 * @property bool $is_fully_graded
 * @property Carbon|null $graded_at
 * @property int|null $graded_by
 * @property int $time_spent_seconds
 * @property array<int, int>|null $question_order
 * @property array<int|string, mixed>|null $options_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Institution|null $institution
 * @property-read Quiz|null $quiz
 * @property-read User|null $user
 * @property-read User|null $grader
 * @property-read Collection<int, QuizAttemptAnswer> $answers
 */
class QuizAttempt extends Model
{
    use BelongsToInstitution;
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'quiz_id',
        'user_id',
        'attempt_number',
        'status',
        'started_at',
        'completed_at',
        'score',
        'total_marks',
        'percentage',
        'passed',
        'is_fully_graded',
        'graded_at',
        'graded_by',
        'time_spent_seconds',
        'question_order',
        'options_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attempt_number' => 'integer',
            'status' => QuizAttemptStatus::class,
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'score' => 'float',
            'total_marks' => 'float',
            'percentage' => 'float',
            'passed' => 'boolean',
            'is_fully_graded' => 'boolean',
            'graded_at' => 'datetime',
            'graded_by' => 'integer',
            'time_spent_seconds' => 'integer',
            'question_order' => 'array',
            'options_order' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Quiz, $this>
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany<QuizAttemptAnswer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QuizAttemptAnswer::class, 'quiz_attempt_id');
    }

    public function isInProgress(): bool
    {
        return $this->status === QuizAttemptStatus::InProgress;
    }

    public function isCompleted(): bool
    {
        return $this->status === QuizAttemptStatus::Completed;
    }

    public function isTimedOut(): bool
    {
        return $this->status === QuizAttemptStatus::TimedOut;
    }

    public function isFinished(): bool
    {
        return $this->status->isFinished();
    }

    public function getRemainingSeconds(): ?int
    {
        $this->loadMissing('quiz');
        if (! $this->quiz || ! $this->quiz->time_limit_minutes) {
            return null;
        }

        $totalLimitSeconds = $this->quiz->time_limit_minutes * 60;
        $elapsedSeconds = now()->diffInSeconds($this->started_at);

        return (int) max(0, $totalLimitSeconds - $elapsedSeconds);
    }

    public function hasTimeExpired(): bool
    {
        $remaining = $this->getRemainingSeconds();

        return $remaining !== null && $remaining <= 0;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getQuestionsSequence(): Collection
    {
        $this->loadMissing('quiz.quizQuestions.question.options');

        $questionsMap = [];
        if ($this->quiz) {
            foreach ($this->quiz->quizQuestions as $qq) {
                if ($qq->question) {
                    $questionsMap[$qq->question->id] = $qq->question;
                }
            }
        }

        if (! empty($this->question_order)) {
            $sorted = new Collection;
            foreach ($this->question_order as $qId) {
                if (isset($questionsMap[$qId])) {
                    $sorted->push($questionsMap[$qId]);
                }
            }

            return $sorted;
        }

        return new Collection(array_values($questionsMap));
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function hasPendingManualGrading(): bool
    {
        if (! $this->is_fully_graded) {
            return true;
        }

        return $this->answers()->whereNull('marks_awarded')->whereHas('question', function ($q) {
            $q->where('type', QuestionType::ShortAnswer->value);
        })->exists();
    }

    public function pendingManualGradingCount(): int
    {
        return $this->answers()->whereNull('marks_awarded')->whereHas('question', function ($q) {
            $q->where('type', QuestionType::ShortAnswer->value);
        })->count();
    }

    /**
     * Auto-mark objective questions, compute score, and finalize attempt.
     */
    public function finalizeAttempt(bool $isTimedOut = false): void
    {
        app(QuizGrader::class)->gradeAttempt($this, $isTimedOut);
    }
}
