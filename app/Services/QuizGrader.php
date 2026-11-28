<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\QuestionType;
use App\Enums\QuizAttemptStatus;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class QuizGrader
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Auto-mark all objective questions in an attempt, compute score, and finalize attempt.
     */
    public function gradeAttempt(QuizAttempt $attempt, bool $isTimedOut = false): QuizAttempt
    {
        $attempt->loadMissing(['quiz.quizQuestions', 'answers.question.options']);

        if (! $attempt->quiz) {
            return $attempt;
        }

        $now = now();
        $attempt->completed_at = $now;
        $attempt->time_spent_seconds = (int) max(1, $now->diffInSeconds($attempt->started_at));
        $attempt->status = $isTimedOut ? QuizAttemptStatus::TimedOut : QuizAttemptStatus::Completed;

        // Map quiz questions for custom marks & penalty overrides
        $quizQuestionsMap = [];
        foreach ($attempt->quiz->quizQuestions as $qq) {
            $quizQuestionsMap[$qq->question_id] = $qq;
        }

        $allowNegative = (bool) $attempt->quiz->allow_negative_marking;
        $hasPendingManual = false;

        foreach ($attempt->answers as $ans) {
            $question = $ans->question;
            if (! $question) {
                continue;
            }

            $qq = $quizQuestionsMap[$question->id] ?? null;

            // Short Answer / Subjective questions are routed to manual review queue
            if ($question->type === QuestionType::ShortAnswer) {
                if ($ans->marks_awarded === null) {
                    $ans->is_correct = null;
                    $ans->save();
                    $hasPendingManual = true;
                }

                continue;
            }

            $this->gradeAnswer($ans, $qq, $allowNegative);
        }

        $attempt->is_fully_graded = ! $hasPendingManual;
        if ($attempt->is_fully_graded) {
            $attempt->graded_at = $now;
        }

        $this->recalculateAttempt($attempt);

        return $attempt;
    }

    /**
     * Auto-mark an individual objective answer.
     */
    public function gradeAnswer(
        QuizAttemptAnswer $answer,
        ?QuizQuestion $quizQuestion = null,
        bool $allowNegative = false
    ): void {
        $question = $answer->question;
        if (! $question) {
            return;
        }

        $maxMarks = $quizQuestion ? $quizQuestion->getEffectiveMarks() : (float) $question->default_marks;
        $penalty = $quizQuestion ? $quizQuestion->getEffectivePenalty() : (float) $question->default_penalty_marks;

        $isCorrect = false;
        $awarded = 0.0;

        switch ($question->type) {
            case QuestionType::SingleChoice:
            case QuestionType::TrueFalse:
            case QuestionType::PictureChoice:
                if ($answer->selected_option_id !== null) {
                    $chosenOpt = $question->options->firstWhere('id', $answer->selected_option_id);
                    if ($chosenOpt && $chosenOpt->is_correct) {
                        $isCorrect = true;
                        $awarded = $maxMarks;
                    } elseif ($allowNegative && $penalty > 0) {
                        $awarded = -$penalty;
                    }
                }
                break;

            case QuestionType::MultipleChoice:
                if (! empty($answer->selected_option_ids)) {
                    $correctOptIds = $question->options->where('is_correct', true)->pluck('id')->all();
                    sort($correctOptIds);
                    $submitted = (array) $answer->selected_option_ids;
                    sort($submitted);

                    if ($correctOptIds === $submitted) {
                        $isCorrect = true;
                        $awarded = $maxMarks;
                    } elseif ($allowNegative && $penalty > 0) {
                        $awarded = -$penalty;
                    }
                }
                break;

            case QuestionType::FillInTheBlank:
                if ($answer->text_answer !== null && trim($answer->text_answer) !== '') {
                    $submittedText = trim($answer->text_answer);
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

                    if (! $isCorrect && $allowNegative && $penalty > 0) {
                        $awarded = -$penalty;
                    }
                }
                break;

            case QuestionType::Ordering:
                if (! empty($answer->ordering_answer)) {
                    $correctOrder = $question->options->sortBy('order_position')->pluck('id')->all();
                    if ($correctOrder === (array) $answer->ordering_answer) {
                        $isCorrect = true;
                        $awarded = $maxMarks;
                    } elseif ($allowNegative && $penalty > 0) {
                        $awarded = -$penalty;
                    }
                }
                break;

            case QuestionType::Matching:
                if (! empty($answer->matching_answer)) {
                    $totalPairs = $question->options->count();
                    $correctPairs = 0;
                    foreach ($answer->matching_answer as $leftId => $rightId) {
                        if ((int) $leftId === (int) $rightId) {
                            $correctPairs++;
                        }
                    }

                    if ($correctPairs === $totalPairs && $totalPairs > 0) {
                        $isCorrect = true;
                        $awarded = $maxMarks;
                    } elseif ($allowNegative && $penalty > 0) {
                        $isCorrect = false;
                        $awarded = -$penalty;
                    } else {
                        $isCorrect = false;
                        $awarded = $totalPairs > 0 ? round(($correctPairs / $totalPairs) * $maxMarks, 2) : 0.0;
                    }
                }
                break;

            case QuestionType::ShortAnswer:
                // Subjective question handled in manual queue
                return;

            default:
                $isCorrect = false;
                $awarded = 0.0;
        }

        $answer->is_correct = $isCorrect;
        $answer->marks_awarded = $awarded;
        $answer->save();
    }

    /**
     * Manually mark a subjective answer (e.g. Short Answer) and recalculate the attempt score.
     */
    public function gradeManualAnswer(
        QuizAttemptAnswer $answer,
        float $marksAwarded,
        ?string $feedback = null,
        ?User $grader = null
    ): QuizAttemptAnswer {
        $answer->loadMissing(['question', 'attempt.quiz.quizQuestions']);

        $question = $answer->question;
        $quiz = $answer->attempt?->quiz;

        $maxMarks = (float) ($question ? $question->default_marks : 1.0);
        if ($quiz && $question) {
            $qq = $quiz->quizQuestions->firstWhere('question_id', $question->id);
            if ($qq) {
                $maxMarks = $qq->getEffectiveMarks();
            }
        }

        // Clamp marks awarded
        $marksAwarded = max(0.0, min($maxMarks, $marksAwarded));

        $answer->marks_awarded = $marksAwarded;
        $answer->is_correct = ($marksAwarded > 0);
        $answer->feedback = $feedback;
        $answer->graded_by = $grader?->id;
        $answer->graded_at = now();
        $answer->save();

        if ($answer->attempt) {
            $this->recalculateAttempt($answer->attempt, $grader);
        }

        return $answer;
    }

    /**
     * Recalculate score, percentage, and passed threshold on an attempt.
     */
    public function recalculateAttempt(QuizAttempt $attempt, ?User $grader = null): void
    {
        $attempt->loadMissing(['quiz.quizQuestions', 'answers.question']);

        if (! $attempt->quiz) {
            return;
        }

        $totalScore = 0.0;
        $quizTotalMarks = (float) $attempt->quiz->total_marks;
        if ($quizTotalMarks <= 0) {
            $quizTotalMarks = (float) $attempt->quiz->recalculateTotalMarks();
        }

        $hasPending = false;
        foreach ($attempt->answers as $ans) {
            if ($ans->marks_awarded === null) {
                $hasPending = true;
            } else {
                $totalScore += (float) $ans->marks_awarded;
            }
        }

        $totalScore = max(0.0, $totalScore);
        $attempt->score = $totalScore;
        $attempt->total_marks = $quizTotalMarks;

        $percentage = $quizTotalMarks > 0 ? round(($totalScore / $quizTotalMarks) * 100, 2) : 0.0;
        $attempt->percentage = $percentage;
        $attempt->passed = $percentage >= (float) $attempt->quiz->pass_percentage;

        $attempt->is_fully_graded = ! $hasPending;
        if ($attempt->is_fully_graded && ! $attempt->graded_at) {
            $attempt->graded_at = now();
            if ($grader) {
                $attempt->graded_by = $grader->id;
            }
        }

        $attempt->save();
    }

    /**
     * Build query for manual marking queue items.
     *
     * @return Builder<QuizAttemptAnswer>
     */
    public function getManualGradingQueueQuery(
        int $institutionId,
        ?int $quizId = null,
        ?int $classId = null,
        string $status = 'pending'
    ): Builder {
        /** @var Builder<QuizAttemptAnswer> $query */
        $query = QuizAttemptAnswer::query()
            ->whereHas('attempt', function (Builder $q) use ($institutionId, $quizId, $classId) {
                $q->where('institution_id', $institutionId)
                    ->whereIn('status', [QuizAttemptStatus::Completed, QuizAttemptStatus::TimedOut]);

                if ($quizId) {
                    $q->where('quiz_id', $quizId);
                }

                if ($classId) {
                    $q->whereHas('quiz', fn (Builder $qz) => $qz->where('class_id', $classId));
                }
            })
            ->whereHas('question', function (Builder $q) {
                $q->where('type', QuestionType::ShortAnswer);
            });

        if ($status === 'pending') {
            $query->whereNull('marks_awarded');
        } elseif ($status === 'graded') {
            $query->whereNotNull('marks_awarded');
        }

        return $query;
    }
}
