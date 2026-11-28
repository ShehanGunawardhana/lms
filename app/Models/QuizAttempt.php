<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuestionType;
use App\Enums\QuizAttemptStatus;
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
 * @property int $time_spent_seconds
 * @property array<int, int>|null $question_order
 * @property array<int|string, mixed>|null $options_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Institution|null $institution
 * @property-read Quiz|null $quiz
 * @property-read User|null $user
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
     * Auto-mark objective questions, compute score, and finalize attempt.
     */
    public function finalizeAttempt(bool $isTimedOut = false): void
    {
        $this->loadMissing(['quiz.quizQuestions', 'answers.question.options']);

        if (! $this->quiz) {
            return;
        }

        $now = now();
        $this->completed_at = $now;
        $this->time_spent_seconds = (int) max(1, $now->diffInSeconds($this->started_at));
        $this->status = $isTimedOut ? QuizAttemptStatus::TimedOut : QuizAttemptStatus::Completed;

        // Calculate marks
        $quizQuestionsMap = [];
        foreach ($this->quiz->quizQuestions as $qq) {
            $quizQuestionsMap[$qq->question_id] = $qq;
        }

        $totalScore = 0.0;
        $quizTotalMarks = (float) $this->quiz->total_marks;
        if ($quizTotalMarks <= 0) {
            $quizTotalMarks = (float) $this->quiz->recalculateTotalMarks();
        }

        foreach ($this->answers as $ans) {
            $question = $ans->question;
            if (! $question) {
                continue;
            }

            $qq = $quizQuestionsMap[$question->id] ?? null;
            $maxMarks = $qq ? $qq->getEffectiveMarks() : (float) $question->default_marks;
            $penalty = $qq ? $qq->getEffectivePenalty() : (float) $question->default_penalty_marks;
            $allowPenalty = (bool) $this->quiz->allow_negative_marking;

            $isCorrect = false;
            $awarded = 0.0;

            switch ($question->type) {
                case QuestionType::SingleChoice:
                case QuestionType::TrueFalse:
                case QuestionType::PictureChoice:
                    if ($ans->selected_option_id !== null) {
                        $chosenOpt = $question->options->firstWhere('id', $ans->selected_option_id);
                        if ($chosenOpt && $chosenOpt->is_correct) {
                            $isCorrect = true;
                            $awarded = $maxMarks;
                        } elseif ($allowPenalty && $penalty > 0) {
                            $awarded = -$penalty;
                        }
                    }
                    break;

                case QuestionType::MultipleChoice:
                    if (! empty($ans->selected_option_ids)) {
                        $correctOptIds = $question->options->where('is_correct', true)->pluck('id')->all();
                        sort($correctOptIds);
                        $submitted = (array) $ans->selected_option_ids;
                        sort($submitted);
                        if ($correctOptIds === $submitted) {
                            $isCorrect = true;
                            $awarded = $maxMarks;
                        } elseif ($allowPenalty && $penalty > 0) {
                            $awarded = -$penalty;
                        }
                    }
                    break;

                case QuestionType::FillInTheBlank:
                    if ($ans->text_answer !== null && trim($ans->text_answer) !== '') {
                        $submittedText = trim($ans->text_answer);
                        $firstOption = $question->options->first();
                        $acceptable = $firstOption ? ($firstOption->acceptable_answers ?? []) : [];
                        if (empty($acceptable) && $firstOption && $firstOption->option_text) {
                            $acceptable = [$firstOption->option_text];
                        }

                        $caseSensitive = ! empty($question->settings['case_sensitive']);
                        foreach ($acceptable as $target) {
                            $match = $caseSensitive
                                ? (strcmp($submittedText, trim((string) $target)) === 0)
                                : (strcasecmp($submittedText, trim((string) $target)) === 0);

                            if ($match) {
                                $isCorrect = true;
                                $awarded = $maxMarks;
                                break;
                            }
                        }

                        if (! $isCorrect && $allowPenalty && $penalty > 0) {
                            $awarded = -$penalty;
                        }
                    }
                    break;

                case QuestionType::Ordering:
                    if (! empty($ans->ordering_answer)) {
                        $correctOrder = $question->options->sortBy('order_position')->pluck('id')->all();
                        if ($correctOrder === (array) $ans->ordering_answer) {
                            $isCorrect = true;
                            $awarded = $maxMarks;
                        } elseif ($allowPenalty && $penalty > 0) {
                            $awarded = -$penalty;
                        }
                    }
                    break;

                case QuestionType::ShortAnswer:
                    // Open-ended questions are routed to manual review queue
                    $isCorrect = null;
                    $awarded = 0.0;
                    break;

                default:
                    // Other question types e.g. matching
                    $isCorrect = false;
                    $awarded = 0.0;
            }

            $ans->is_correct = $isCorrect;
            $ans->marks_awarded = $awarded;
            $ans->save();

            $totalScore += $awarded;
        }

        $totalScore = max(0.0, $totalScore);
        $this->score = $totalScore;
        $this->total_marks = $quizTotalMarks;

        $percentage = $quizTotalMarks > 0 ? round(($totalScore / $quizTotalMarks) * 100, 2) : 0.0;
        $this->percentage = $percentage;
        $this->passed = $percentage >= (float) $this->quiz->pass_percentage;

        $this->save();
    }
}
