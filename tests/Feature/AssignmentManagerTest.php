<?php

namespace Tests\Feature;

use App\Enums\AssignmentStatus;
use App\Enums\ClassKind;
use App\Enums\RoleName;
use App\Livewire\Teacher\AssignmentManager;
use App\Models\Assignment;
use App\Models\AssignmentAttachment;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AssignmentManagerTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected Institution $otherInstitution;

    protected User $teacher;

    protected User $otherTeacher;

    protected User $admin;

    protected User $student;

    protected SchoolClass $class;

    protected SchoolClass $otherClass;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->institution = Institution::create([
            'name' => 'Apex High Academy',
            'slug' => 'apex-high',
            'code' => 'AHA',
            'type' => 'institute',
            'is_active' => true,
        ]);

        $this->otherInstitution = Institution::create([
            'name' => 'Metro College',
            'slug' => 'metro-college',
            'code' => 'MTC',
            'type' => 'institute',
            'is_active' => true,
        ]);

        session(['current_institution_id' => $this->institution->id]);

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->teacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Professor Alan',
            'status' => 'active',
        ]);
        $this->teacher->assignRole(RoleName::Teacher->value);

        $this->otherTeacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Professor Brenda',
            'status' => 'active',
        ]);
        $this->otherTeacher->assignRole(RoleName::Teacher->value);

        $this->admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Admin Claire',
            'status' => 'active',
            'two_factor_secret' => 'secret123',
            'two_factor_confirmed_at' => now(),
        ]);
        $this->admin->assignRole(RoleName::Admin->value);

        $this->student = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Student Dan',
            'status' => 'active',
        ]);
        $this->student->assignRole(RoleName::Student->value);

        $this->class = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Grade 10 - Advanced Physics',
            'kind' => ClassKind::InstituteClass,
            'fee_amount' => 5000.00,
            'capacity' => 30,
            'status' => 'active',
        ]);

        $this->otherClass = SchoolClass::create([
            'institution_id' => $this->otherInstitution->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Cross-Institution Chemistry',
            'kind' => ClassKind::InstituteClass,
            'fee_amount' => 6000.00,
            'capacity' => 25,
            'status' => 'active',
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('teacher.assignments'))
            ->assertRedirect(route('login'));
    }

    public function test_student_cannot_access_teacher_assignment_manager(): void
    {
        $this->actingAs($this->student)
            ->get(route('teacher.assignments'))
            ->assertForbidden();
    }

    public function test_teacher_and_admin_can_access_assignment_manager(): void
    {
        $this->actingAs($this->teacher)
            ->get(route('teacher.assignments'))
            ->assertOk()
            ->assertSee(__('Assignments & Coursework Manager'))
            ->assertSee(__('New Assignment'));

        $this->actingAs($this->admin)
            ->get(route('teacher.assignments'))
            ->assertOk()
            ->assertSee(__('Assignments & Coursework Manager'));
    }

    public function test_teacher_can_create_draft_assignment_with_allowed_file_types(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(AssignmentManager::class)
            ->call('openCreateModal')
            ->set('classId', $this->class->id)
            ->set('title', 'Mechanics Lab Report')
            ->set('description', 'Submit comprehensive report on projectile motion.')
            ->set('instructions', 'Include all calculations, error margins, and diagrams.')
            ->set('status', AssignmentStatus::Draft->value)
            ->set('allowedFileTypes', ['pdf', 'docx'])
            ->set('maxFileSizeMb', 25)
            ->set('deadline', now()->addDays(7)->format('Y-m-d\TH:i'))
            ->set('allowLate', true)
            ->set('resubmissionLimit', 2)
            ->set('totalMarks', 100)
            ->set('passMarks', 60)
            ->call('saveAssignment')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('assignments', [
            'institution_id' => $this->institution->id,
            'class_id' => $this->class->id,
            'created_by' => $this->teacher->id,
            'title' => 'Mechanics Lab Report',
            'status' => AssignmentStatus::Draft->value,
            'max_file_size_mb' => 25,
            'allow_late' => true,
            'resubmission_limit' => 2,
            'total_marks' => 100,
            'pass_marks' => 60,
        ]);

        $assignment = Assignment::where('title', 'Mechanics Lab Report')->firstOrFail();
        $this->assertEquals(['pdf', 'docx'], $assignment->allowed_file_types);
        $this->assertTrue($assignment->isFileTypeAllowed('pdf'));
        $this->assertTrue($assignment->isFileTypeAllowed('docx'));
        $this->assertFalse($assignment->isFileTypeAllowed('zip'));
    }

    public function test_teacher_can_create_assignment_with_web_link_attachment(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(AssignmentManager::class)
            ->call('openCreateModal')
            ->set('classId', $this->class->id)
            ->set('title', 'Thermodynamics Research')
            ->set('deadline', now()->addDays(5)->format('Y-m-d\TH:i'))
            ->set('linkTitle', 'MIT OpenCourseWare Lecture Notes')
            ->set('linkUrl', 'https://ocw.mit.edu/courses/physics')
            ->call('addLinkAttachment')
            ->assertHasNoErrors()
            ->call('saveAssignment')
            ->assertHasNoErrors();

        $assignment = Assignment::where('title', 'Thermodynamics Research')->firstOrFail();

        $this->assertDatabaseHas('assignment_attachments', [
            'assignment_id' => $assignment->id,
            'type' => 'link',
            'title' => 'MIT OpenCourseWare Lecture Notes',
            'url' => 'https://ocw.mit.edu/courses/physics',
        ]);
    }

    public function test_teacher_can_create_assignment_with_file_attachment(): void
    {
        $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";
        $file = UploadedFile::fake()->createWithContent('rubric_guidelines.pdf', $pdfContent);

        Livewire::actingAs($this->teacher)
            ->test(AssignmentManager::class)
            ->call('openCreateModal')
            ->set('classId', $this->class->id)
            ->set('title', 'Optics & Waveforms Lab')
            ->set('deadline', now()->addDays(10)->format('Y-m-d\TH:i'))
            ->set('attachmentTitle', 'Official Lab Rubric')
            ->set('attachmentFile', $file)
            ->call('uploadFileAttachment')
            ->assertHasNoErrors()
            ->call('saveAssignment')
            ->assertHasNoErrors();

        $assignment = Assignment::where('title', 'Optics & Waveforms Lab')->firstOrFail();

        $this->assertDatabaseHas('assignment_attachments', [
            'assignment_id' => $assignment->id,
            'type' => 'file',
            'title' => 'Official Lab Rubric',
        ]);

        $attachment = AssignmentAttachment::where('assignment_id', $assignment->id)->firstOrFail();
        $this->assertNotNull($attachment->file_asset_id);
        $this->assertTrue($attachment->isFile());
        $this->assertFalse($attachment->isLink());
    }

    public function test_validation_rejects_max_file_size_exceeding_50mb(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(AssignmentManager::class)
            ->call('openCreateModal')
            ->set('classId', $this->class->id)
            ->set('title', 'Massive Project')
            ->set('deadline', now()->addDays(7)->format('Y-m-d\TH:i'))
            ->set('maxFileSizeMb', 55) // over 50MB
            ->call('saveAssignment')
            ->assertHasErrors(['maxFileSizeMb']);
    }

    public function test_validation_rejects_pass_marks_greater_than_total_marks(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(AssignmentManager::class)
            ->call('openCreateModal')
            ->set('classId', $this->class->id)
            ->set('title', 'Quantum Mechanics Essay')
            ->set('deadline', now()->addDays(7)->format('Y-m-d\TH:i'))
            ->set('totalMarks', 50)
            ->set('passMarks', 60) // passMarks > totalMarks
            ->call('saveAssignment')
            ->assertHasErrors(['passMarks']);
    }

    public function test_teacher_can_edit_existing_assignment(): void
    {
        $assignment = Assignment::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->class->id,
            'created_by' => $this->teacher->id,
            'title' => 'Original Title',
            'description' => 'Original description',
            'status' => AssignmentStatus::Draft->value,
            'allowed_file_types' => ['pdf'],
            'max_file_size_mb' => 20,
            'deadline' => now()->addDays(3),
            'allow_late' => false,
            'resubmission_limit' => 1,
            'total_marks' => 50,
            'pass_marks' => 25,
        ]);

        Livewire::actingAs($this->teacher)
            ->test(AssignmentManager::class)
            ->call('openEditModal', $assignment->id)
            ->assertSet('title', 'Original Title')
            ->set('title', 'Updated Electrostatics Project')
            ->set('allowedFileTypes', ['pdf', 'zip', 'docx'])
            ->set('allowLate', true)
            ->set('totalMarks', 80)
            ->call('saveAssignment')
            ->assertHasNoErrors();

        $assignment->refresh();
        $this->assertEquals('Updated Electrostatics Project', $assignment->title);
        $this->assertEquals(['pdf', 'zip', 'docx'], $assignment->allowed_file_types);
        $this->assertTrue($assignment->allow_late);
        $this->assertEquals(80, $assignment->total_marks);
    }

    public function test_teacher_can_publish_close_and_reopen_assignment(): void
    {
        $assignment = Assignment::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->class->id,
            'created_by' => $this->teacher->id,
            'title' => 'Astronomy Term Paper',
            'status' => AssignmentStatus::Draft->value,
            'allowed_file_types' => ['pdf'],
            'max_file_size_mb' => 10,
            'deadline' => now()->addDays(14),
            'allow_late' => false,
            'resubmission_limit' => 1,
            'total_marks' => 100,
        ]);

        // Publish
        Livewire::actingAs($this->teacher)
            ->test(AssignmentManager::class)
            ->call('publishAssignment', $assignment->id);

        $assignment->refresh();
        $this->assertEquals(AssignmentStatus::Published, $assignment->status);

        // Close
        Livewire::actingAs($this->teacher)
            ->test(AssignmentManager::class)
            ->call('closeAssignment', $assignment->id);

        $assignment->refresh();
        $this->assertEquals(AssignmentStatus::Closed, $assignment->status);

        // Reopen
        Livewire::actingAs($this->teacher)
            ->test(AssignmentManager::class)
            ->call('reopenAssignment', $assignment->id);

        $assignment->refresh();
        $this->assertEquals(AssignmentStatus::Published, $assignment->status);
    }

    public function test_teacher_can_soft_delete_assignment(): void
    {
        $assignment = Assignment::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->class->id,
            'created_by' => $this->teacher->id,
            'title' => 'To Be Deleted Assignment',
            'status' => AssignmentStatus::Draft->value,
            'allowed_file_types' => ['pdf'],
            'max_file_size_mb' => 10,
            'deadline' => now()->addDays(5),
            'allow_late' => false,
            'resubmission_limit' => 0,
            'total_marks' => 10,
        ]);

        Livewire::actingAs($this->teacher)
            ->test(AssignmentManager::class)
            ->call('deleteAssignment', $assignment->id);

        $this->assertSoftDeleted('assignments', [
            'id' => $assignment->id,
        ]);
    }

    public function test_teacher_cannot_manage_assignment_from_another_institution(): void
    {
        $foreignAssignment = Assignment::create([
            'institution_id' => $this->otherInstitution->id,
            'class_id' => $this->otherClass->id,
            'created_by' => $this->teacher->id,
            'title' => 'Foreign Assignment',
            'status' => AssignmentStatus::Draft->value,
            'allowed_file_types' => ['pdf'],
            'max_file_size_mb' => 10,
            'deadline' => now()->addDays(5),
            'allow_late' => false,
            'resubmission_limit' => 0,
            'total_marks' => 10,
        ]);

        Livewire::actingAs($this->teacher)
            ->test(AssignmentManager::class)
            ->assertDontSee('Foreign Assignment');

        // Attempting to edit foreign assignment will throw ModelNotFoundException due to institution scoping
        $this->expectException(ModelNotFoundException::class);
        Livewire::actingAs($this->teacher)
            ->test(AssignmentManager::class)
            ->call('openEditModal', $foreignAssignment->id);
    }

    public function test_search_and_status_filtering_works(): void
    {
        Assignment::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->class->id,
            'created_by' => $this->teacher->id,
            'title' => 'Nuclear Physics Quiz Paper',
            'status' => AssignmentStatus::Published->value,
            'allowed_file_types' => ['pdf'],
            'max_file_size_mb' => 10,
            'deadline' => now()->addDays(2),
            'allow_late' => false,
            'resubmission_limit' => 1,
            'total_marks' => 50,
        ]);

        Assignment::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->class->id,
            'created_by' => $this->teacher->id,
            'title' => 'Biology Dissection Lab',
            'status' => AssignmentStatus::Draft->value,
            'allowed_file_types' => ['pdf'],
            'max_file_size_mb' => 10,
            'deadline' => now()->addDays(6),
            'allow_late' => false,
            'resubmission_limit' => 1,
            'total_marks' => 50,
        ]);

        Livewire::actingAs($this->teacher)
            ->test(AssignmentManager::class)
            ->set('search', 'Nuclear')
            ->assertSee('Nuclear Physics Quiz Paper')
            ->assertDontSee('Biology Dissection Lab')
            ->set('search', '')
            ->set('statusFilter', 'draft')
            ->assertSee('Biology Dissection Lab')
            ->assertDontSee('Nuclear Physics Quiz Paper');
    }

    public function test_assignments_index_redirects_appropriately(): void
    {
        $this->actingAs($this->teacher)
            ->get(route('assignments.index'))
            ->assertRedirect(route('teacher.assignments'));

        $this->actingAs($this->student)
            ->get(route('assignments.index'))
            ->assertRedirect(route('student.dashboard'));
    }
}
