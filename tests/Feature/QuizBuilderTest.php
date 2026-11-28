<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\QuizMode;
use App\Enums\QuizShowAnswersPolicy;
use App\Enums\QuizStatus;
use App\Enums\RoleName;
use App\Livewire\Teacher\QuizBuilder;
use App\Models\Activity;
use App\Models\ClassSession;
use App\Models\Institution;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\SchoolClass;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuizBuilderTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    private User $teacher;

    private User $admin;

    private User $student;

    private User $parent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->institution = Institution::factory()->create();

        $this->teacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'two_factor_secret' => 'secret123',
            'two_factor_confirmed_at' => now(),
        ]);
        $this->teacher->assignRole(RoleName::Teacher->value);

        $this->admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'two_factor_secret' => 'secret123',
            'two_factor_confirmed_at' => now(),
        ]);
        $this->admin->assignRole(RoleName::Admin->value);

        $this->student = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'two_factor_secret' => 'secret123',
            'two_factor_confirmed_at' => now(),
        ]);
        $this->student->assignRole(RoleName::Student->value);

        $this->parent = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'two_factor_secret' => 'secret123',
            'two_factor_confirmed_at' => now(),
        ]);
        $this->parent->assignRole(RoleName::Parent->value);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('teacher.quizzes.index'))
            ->assertRedirect(route('login'));
    }

    public function test_student_and_parent_cannot_access_quiz_builder(): void
    {
        $this->actingAs($this->student)
            ->get(route('teacher.quizzes.index'))
            ->assertStatus(403);

        $this->actingAs($this->parent)
            ->get(route('teacher.quizzes.index'))
            ->assertStatus(403);
    }

    public function test_teacher_and_admin_can_access_quiz_builder(): void
    {
        $this->actingAs($this->teacher)
            ->get(route('teacher.quizzes.index'))
            ->assertStatus(200);

        $this->actingAs($this->admin)
            ->get(route('teacher.quizzes.index'))
            ->assertStatus(200);
    }

    public function test_teacher_can_create_standalone_draft_quiz_with_all_settings(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(QuizBuilder::class)
            ->call('openCreateModal')
            ->set('title', 'General Science Term 1 Assessment')
            ->set('description', 'Comprehensive quiz covering physics, chemistry and biology basics.')
            ->set('instructions', 'You have 45 minutes to answer all questions.')
            ->set('mode', QuizMode::Standard->value)
            ->set('status', QuizStatus::Draft->value)
            ->set('timeLimitMinutes', 45)
            ->set('maxAttempts', 2)
            ->set('passPercentage', 65.0)
            ->set('shuffleQuestions', true)
            ->set('shuffleOptions', true)
            ->set('allowNegativeMarking', true)
            ->set('showAnswersPolicy', QuizShowAnswersPolicy::AfterClosing->value)
            ->set('attachmentType', 'standalone')
            ->set('startsAt', now()->addDay()->format('Y-m-d\TH:i'))
            ->set('endsAt', now()->addDays(7)->format('Y-m-d\TH:i'))
            ->call('saveQuiz')
            ->assertHasNoErrors()
            ->assertSet('showQuizModal', false);

        $quiz = Quiz::where('title', 'General Science Term 1 Assessment')->firstOrFail();
        $this->assertSame($this->institution->id, $quiz->institution_id);
        $this->assertSame($this->teacher->id, $quiz->created_by);
        $this->assertTrue($quiz->isStandalone());
        $this->assertTrue($quiz->isDraft());
        $this->assertSame(45, $quiz->time_limit_minutes);
        $this->assertSame(2, $quiz->max_attempts);
        $this->assertSame(65.0, (float) $quiz->pass_percentage);
        $this->assertTrue($quiz->shuffle_questions);
        $this->assertTrue($quiz->shuffle_options);
        $this->assertTrue($quiz->allow_negative_marking);
        $this->assertSame(QuizShowAnswersPolicy::AfterClosing, $quiz->show_answers_policy);
        $this->assertSame('Standalone', $quiz->getAttachmentTargetLabel());
    }

    public function test_quiz_validation_rejects_empty_title_and_invalid_date_ranges(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(QuizBuilder::class)
            ->call('openCreateModal')
            ->set('title', '')
            ->call('saveQuiz')
            ->assertHasErrors(['title']);

        Livewire::actingAs($this->teacher)
            ->test(QuizBuilder::class)
            ->call('openCreateModal')
            ->set('title', 'Invalid Dates Quiz')
            ->set('startsAt', now()->addDays(5)->format('Y-m-d\TH:i'))
            ->set('endsAt', now()->addDays(2)->format('Y-m-d\TH:i'))
            ->call('saveQuiz')
            ->assertHasErrors(['endsAt']);
    }

    public function test_publishing_a_quiz_requires_at_least_one_question(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(QuizBuilder::class)
            ->call('openCreateModal')
            ->set('title', 'Empty Quiz Cannot Be Published')
            ->set('status', QuizStatus::Published->value)
            ->set('selectedQuestions', [])
            ->call('saveQuiz')
            ->assertHasErrors(['status']);

        $this->assertDatabaseMissing('quizzes', [
            'title' => 'Empty Quiz Cannot Be Published',
        ]);
    }

    public function test_teacher_can_attach_quiz_to_school_class(): void
    {
        $class = SchoolClass::factory()->create([
            'institution_id' => $this->institution->id,
            'title' => 'Grade 10 Biology',
        ]);

        Livewire::actingAs($this->teacher)
            ->test(QuizBuilder::class)
            ->call('openCreateModal')
            ->set('title', 'Class Biology Quiz')
            ->set('status', QuizStatus::Draft->value)
            ->set('attachmentType', 'class')
            ->set('selectedClassId', $class->id)
            ->call('saveQuiz')
            ->assertHasNoErrors();

        $quiz = Quiz::where('title', 'Class Biology Quiz')->firstOrFail();
        $this->assertSame($class->id, $quiz->class_id);
        $this->assertNull($quiz->activity_id);
        $this->assertNull($quiz->class_session_id);
        $this->assertFalse($quiz->isStandalone());
        $this->assertStringContainsString('Grade 10 Biology', $quiz->getAttachmentTargetLabel());
    }

    public function test_teacher_can_attach_quiz_to_activity(): void
    {
        $class = SchoolClass::factory()->create(['institution_id' => $this->institution->id]);
        $activity = Activity::create([
            'institution_id' => $this->institution->id,
            'class_id' => $class->id,
            'created_by' => $this->teacher->id,
            'title' => 'Fun with Shapes and Colors',
            'instructions' => 'Follow the prompt.',
        ]);

        Livewire::actingAs($this->teacher)
            ->test(QuizBuilder::class)
            ->call('openCreateModal')
            ->set('title', 'Activity Checkpoint Quiz')
            ->set('mode', QuizMode::Nursery->value)
            ->set('status', QuizStatus::Draft->value)
            ->set('attachmentType', 'activity')
            ->set('selectedActivityId', $activity->id)
            ->call('saveQuiz')
            ->assertHasNoErrors();

        $quiz = Quiz::where('title', 'Activity Checkpoint Quiz')->firstOrFail();
        $this->assertSame($activity->id, $quiz->activity_id);
        $this->assertTrue($quiz->isNursery());
        $this->assertStringContainsString('Fun with Shapes and Colors', $quiz->getAttachmentTargetLabel());
    }

    public function test_teacher_can_attach_quiz_to_class_session(): void
    {
        $class = SchoolClass::factory()->create(['institution_id' => $this->institution->id]);
        $session = ClassSession::create([
            'institution_id' => $this->institution->id,
            'class_id' => $class->id,
            'title' => 'Cell Division Lab Session',
            'date' => now()->toDateString(),
            'starts_at' => '09:00:00',
            'ends_at' => '10:30:00',
        ]);

        Livewire::actingAs($this->teacher)
            ->test(QuizBuilder::class)
            ->call('openCreateModal')
            ->set('title', 'In-Class Live Quiz')
            ->set('mode', QuizMode::Live->value)
            ->set('status', QuizStatus::Draft->value)
            ->set('attachmentType', 'session')
            ->set('selectedSessionId', $session->id)
            ->call('saveQuiz')
            ->assertHasNoErrors();

        $quiz = Quiz::where('title', 'In-Class Live Quiz')->firstOrFail();
        $this->assertSame($session->id, $quiz->class_session_id);
        $this->assertTrue($quiz->isLive());
        $this->assertStringContainsString('Cell Division Lab Session', $quiz->getAttachmentTargetLabel());
    }

    public function test_teacher_can_add_reorder_and_score_questions_from_question_bank(): void
    {
        $q1 = Question::factory()->create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
            'title' => 'Question 1: Plant Cell Structure',
            'default_marks' => 2.0,
            'default_penalty_marks' => 0.5,
        ]);

        $q2 = Question::factory()->create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
            'title' => 'Question 2: Animal Cell Mitochondria',
            'default_marks' => 3.0,
            'default_penalty_marks' => 0.0,
        ]);

        Livewire::actingAs($this->teacher)
            ->test(QuizBuilder::class)
            ->call('openCreateModal')
            ->set('title', 'Cytology Assessment')
            ->set('status', QuizStatus::Published->value)
            // Open question bank picker and select questions
            ->call('openQuestionBankPicker')
            ->set('pickerCheckedQuestionIds', [$q1->id, $q2->id])
            ->call('addSelectedQuestionsToQuiz')
            ->assertCount('selectedQuestions', 2)
            // Override Question 1 marks from 2.0 to 5.0
            ->set('selectedQuestions.0.marks', 5.0)
            // Swap order: move Question 2 up to position 1
            ->call('moveQuestionUp', 1)
            ->call('saveQuiz')
            ->assertHasNoErrors();

        $quiz = Quiz::where('title', 'Cytology Assessment')->with('quizQuestions.question')->firstOrFail();
        $this->assertCount(2, $quiz->quizQuestions);

        // Verify order position after swap
        $firstQuestionInQuiz = $quiz->quizQuestions->firstWhere('order_position', 1);
        $secondQuestionInQuiz = $quiz->quizQuestions->firstWhere('order_position', 2);

        $this->assertSame($q2->id, $firstQuestionInQuiz->question_id);
        $this->assertSame(3.0, (float) $firstQuestionInQuiz->marks);

        $this->assertSame($q1->id, $secondQuestionInQuiz->question_id);
        $this->assertSame(5.0, (float) $secondQuestionInQuiz->marks);

        // Verify total marks calculation: 3.0 + 5.0 = 8.0
        $this->assertSame(8.0, (float) $quiz->total_marks);
    }

    public function test_teacher_can_edit_existing_quiz(): void
    {
        $quiz = Quiz::factory()->create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
            'title' => 'Original Quiz Title',
            'time_limit_minutes' => 20,
        ]);

        $question = Question::factory()->create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
            'title' => 'Added Question',
            'default_marks' => 4.0,
        ]);

        Livewire::actingAs($this->teacher)
            ->test(QuizBuilder::class)
            ->call('editQuiz', $quiz->id)
            ->assertSet('title', 'Original Quiz Title')
            ->assertSet('timeLimitMinutes', 20)
            ->set('title', 'Updated Quiz Title')
            ->set('timeLimitMinutes', 60)
            ->call('openQuestionBankPicker')
            ->set('pickerCheckedQuestionIds', [$question->id])
            ->call('addSelectedQuestionsToQuiz')
            ->call('saveQuiz')
            ->assertHasNoErrors();

        $quiz->refresh();
        $this->assertSame('Updated Quiz Title', $quiz->title);
        $this->assertSame(60, $quiz->time_limit_minutes);
        $this->assertSame(4.0, (float) $quiz->total_marks);
    }

    public function test_teacher_can_duplicate_quiz(): void
    {
        $quiz = Quiz::factory()->create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
            'title' => 'Master Chemistry Quiz',
            'total_marks' => 10.0,
        ]);

        $q = Question::factory()->create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
        ]);

        $quiz->questions()->attach($q->id, [
            'order_position' => 1,
            'marks' => 10.0,
            'penalty_marks' => 1.0,
        ]);

        Livewire::actingAs($this->teacher)
            ->test(QuizBuilder::class)
            ->call('duplicateQuiz', $quiz->id);

        $copy = Quiz::where('title', 'Master Chemistry Quiz (Copy)')->with('quizQuestions')->firstOrFail();
        $this->assertSame(QuizStatus::Draft, $copy->status);
        $this->assertCount(1, $copy->quizQuestions);
        $this->assertSame(10.0, (float) $copy->total_marks);
    }

    public function test_teacher_can_toggle_publish_and_delete_quiz(): void
    {
        $quiz = Quiz::factory()->draft()->create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
            'title' => 'Quiz To Toggle And Delete',
        ]);

        $q = Question::factory()->create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
        ]);
        $quiz->questions()->attach($q->id, ['order_position' => 1, 'marks' => 2.0]);

        Livewire::actingAs($this->teacher)
            ->test(QuizBuilder::class)
            // Toggle Draft -> Published
            ->call('toggleStatus', $quiz->id);

        $quiz->refresh();
        $this->assertSame(QuizStatus::Published, $quiz->status);

        // Delete quiz
        Livewire::actingAs($this->teacher)
            ->test(QuizBuilder::class)
            ->call('confirmDelete', $quiz->id)
            ->assertSet('showDeleteModal', true)
            ->call('deleteQuiz')
            ->assertSet('showDeleteModal', false);

        $this->assertSoftDeleted('quizzes', ['id' => $quiz->id]);
    }

    public function test_multi_institution_isolation_prevents_viewing_or_editing_other_institution_quizzes(): void
    {
        $otherInstitution = Institution::factory()->create();
        $foreignQuiz = Quiz::factory()->create([
            'institution_id' => $otherInstitution->id,
            'title' => 'Foreign Institution Exam',
        ]);

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($this->teacher)
            ->test(QuizBuilder::class)
            ->call('editQuiz', $foreignQuiz->id);
    }
}
