<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use App\Enums\QuizAttemptStatus;
use App\Enums\QuizMode;
use App\Enums\QuizShowAnswersPolicy;
use App\Enums\QuizStatus;
use App\Enums\RoleName;
use App\Livewire\Teacher\QuizGradingQueue;
use App\Models\Institution;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizQuestion;
use App\Models\User;
use App\Services\InstitutionContext;
use App\Services\QuizGrader;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuizGraderTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    private User $teacher;

    private User $student;

    private User $parent;

    private User $otherInstitutionTeacher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->institution = Institution::factory()->create();
        app(InstitutionContext::class)->set($this->institution);

        $this->teacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'two_factor_secret' => 'secret123',
            'two_factor_confirmed_at' => now(),
        ]);
        $this->teacher->assignRole(RoleName::Teacher->value);

        $this->student = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $this->student->assignRole(RoleName::Student->value);

        $this->parent = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $this->parent->assignRole(RoleName::Parent->value);

        $otherInstitution = Institution::factory()->create();
        $this->otherInstitutionTeacher = User::factory()->create([
            'institution_id' => $otherInstitution->id,
            'status' => 'active',
            'two_factor_secret' => 'secret123',
            'two_factor_confirmed_at' => now(),
        ]);
        $this->otherInstitutionTeacher->assignRole(RoleName::Teacher->value);
    }

    private function createQuizWithQuestions(bool $allowNegative = false): Quiz
    {
        $quiz = Quiz::create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
            'title' => 'Biology Comprehensive Exam',
            'mode' => QuizMode::Standard,
            'status' => QuizStatus::Published,
            'time_limit_minutes' => 45,
            'max_attempts' => 2,
            'pass_percentage' => 50.0,
            'total_marks' => 15.0,
            'allow_negative_marking' => $allowNegative,
            'show_answers_policy' => QuizShowAnswersPolicy::AfterSubmission,
        ]);

        // Q1: Objective Single Choice (5 marks, 1 penalty)
        $q1 = Question::create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
            'title' => 'Cell Structure',
            'type' => QuestionType::SingleChoice,
            'difficulty' => QuestionDifficulty::Easy,
            'question_text' => 'Which organelle produces ATP?',
            'default_marks' => 5.0,
            'default_penalty_marks' => 1.0,
        ]);
        QuestionOption::create(['question_id' => $q1->id, 'option_text' => 'Mitochondria', 'is_correct' => true, 'order_position' => 1]);
        QuestionOption::create(['question_id' => $q1->id, 'option_text' => 'Ribosome', 'is_correct' => false, 'order_position' => 2]);

        // Q2: Subjective Short Answer (10 marks)
        $q2 = Question::create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
            'title' => 'Cell Division Theory',
            'type' => QuestionType::ShortAnswer,
            'difficulty' => QuestionDifficulty::Hard,
            'question_text' => 'Explain the key stages of mitosis in detail.',
            'default_marks' => 10.0,
            'explanation' => 'Must mention Prophase, Metaphase, Anaphase, Telophase with chromosome behavior.',
        ]);

        QuizQuestion::create(['quiz_id' => $quiz->id, 'question_id' => $q1->id, 'order_position' => 1, 'marks' => 5.0, 'penalty_marks' => 1.0]);
        QuizQuestion::create(['quiz_id' => $quiz->id, 'question_id' => $q2->id, 'order_position' => 2, 'marks' => 10.0]);

        return $quiz;
    }

    public function test_quiz_grader_auto_marks_objective_and_flags_subjective_for_manual_queue(): void
    {
        $quiz = $this->createQuizWithQuestions();
        $q1 = $quiz->questions->firstWhere('type', QuestionType::SingleChoice);
        $q2 = $quiz->questions->firstWhere('type', QuestionType::ShortAnswer);
        $optCorrect = $q1->options->firstWhere('is_correct', true);

        $attempt = QuizAttempt::create([
            'institution_id' => $this->institution->id,
            'quiz_id' => $quiz->id,
            'user_id' => $this->student->id,
            'attempt_number' => 1,
            'status' => QuizAttemptStatus::InProgress,
            'started_at' => now()->subMinutes(10),
        ]);

        // Answer Q1 correctly
        QuizAttemptAnswer::create([
            'quiz_attempt_id' => $attempt->id,
            'question_id' => $q1->id,
            'selected_option_id' => $optCorrect->id,
        ]);

        // Answer Q2 with written text
        QuizAttemptAnswer::create([
            'quiz_attempt_id' => $attempt->id,
            'question_id' => $q2->id,
            'text_answer' => 'Mitosis consists of prophase, metaphase, anaphase, and telophase.',
        ]);

        $grader = app(QuizGrader::class);
        $gradedAttempt = $grader->gradeAttempt($attempt);

        $this->assertEquals(QuizAttemptStatus::Completed, $gradedAttempt->status);
        $this->assertFalse($gradedAttempt->is_fully_graded);
        $this->assertTrue($gradedAttempt->hasPendingManualGrading());
        $this->assertEquals(1, $gradedAttempt->pendingManualGradingCount());

        // Check Q1: Auto-graded
        $ans1 = $gradedAttempt->answers->firstWhere('question_id', $q1->id);
        $this->assertTrue($ans1->is_correct);
        $this->assertEquals(5.0, $ans1->marks_awarded);

        // Check Q2: Subjective awaiting manual review
        $ans2 = $gradedAttempt->answers->firstWhere('question_id', $q2->id);
        $this->assertNull($ans2->marks_awarded);
        $this->assertNull($ans2->is_correct);
        $this->assertTrue($ans2->isPendingManualGrading());

        // Current attempt score should be 5.0 out of 15.0
        $this->assertEquals(5.0, $gradedAttempt->score);
    }

    public function test_quiz_grader_applies_negative_marking_when_enabled(): void
    {
        $quiz = $this->createQuizWithQuestions(allowNegative: true);
        $q1 = $quiz->questions->firstWhere('type', QuestionType::SingleChoice);
        $optWrong = $q1->options->firstWhere('is_correct', false);

        $attempt = QuizAttempt::create([
            'institution_id' => $this->institution->id,
            'quiz_id' => $quiz->id,
            'user_id' => $this->student->id,
            'attempt_number' => 1,
            'status' => QuizAttemptStatus::InProgress,
            'started_at' => now()->subMinutes(10),
        ]);

        // Wrong answer with penalty of 1.0 mark
        $ans = QuizAttemptAnswer::create([
            'quiz_attempt_id' => $attempt->id,
            'question_id' => $q1->id,
            'selected_option_id' => $optWrong->id,
        ]);

        $grader = app(QuizGrader::class);
        $qq = $quiz->quizQuestions->firstWhere('question_id', $q1->id);
        $grader->gradeAnswer($ans, $qq, true);

        $ans->refresh();
        $this->assertFalse($ans->is_correct);
        $this->assertEquals(-1.0, $ans->marks_awarded);
    }

    public function test_teacher_can_manually_grade_subjective_answer_and_recalculate_attempt(): void
    {
        $quiz = $this->createQuizWithQuestions();
        $q2 = $quiz->questions->firstWhere('type', QuestionType::ShortAnswer);

        $attempt = QuizAttempt::create([
            'institution_id' => $this->institution->id,
            'quiz_id' => $quiz->id,
            'user_id' => $this->student->id,
            'attempt_number' => 1,
            'status' => QuizAttemptStatus::Completed,
            'is_fully_graded' => false,
            'started_at' => now()->subMinutes(20),
            'completed_at' => now()->subMinutes(5),
            'score' => 5.0,
            'total_marks' => 15.0,
            'percentage' => 33.33,
            'passed' => false,
        ]);

        $q1 = $quiz->questions->firstWhere('type', QuestionType::SingleChoice);
        $opt1 = $q1->options->firstWhere('is_correct', true);

        QuizAttemptAnswer::create([
            'quiz_attempt_id' => $attempt->id,
            'question_id' => $q1->id,
            'selected_option_id' => $opt1->id,
            'marks_awarded' => 5.0,
            'is_correct' => true,
        ]);

        $ans2 = QuizAttemptAnswer::create([
            'quiz_attempt_id' => $attempt->id,
            'question_id' => $q2->id,
            'text_answer' => 'Good overview of mitosis phases.',
            'marks_awarded' => null,
            'is_correct' => null,
        ]);

        $grader = app(QuizGrader::class);
        $gradedAns = $grader->gradeManualAnswer(
            $ans2,
            8.5,
            'Excellent explanation! Missing telophase details.',
            $this->teacher
        );

        $this->assertEquals(8.5, $gradedAns->marks_awarded);
        $this->assertTrue($gradedAns->is_correct);
        $this->assertEquals('Excellent explanation! Missing telophase details.', $gradedAns->feedback);
        $this->assertEquals($this->teacher->id, $gradedAns->graded_by);
        $this->assertNotNull($gradedAns->graded_at);

        // Verify attempt recalculated: 5.0 + 8.5 = 13.5 / 15.0 = 90% -> Passed!
        $attempt->refresh();
        $this->assertEquals(13.5, $attempt->score);
        $this->assertEquals(90.0, $attempt->percentage);
        $this->assertTrue($attempt->passed);
        $this->assertTrue($attempt->is_fully_graded);
        $this->assertNotNull($attempt->graded_at);
    }

    public function test_guest_and_student_cannot_access_manual_marking_queue(): void
    {
        $this->get(route('teacher.quizzes.grading'))
            ->assertRedirect(route('login'));

        $this->actingAs($this->student)
            ->get(route('teacher.quizzes.grading'))
            ->assertForbidden();

        $this->actingAs($this->parent)
            ->get(route('teacher.quizzes.grading'))
            ->assertForbidden();
    }

    public function test_teacher_can_view_and_filter_manual_marking_queue(): void
    {
        $quiz = $this->createQuizWithQuestions();
        $q2 = $quiz->questions->firstWhere('type', QuestionType::ShortAnswer);

        $attempt = QuizAttempt::create([
            'institution_id' => $this->institution->id,
            'quiz_id' => $quiz->id,
            'user_id' => $this->student->id,
            'attempt_number' => 1,
            'status' => QuizAttemptStatus::Completed,
            'is_fully_graded' => false,
            'started_at' => now()->subMinutes(20),
            'completed_at' => now()->subMinutes(5),
        ]);

        $ans = QuizAttemptAnswer::create([
            'quiz_attempt_id' => $attempt->id,
            'question_id' => $q2->id,
            'text_answer' => 'Student response to mitosis.',
            'marks_awarded' => null,
            'is_correct' => null,
        ]);

        Livewire::actingAs($this->teacher)
            ->test(QuizGradingQueue::class)
            ->assertStatus(200)
            ->assertSee('Quiz Manual Marking Queue')
            ->assertSee($this->student->name)
            ->assertSee('Needs Grading')
            ->assertSee('Student response to mitosis.');
    }

    public function test_teacher_can_grade_from_queue_modal_with_quick_buttons_and_feedback(): void
    {
        $quiz = $this->createQuizWithQuestions();
        $q2 = $quiz->questions->firstWhere('type', QuestionType::ShortAnswer);

        $attempt = QuizAttempt::create([
            'institution_id' => $this->institution->id,
            'quiz_id' => $quiz->id,
            'user_id' => $this->student->id,
            'attempt_number' => 1,
            'status' => QuizAttemptStatus::Completed,
            'is_fully_graded' => false,
            'started_at' => now()->subMinutes(20),
            'completed_at' => now()->subMinutes(5),
            'total_marks' => 15.0,
        ]);

        $ans = QuizAttemptAnswer::create([
            'quiz_attempt_id' => $attempt->id,
            'question_id' => $q2->id,
            'text_answer' => 'Detailed student explanation of chromosomes.',
            'marks_awarded' => null,
            'is_correct' => null,
        ]);

        Livewire::actingAs($this->teacher)
            ->test(QuizGradingQueue::class)
            ->call('openGradeModal', $ans->id)
            ->assertSet('showGradeModal', true)
            ->assertSet('maxMarks', 10.0)
            // Use quick button: Half Marks (50% = 5.0)
            ->call('setScoreQuick', 0.5)
            ->assertSet('marksAwarded', 5.0)
            // Custom mark & feedback
            ->set('marksAwarded', 8.0)
            ->set('teacherFeedback', 'Solid comprehension shown.')
            ->call('saveGrade', false)
            ->assertSet('showGradeModal', false);

        $ans->refresh();
        $this->assertEquals(8.0, $ans->marks_awarded);
        $this->assertEquals('Solid comprehension shown.', $ans->feedback);
        $this->assertEquals($this->teacher->id, $ans->graded_by);

        $attempt->refresh();
        $this->assertEquals(8.0, $attempt->score);
        $this->assertTrue($attempt->is_fully_graded);
    }

    public function test_multi_institution_isolation_prevents_viewing_or_grading_foreign_answers(): void
    {
        $quiz = $this->createQuizWithQuestions();
        $q2 = $quiz->questions->firstWhere('type', QuestionType::ShortAnswer);

        $attempt = QuizAttempt::create([
            'institution_id' => $this->institution->id,
            'quiz_id' => $quiz->id,
            'user_id' => $this->student->id,
            'attempt_number' => 1,
            'status' => QuizAttemptStatus::Completed,
            'is_fully_graded' => false,
            'started_at' => now()->subMinutes(20),
            'completed_at' => now()->subMinutes(5),
        ]);

        $ans = QuizAttemptAnswer::create([
            'quiz_attempt_id' => $attempt->id,
            'question_id' => $q2->id,
            'text_answer' => 'Internal response.',
            'marks_awarded' => null,
        ]);

        // Foreign teacher cannot see item in queue
        Livewire::actingAs($this->otherInstitutionTeacher)
            ->test(QuizGradingQueue::class)
            ->assertDontSee('Internal response.');

        // Foreign teacher cannot open grade modal for foreign answer (404)
        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($this->otherInstitutionTeacher)
            ->test(QuizGradingQueue::class)
            ->call('openGradeModal', $ans->id);
    }
}
