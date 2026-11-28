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
use App\Livewire\Student\QuizAttemptRunner;
use App\Models\Institution;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizQuestion;
use App\Models\User;
use App\Services\InstitutionContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuizAttemptTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    private User $teacher;

    private User $student;

    private User $otherInstitutionStudent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->institution = Institution::factory()->create();
        app(InstitutionContext::class)->set($this->institution);

        $this->teacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $this->teacher->assignRole(RoleName::Teacher->value);

        $this->student = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $this->student->assignRole(RoleName::Student->value);

        $otherInstitution = Institution::factory()->create();
        $this->otherInstitutionStudent = User::factory()->create([
            'institution_id' => $otherInstitution->id,
            'status' => 'active',
        ]);
        $this->otherInstitutionStudent->assignRole(RoleName::Student->value);
    }

    private function createSampleQuiz(array $attributes = []): Quiz
    {
        return Quiz::factory()->create(array_merge([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
            'title' => 'Biology Midterm Quiz',
            'mode' => QuizMode::Standard,
            'status' => QuizStatus::Published,
            'time_limit_minutes' => 30,
            'max_attempts' => 2,
            'pass_percentage' => 50.0,
            'total_marks' => 10.0,
            'shuffle_questions' => false,
            'shuffle_options' => false,
            'show_answers_policy' => QuizShowAnswersPolicy::AfterSubmission,
        ], $attributes));
    }

    public function test_student_can_view_quiz_overview_screen(): void
    {
        $quiz = $this->createSampleQuiz();

        $this->actingAs($this->student);

        Livewire::test(QuizAttemptRunner::class, ['quiz' => $quiz])
            ->assertStatus(200)
            ->assertSee('Biology Midterm Quiz')
            ->assertSee('30 minutes')
            ->assertSee('Start Quiz');
    }

    public function test_cross_institution_quiz_access_is_forbidden(): void
    {
        $quiz = $this->createSampleQuiz();

        $this->actingAs($this->otherInstitutionStudent)
            ->get(route('quizzes.show', $quiz))
            ->assertStatus(404);
    }

    public function test_cannot_start_attempt_if_quiz_is_closed_or_not_available(): void
    {
        $quiz = $this->createSampleQuiz([
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addDays(5),
        ]);

        $this->actingAs($this->student);

        Livewire::test(QuizAttemptRunner::class, ['quiz' => $quiz])
            ->call('startAttempt')
            ->assertSet('errorMessage', 'This quiz is currently not available for attempts.')
            ->assertSet('attempt', null);
    }

    public function test_attempt_limit_is_enforced(): void
    {
        $quiz = $this->createSampleQuiz([
            'max_attempts' => 1,
        ]);

        // Create completed attempt already
        QuizAttempt::factory()->completed()->create([
            'institution_id' => $this->institution->id,
            'quiz_id' => $quiz->id,
            'user_id' => $this->student->id,
            'attempt_number' => 1,
        ]);

        $this->actingAs($this->student);

        Livewire::test(QuizAttemptRunner::class, ['quiz' => $quiz])
            ->call('startAttempt')
            ->assertSet('attempt', null)
            ->assertSee('Maximum attempts reached');
    }

    public function test_student_can_start_attempt_and_questions_are_sequenced(): void
    {
        $quiz = $this->createSampleQuiz();

        // Create 2 questions
        $q1 = Question::create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
            'title' => 'Cell Powerhouse',
            'type' => QuestionType::SingleChoice,
            'difficulty' => QuestionDifficulty::Medium,
            'question_text' => 'What is the powerhouse of the cell?',
            'default_marks' => 5.0,
        ]);
        QuestionOption::create([
            'question_id' => $q1->id,
            'option_text' => 'Mitochondria',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        QuestionOption::create([
            'question_id' => $q1->id,
            'option_text' => 'Nucleus',
            'is_correct' => false,
            'order_position' => 2,
        ]);

        $q2 = Question::create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
            'title' => 'Water Boiling Point',
            'type' => QuestionType::TrueFalse,
            'difficulty' => QuestionDifficulty::Easy,
            'question_text' => 'Water boils at 100 degrees Celsius at sea level.',
            'default_marks' => 5.0,
        ]);
        QuestionOption::create([
            'question_id' => $q2->id,
            'option_text' => 'True',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        QuestionOption::create([
            'question_id' => $q2->id,
            'option_text' => 'False',
            'is_correct' => false,
            'order_position' => 2,
        ]);

        QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_id' => $q1->id,
            'order_position' => 1,
            'marks' => 5.0,
        ]);
        QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_id' => $q2->id,
            'order_position' => 2,
            'marks' => 5.0,
        ]);

        $this->actingAs($this->student);

        $test = Livewire::test(QuizAttemptRunner::class, ['quiz' => $quiz])
            ->call('startAttempt');

        $attempt = QuizAttempt::where('quiz_id', $quiz->id)->where('user_id', $this->student->id)->first();
        $this->assertNotNull($attempt);
        $this->assertEquals(QuizAttemptStatus::InProgress, $attempt->status);
        $this->assertEquals(1, $attempt->attempt_number);

        // Verify pre-created answers exist
        $answers = QuizAttemptAnswer::where('quiz_attempt_id', $attempt->id)->get();
        $this->assertCount(2, $answers);

        $test->assertSee('What is the powerhouse of the cell?')
            ->assertSee('Mitochondria')
            ->assertSee('Nucleus');
    }

    public function test_shuffling_questions_and_options_persists_in_attempt(): void
    {
        $quiz = $this->createSampleQuiz([
            'shuffle_questions' => true,
            'shuffle_options' => true,
        ]);

        for ($i = 1; $i <= 5; $i++) {
            $q = Question::create([
                'institution_id' => $this->institution->id,
                'created_by' => $this->teacher->id,
                'title' => "Question Title {$i}",
                'type' => QuestionType::SingleChoice,
                'difficulty' => QuestionDifficulty::Easy,
                'question_text' => "Question Number {$i}",
                'default_marks' => 2.0,
            ]);
            for ($j = 1; $j <= 4; $j++) {
                QuestionOption::create([
                    'question_id' => $q->id,
                    'option_text' => "Option {$j} of Q{$i}",
                    'is_correct' => $j === 1,
                    'order_position' => $j,
                ]);
            }
            QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'question_id' => $q->id,
                'order_position' => $i,
                'marks' => 2.0,
            ]);
        }

        $this->actingAs($this->student);

        Livewire::test(QuizAttemptRunner::class, ['quiz' => $quiz])
            ->call('startAttempt');

        $attempt = QuizAttempt::where('quiz_id', $quiz->id)->first();
        $this->assertNotNull($attempt);
        $this->assertIsArray($attempt->question_order);
        $this->assertCount(5, $attempt->question_order);
        $this->assertIsArray($attempt->options_order);
        $this->assertCount(5, $attempt->options_order);
    }

    public function test_student_can_answer_single_choice_multiple_choice_and_true_false(): void
    {
        $quiz = $this->createSampleQuiz();

        // 1. Single Choice
        $qSingle = Question::create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
            'title' => 'Simple Addition',
            'type' => QuestionType::SingleChoice,
            'difficulty' => QuestionDifficulty::Easy,
            'question_text' => 'What is 2 + 2?',
            'default_marks' => 2.0,
        ]);
        $optSingle1 = QuestionOption::create([
            'question_id' => $qSingle->id,
            'option_text' => '3',
            'is_correct' => false,
            'order_position' => 1,
        ]);
        $optSingle2 = QuestionOption::create([
            'question_id' => $qSingle->id,
            'option_text' => '4',
            'is_correct' => true,
            'order_position' => 2,
        ]);

        // 2. Multiple Choice
        $qMulti = Question::create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
            'title' => 'Prime Numbers',
            'type' => QuestionType::MultipleChoice,
            'difficulty' => QuestionDifficulty::Medium,
            'question_text' => 'Which are prime numbers?',
            'default_marks' => 3.0,
        ]);
        $optM1 = QuestionOption::create([
            'question_id' => $qMulti->id,
            'option_text' => '2',
            'is_correct' => true,
            'order_position' => 1,
        ]);
        $optM2 = QuestionOption::create([
            'question_id' => $qMulti->id,
            'option_text' => '3',
            'is_correct' => true,
            'order_position' => 2,
        ]);
        $optM3 = QuestionOption::create([
            'question_id' => $qMulti->id,
            'option_text' => '4',
            'is_correct' => false,
            'order_position' => 3,
        ]);

        QuizQuestion::create(['quiz_id' => $quiz->id, 'question_id' => $qSingle->id, 'order_position' => 1, 'marks' => 2.0]);
        QuizQuestion::create(['quiz_id' => $quiz->id, 'question_id' => $qMulti->id, 'order_position' => 2, 'marks' => 3.0]);

        $this->actingAs($this->student);

        $runner = Livewire::test(QuizAttemptRunner::class, ['quiz' => $quiz])
            ->call('startAttempt');

        $attempt = QuizAttempt::where('quiz_id', $quiz->id)->first();

        // Select single choice option
        $runner->call('selectSingleOption', $optSingle2->id);

        $ansSingle = QuizAttemptAnswer::where('quiz_attempt_id', $attempt->id)->where('question_id', $qSingle->id)->first();
        $this->assertEquals($optSingle2->id, $ansSingle->selected_option_id);

        // Move to question 2
        $runner->call('nextQuestion');
        $this->assertEquals(1, $runner->get('activeQuestionIndex'));

        // Toggle multiple options
        $runner->call('toggleMultipleOption', $optM1->id)
            ->call('toggleMultipleOption', $optM2->id);

        $ansMulti = QuizAttemptAnswer::where('quiz_attempt_id', $attempt->id)->where('question_id', $qMulti->id)->first();
        $this->assertEquals([$optM1->id, $optM2->id], $ansMulti->selected_option_ids);

        // Untoggle option
        $runner->call('toggleMultipleOption', $optM2->id);
        $ansMulti->refresh();
        $this->assertEquals([$optM1->id], $ansMulti->selected_option_ids);
    }

    public function test_student_can_answer_fill_in_the_blank_and_ordering(): void
    {
        $quiz = $this->createSampleQuiz();

        // 1. Fill in blank
        $qFitb = Question::create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
            'title' => 'French Capital',
            'type' => QuestionType::FillInTheBlank,
            'difficulty' => QuestionDifficulty::Easy,
            'question_text' => 'The capital of France is [blank].',
            'default_marks' => 2.0,
        ]);
        QuestionOption::create([
            'question_id' => $qFitb->id,
            'option_text' => 'Paris',
            'is_correct' => true,
            'acceptable_answers' => ['Paris', 'paris'],
            'order_position' => 1,
        ]);

        // 2. Ordering
        $qOrder = Question::create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
            'title' => 'Number Sequencing',
            'type' => QuestionType::Ordering,
            'difficulty' => QuestionDifficulty::Medium,
            'question_text' => 'Order the numbers from least to greatest:',
            'default_marks' => 3.0,
        ]);
        $optO1 = QuestionOption::create(['question_id' => $qOrder->id, 'option_text' => 'One', 'is_correct' => true, 'order_position' => 1]);
        $optO2 = QuestionOption::create(['question_id' => $qOrder->id, 'option_text' => 'Two', 'is_correct' => true, 'order_position' => 2]);
        $optO3 = QuestionOption::create(['question_id' => $qOrder->id, 'option_text' => 'Three', 'is_correct' => true, 'order_position' => 3]);

        QuizQuestion::create(['quiz_id' => $quiz->id, 'question_id' => $qFitb->id, 'order_position' => 1, 'marks' => 2.0]);
        QuizQuestion::create(['quiz_id' => $quiz->id, 'question_id' => $qOrder->id, 'order_position' => 2, 'marks' => 3.0]);

        $this->actingAs($this->student);

        $runner = Livewire::test(QuizAttemptRunner::class, ['quiz' => $quiz])
            ->call('startAttempt');

        $attempt = QuizAttempt::where('quiz_id', $quiz->id)->first();

        // Save Fill In The Blank answer
        $runner->set('textAnswer', 'Paris')
            ->call('saveTextAnswer');

        $ansFitb = QuizAttemptAnswer::where('quiz_attempt_id', $attempt->id)->where('question_id', $qFitb->id)->first();
        $this->assertEquals('Paris', $ansFitb->text_answer);

        // Go to ordering question
        $runner->call('nextQuestion');
        $this->assertEquals(1, $runner->get('activeQuestionIndex'));

        // Swap order items
        $runner->call('moveOrderItemDown', 0); // swaps index 0 and 1
        $ansOrder = QuizAttemptAnswer::where('quiz_attempt_id', $attempt->id)->where('question_id', $qOrder->id)->first();
        $this->assertEquals([$optO2->id, $optO1->id, $optO3->id], $ansOrder->ordering_answer);

        $runner->call('moveOrderItemUp', 1); // swaps back
        $ansOrder->refresh();
        $this->assertEquals([$optO1->id, $optO2->id, $optO3->id], $ansOrder->ordering_answer);
    }

    public function test_student_can_flag_question_for_review(): void
    {
        $quiz = $this->createSampleQuiz();

        $q = Question::create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
            'title' => 'Photosynthesis Explanation',
            'type' => QuestionType::ShortAnswer,
            'difficulty' => QuestionDifficulty::Medium,
            'question_text' => 'Explain photosynthesis.',
            'default_marks' => 5.0,
        ]);
        QuizQuestion::create(['quiz_id' => $quiz->id, 'question_id' => $q->id, 'order_position' => 1, 'marks' => 5.0]);

        $this->actingAs($this->student);

        $runner = Livewire::test(QuizAttemptRunner::class, ['quiz' => $quiz])
            ->call('startAttempt');

        $attempt = QuizAttempt::where('quiz_id', $quiz->id)->first();

        $runner->call('toggleFlag')
            ->assertSet('isFlagged', true);

        $ans = QuizAttemptAnswer::where('quiz_attempt_id', $attempt->id)->where('question_id', $q->id)->first();
        $this->assertTrue($ans->is_flagged);

        // Unflag
        $runner->call('toggleFlag')
            ->assertSet('isFlagged', false);
        $ans->refresh();
        $this->assertFalse($ans->is_flagged);
    }

    public function test_submitting_attempt_calculates_score_percentage_and_marks_completed(): void
    {
        $quiz = $this->createSampleQuiz([
            'pass_percentage' => 50.0,
            'total_marks' => 10.0,
        ]);

        $q1 = Question::create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
            'title' => 'Japan Capital',
            'type' => QuestionType::SingleChoice,
            'difficulty' => QuestionDifficulty::Easy,
            'question_text' => 'Capital of Japan?',
            'default_marks' => 5.0,
        ]);
        $optTokyo = QuestionOption::create(['question_id' => $q1->id, 'option_text' => 'Tokyo', 'is_correct' => true, 'order_position' => 1]);
        $optKyoto = QuestionOption::create(['question_id' => $q1->id, 'option_text' => 'Kyoto', 'is_correct' => false, 'order_position' => 2]);

        $q2 = Question::create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
            'title' => 'Sunrise Direction',
            'type' => QuestionType::TrueFalse,
            'difficulty' => QuestionDifficulty::Easy,
            'question_text' => 'The sun rises in the west.',
            'default_marks' => 5.0,
        ]);
        $optFalse = QuestionOption::create(['question_id' => $q2->id, 'option_text' => 'False', 'is_correct' => true, 'order_position' => 1]);
        $optTrue = QuestionOption::create(['question_id' => $q2->id, 'option_text' => 'True', 'is_correct' => false, 'order_position' => 2]);

        QuizQuestion::create(['quiz_id' => $quiz->id, 'question_id' => $q1->id, 'order_position' => 1, 'marks' => 5.0]);
        QuizQuestion::create(['quiz_id' => $quiz->id, 'question_id' => $q2->id, 'order_position' => 2, 'marks' => 5.0]);

        $this->actingAs($this->student);

        $runner = Livewire::test(QuizAttemptRunner::class, ['quiz' => $quiz])
            ->call('startAttempt')
            ->call('selectSingleOption', $optTokyo->id) // Q1: Correct (5 marks)
            ->call('nextQuestion')
            ->call('selectSingleOption', $optFalse->id) // Q2: Correct (5 marks)
            ->call('submitAttempt');

        $attempt = QuizAttempt::where('quiz_id', $quiz->id)->first();
        $this->assertEquals(QuizAttemptStatus::Completed, $attempt->status);
        $this->assertEquals(10.0, $attempt->score);
        $this->assertEquals(100.0, $attempt->percentage);
        $this->assertTrue($attempt->passed);
        $this->assertNotNull($attempt->completed_at);

        $runner->assertSee('Passed')
            ->assertSee('100%');
    }

    public function test_auto_submit_on_timer_expiration_marks_as_timed_out(): void
    {
        $quiz = $this->createSampleQuiz([
            'time_limit_minutes' => 1,
            'total_marks' => 5.0,
        ]);

        $q = Question::create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
            'title' => 'Quick Question',
            'type' => QuestionType::SingleChoice,
            'difficulty' => QuestionDifficulty::Easy,
            'question_text' => 'Quick question?',
            'default_marks' => 5.0,
        ]);
        QuizQuestion::create(['quiz_id' => $quiz->id, 'question_id' => $q->id, 'order_position' => 1, 'marks' => 5.0]);

        $this->actingAs($this->student);

        $runner = Livewire::test(QuizAttemptRunner::class, ['quiz' => $quiz])
            ->call('startAttempt')
            ->call('autoSubmitTimedOut');

        $attempt = QuizAttempt::where('quiz_id', $quiz->id)->first();
        $this->assertEquals(QuizAttemptStatus::TimedOut, $attempt->status);
        $this->assertFalse($attempt->passed);

        $runner->assertSee('Time Expired');
    }
}
