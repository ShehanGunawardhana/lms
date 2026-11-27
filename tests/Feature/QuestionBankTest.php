<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use App\Enums\RoleName;
use App\Livewire\Teacher\QuestionBank;
use App\Models\Institution;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuestionBankTest extends TestCase
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
        ]);
        $this->student->assignRole(RoleName::Student->value);

        $this->parent = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $this->parent->assignRole(RoleName::Parent->value);

        session(['current_institution_id' => $this->institution->id]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('teacher.questions.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_student_and_parent_cannot_access_question_bank(): void
    {
        $this->actingAs($this->student)
            ->get(route('teacher.questions.index'))
            ->assertForbidden();

        $this->actingAs($this->parent)
            ->get(route('teacher.questions.index'))
            ->assertForbidden();
    }

    public function test_teacher_and_admin_can_access_question_bank(): void
    {
        $this->actingAs($this->teacher)
            ->get(route('teacher.questions.index'))
            ->assertOk()
            ->assertSee('Question Bank');

        $this->actingAs($this->admin)
            ->get(route('teacher.questions.index'))
            ->assertOk()
            ->assertSee('Question Bank');
    }

    public function test_teacher_can_create_single_choice_question_with_options_and_tags(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(QuestionBank::class)
            ->call('openCreateModal')
            ->set('type', QuestionType::SingleChoice->value)
            ->set('title', 'Photosynthesis Primary Organelle')
            ->set('question_text', 'Which organelle is primarily responsible for photosynthesis in plant cells?')
            ->set('difficulty', QuestionDifficulty::Easy->value)
            ->set('default_marks', 2.0)
            ->set('default_penalty_marks', 0.5)
            ->set('tagsInput', 'biology, plant-biology, cell-structure')
            ->set('options', [
                ['text' => 'Chloroplast', 'is_correct' => true],
                ['text' => 'Mitochondria', 'is_correct' => false],
                ['text' => 'Ribosome', 'is_correct' => false],
                ['text' => 'Golgi Apparatus', 'is_correct' => false],
            ])
            ->set('hint', 'It contains chlorophyll and has a green pigment.')
            ->set('explanation', 'Chloroplasts absorb sunlight and utilize it in conjunction with water and carbon dioxide gas to produce food.')
            ->call('saveQuestion')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $question = Question::where('title', 'Photosynthesis Primary Organelle')->first();
        $this->assertNotNull($question);
        $this->assertEquals(QuestionType::SingleChoice, $question->type);
        $this->assertEquals(QuestionDifficulty::Easy, $question->difficulty);
        $this->assertEquals(2.0, $question->default_marks);
        $this->assertEquals(0.5, $question->default_penalty_marks);
        $this->assertEquals($this->institution->id, $question->institution_id);
        $this->assertEquals($this->teacher->id, $question->created_by);

        // Verify options
        $this->assertCount(4, $question->options);
        $correct = $question->options->where('is_correct', true)->first();
        $this->assertNotNull($correct);
        $this->assertEquals('Chloroplast', $correct->option_text);

        // Verify tags
        $this->assertCount(3, $question->tags);
        $this->assertTrue($question->tags->contains('name', 'biology'));
        $this->assertTrue($question->tags->contains('name', 'plant-biology'));
    }

    public function test_single_choice_validation_rejects_missing_correct_or_empty_text(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(QuestionBank::class)
            ->call('openCreateModal')
            ->set('type', QuestionType::SingleChoice->value)
            ->set('title', 'Incomplete Question')
            ->set('question_text', 'Sample question?')
            ->set('options', [
                ['text' => 'Option A', 'is_correct' => false],
                ['text' => 'Option B', 'is_correct' => false],
            ])
            ->call('saveQuestion')
            ->assertHasErrors(['options']);
    }

    public function test_teacher_can_create_multiple_choice_question(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(QuestionBank::class)
            ->call('openCreateModal')
            ->set('type', QuestionType::MultipleChoice->value)
            ->set('title', 'Select Prime Numbers')
            ->set('question_text', 'Which of the following numbers are prime?')
            ->set('options', [
                ['text' => '2', 'is_correct' => true],
                ['text' => '3', 'is_correct' => true],
                ['text' => '4', 'is_correct' => false],
                ['text' => '5', 'is_correct' => true],
            ])
            ->call('saveQuestion')
            ->assertHasNoErrors();

        $question = Question::where('title', 'Select Prime Numbers')->first();
        $this->assertNotNull($question);
        $this->assertEquals(QuestionType::MultipleChoice, $question->type);
        $this->assertEquals(3, $question->options->where('is_correct', true)->count());
    }

    public function test_teacher_can_create_true_false_question(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(QuestionBank::class)
            ->call('openCreateModal')
            ->set('type', QuestionType::TrueFalse->value)
            ->set('title', 'Earth Orbit Shape')
            ->set('question_text', 'The Earth orbits the sun in a perfectly circular path.')
            ->call('setTrueFalseCorrect', false)
            ->call('saveQuestion')
            ->assertHasNoErrors();

        $question = Question::where('title', 'Earth Orbit Shape')->first();
        $this->assertNotNull($question);
        $this->assertEquals(QuestionType::TrueFalse, $question->type);

        $falseOption = $question->options->where('option_text', 'False')->first();
        $this->assertNotNull($falseOption);
        $this->assertTrue($falseOption->is_correct);
    }

    public function test_teacher_can_create_matching_pairs_question(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(QuestionBank::class)
            ->call('openCreateModal')
            ->set('type', QuestionType::Matching->value)
            ->set('title', 'Match Countries to Capitals')
            ->set('question_text', 'Match each country on the left with its capital city on the right:')
            ->set('options', [
                ['left' => 'Sri Lanka', 'right' => 'Sri Jayawardenepura Kotte'],
                ['left' => 'France', 'right' => 'Paris'],
                ['left' => 'Japan', 'right' => 'Tokyo'],
            ])
            ->call('saveQuestion')
            ->assertHasNoErrors();

        $question = Question::where('title', 'Match Countries to Capitals')->first();
        $this->assertNotNull($question);
        $this->assertEquals(QuestionType::Matching, $question->type);
        $this->assertCount(3, $question->options);

        $pair = $question->options->first();
        $this->assertEquals('Sri Lanka', $pair->option_text);
        $this->assertEquals('Sri Jayawardenepura Kotte', $pair->match_text);
    }

    public function test_teacher_can_create_picture_choice_question(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(QuestionBank::class)
            ->call('openCreateModal')
            ->set('type', QuestionType::PictureChoice->value)
            ->set('title', 'Identify the Herbivore')
            ->set('question_text', 'Select the animal that is an herbivore:')
            ->set('options', [
                ['text' => 'Cow', 'image_path' => 'animals/cow.jpg', 'is_correct' => true],
                ['text' => 'Lion', 'image_path' => 'animals/lion.jpg', 'is_correct' => false],
                ['text' => 'Eagle', 'image_path' => 'animals/eagle.jpg', 'is_correct' => false],
            ])
            ->call('saveQuestion')
            ->assertHasNoErrors();

        $question = Question::where('title', 'Identify the Herbivore')->first();
        $this->assertNotNull($question);
        $this->assertEquals(QuestionType::PictureChoice, $question->type);
        $this->assertTrue($question->options->where('option_text', 'Cow')->first()->is_correct);
    }

    public function test_teacher_can_create_fill_in_the_blank_question(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(QuestionBank::class)
            ->call('openCreateModal')
            ->set('type', QuestionType::FillInTheBlank->value)
            ->set('title', 'Cell Energy Unit')
            ->set('question_text', 'The cellular energy currency molecule is known as [blank].')
            ->set('caseSensitive', false)
            ->set('options', [
                ['acceptable_answers' => 'ATP, adenosine triphosphate, Adenosine Triphosphate', 'feedback' => 'Standard energy currency'],
            ])
            ->call('saveQuestion')
            ->assertHasNoErrors();

        $question = Question::where('title', 'Cell Energy Unit')->first();
        $this->assertNotNull($question);
        $this->assertEquals(QuestionType::FillInTheBlank, $question->type);

        $option = $question->options->first();
        $this->assertNotNull($option);
        $this->assertContains('ATP', $option->acceptable_answers);
        $this->assertContains('adenosine triphosphate', $option->acceptable_answers);
    }

    public function test_teacher_can_create_ordering_question(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(QuestionBank::class)
            ->call('openCreateModal')
            ->set('type', QuestionType::Ordering->value)
            ->set('title', 'Scientific Method Order')
            ->set('question_text', 'Arrange the steps of the scientific method in chronological order:')
            ->set('options', [
                ['text' => 'Observation', 'order_position' => 1],
                ['text' => 'Hypothesis formulation', 'order_position' => 2],
                ['text' => 'Experimentation', 'order_position' => 3],
                ['text' => 'Conclusion', 'order_position' => 4],
            ])
            ->call('saveQuestion')
            ->assertHasNoErrors();

        $question = Question::where('title', 'Scientific Method Order')->first();
        $this->assertNotNull($question);
        $this->assertEquals(QuestionType::Ordering, $question->type);
        $this->assertCount(4, $question->options);
        $this->assertEquals(1, $question->options[0]->order_position);
        $this->assertEquals('Observation', $question->options[0]->option_text);
        $this->assertEquals(4, $question->options[3]->order_position);
    }

    public function test_teacher_can_create_short_answer_question(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(QuestionBank::class)
            ->call('openCreateModal')
            ->set('type', QuestionType::ShortAnswer->value)
            ->set('title', 'Newton Second Law Explanation')
            ->set('question_text', 'Explain Newton’s second law of motion and its real-world application.')
            ->set('options', [
                [
                    'sample_answer' => 'Force equals mass times acceleration (F = ma). As force increases, acceleration increases proportionally.',
                    'feedback' => 'Give 1 mark for formula, 1 mark for relationship, 1 mark for practical example.',
                ],
            ])
            ->call('saveQuestion')
            ->assertHasNoErrors();

        $question = Question::where('title', 'Newton Second Law Explanation')->first();
        $this->assertNotNull($question);
        $this->assertEquals(QuestionType::ShortAnswer, $question->type);
        $this->assertFalse($question->type->isAutoMarked());
        $this->assertStringContainsString('F = ma', $question->options->first()->option_text);
    }

    public function test_teacher_can_edit_existing_question(): void
    {
        $question = Question::factory()->create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
            'title' => 'Original Title',
            'type' => QuestionType::SingleChoice,
        ]);
        QuestionOption::factory()->create([
            'question_id' => $question->id,
            'option_text' => 'Option 1',
            'is_correct' => true,
        ]);
        QuestionOption::factory()->count(2)->create([
            'question_id' => $question->id,
            'option_text' => 'Option Wrong',
            'is_correct' => false,
        ]);

        Livewire::actingAs($this->teacher)
            ->test(QuestionBank::class)
            ->call('editQuestion', $question->id)
            ->assertSet('title', 'Original Title')
            ->set('title', 'Updated Question Title')
            ->set('default_marks', 5.0)
            ->call('saveQuestion')
            ->assertHasNoErrors();

        $question->refresh();
        $this->assertEquals('Updated Question Title', $question->title);
        $this->assertEquals(5.0, $question->default_marks);
    }

    public function test_teacher_can_duplicate_question(): void
    {
        $question = Question::factory()->create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
            'title' => 'Calculus Derivative Rules',
            'type' => QuestionType::SingleChoice,
        ]);

        QuestionOption::factory()->create([
            'question_id' => $question->id,
            'option_text' => 'Power rule',
            'is_correct' => true,
        ]);
        QuestionOption::factory()->create([
            'question_id' => $question->id,
            'option_text' => 'Product rule',
            'is_correct' => false,
        ]);

        $tag = Tag::factory()->create(['institution_id' => $this->institution->id, 'name' => 'Math']);
        $question->tags()->attach($tag);

        Livewire::actingAs($this->teacher)
            ->test(QuestionBank::class)
            ->call('duplicateQuestion', $question->id);

        $cloned = Question::where('title', 'Calculus Derivative Rules (Copy)')->first();
        $this->assertNotNull($cloned);
        $this->assertCount(2, $cloned->options);
        $this->assertCount(1, $cloned->tags);
        $this->assertEquals('Power rule', $cloned->options->first()->option_text);
    }

    public function test_teacher_can_toggle_active_and_delete_question(): void
    {
        $question = Question::factory()->create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
            'is_active' => true,
        ]);

        Livewire::actingAs($this->teacher)
            ->test(QuestionBank::class)
            ->call('toggleActive', $question->id);

        $question->refresh();
        $this->assertFalse($question->is_active);

        Livewire::actingAs($this->teacher)
            ->test(QuestionBank::class)
            ->call('confirmDelete', $question->id)
            ->assertSet('showDeleteModal', true)
            ->call('deleteQuestion')
            ->assertSet('showDeleteModal', false);

        $this->assertSoftDeleted('questions', ['id' => $question->id]);
    }

    public function test_search_and_filters_work_on_question_bank(): void
    {
        $q1 = Question::factory()->create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
            'title' => 'Quantum Mechanics Fundamentals',
            'type' => QuestionType::SingleChoice,
            'difficulty' => QuestionDifficulty::Hard,
        ]);

        $q2 = Question::factory()->create([
            'institution_id' => $this->institution->id,
            'created_by' => $this->teacher->id,
            'title' => 'Basic English Grammar',
            'type' => QuestionType::FillInTheBlank,
            'difficulty' => QuestionDifficulty::Easy,
        ]);

        $tagPhysics = Tag::factory()->create(['institution_id' => $this->institution->id, 'name' => 'Physics']);
        $q1->tags()->attach($tagPhysics);

        Livewire::actingAs($this->teacher)
            ->test(QuestionBank::class)
            ->set('search', 'Quantum')
            ->assertSee('Quantum Mechanics Fundamentals')
            ->assertDontSee('Basic English Grammar')
            ->set('search', '')
            ->set('typeFilter', QuestionType::FillInTheBlank->value)
            ->assertSee('Basic English Grammar')
            ->assertDontSee('Quantum Mechanics Fundamentals')
            ->set('typeFilter', 'all')
            ->set('tagFilter', (string) $tagPhysics->id)
            ->assertSee('Quantum Mechanics Fundamentals')
            ->assertDontSee('Basic English Grammar');
    }

    public function test_multi_institution_isolation_prevents_viewing_or_editing_another_institution_questions(): void
    {
        $otherInstitution = Institution::factory()->create();
        $otherTeacher = User::factory()->create(['institution_id' => $otherInstitution->id]);
        $otherTeacher->assignRole(RoleName::Teacher->value);

        $foreignQuestion = Question::factory()->create([
            'institution_id' => $otherInstitution->id,
            'created_by' => $otherTeacher->id,
            'title' => 'Foreign Confidential Question',
        ]);

        Livewire::actingAs($this->teacher)
            ->test(QuestionBank::class)
            ->assertDontSee('Foreign Confidential Question');

        $this->expectException(ModelNotFoundException::class);
        Livewire::actingAs($this->teacher)
            ->test(QuestionBank::class)
            ->call('editQuestion', $foreignQuestion->id);
    }
}
