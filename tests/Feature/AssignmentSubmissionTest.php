<?php

namespace Tests\Feature;

use App\Enums\AssignmentStatus;
use App\Enums\AssignmentSubmissionStatus;
use App\Enums\ClassKind;
use App\Enums\InstitutionType;
use App\Enums\RoleName;
use App\Livewire\Student\AssignmentShow;
use App\Models\Assignment;
use App\Models\AssignmentAttachment;
use App\Models\AssignmentSubmission;
use App\Models\Guardian;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity as AuditActivity;
use Tests\TestCase;

class AssignmentSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected Institution $otherInstitution;

    protected User $teacher;

    protected User $studentUser;

    protected User $unenrolledStudentUser;

    protected User $parentUser;

    protected Student $student;

    protected Student $otherStudent;

    protected Guardian $guardian;

    protected SchoolClass $class;

    protected Assignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');
        Storage::fake('public');

        $this->institution = Institution::create([
            'name' => 'Horizon Science Academy',
            'slug' => 'horizon-science',
            'code' => 'HSA',
            'type' => InstitutionType::Institute->value,
            'is_active' => true,
        ]);

        $this->otherInstitution = Institution::create([
            'name' => 'Metro College',
            'slug' => 'metro-college',
            'code' => 'MTC',
            'type' => InstitutionType::Institute->value,
            'is_active' => true,
        ]);

        session(['current_institution_id' => $this->institution->id]);

        $this->seed(RolesAndPermissionsSeeder::class);

        // Teacher
        $this->teacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Dr. Robert Oppenheimer',
            'status' => 'active',
        ]);
        $this->teacher->assignRole(RoleName::Teacher->value);

        // Enrolled Student User & Student
        $this->studentUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Albert Einstein',
            'status' => 'active',
        ]);
        $this->studentUser->assignRole(RoleName::Student->value);

        $this->student = Student::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->studentUser->id,
            'student_code' => 'INS-26-0001',
            'first_name' => 'Albert',
            'last_name' => 'Einstein',
            'dob' => now()->subYears(16),
            'status' => 'active',
            'kind' => 'institute',
        ]);

        // Unenrolled Student User & Student
        $this->unenrolledStudentUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Isaac Newton',
            'status' => 'active',
        ]);
        $this->unenrolledStudentUser->assignRole(RoleName::Student->value);

        $this->otherStudent = Student::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->unenrolledStudentUser->id,
            'student_code' => 'INS-26-0002',
            'first_name' => 'Isaac',
            'last_name' => 'Newton',
            'dob' => now()->subYears(17),
            'status' => 'active',
            'kind' => 'institute',
        ]);

        // Parent & Guardian
        $this->parentUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Pauline Einstein',
            'status' => 'active',
        ]);
        $this->parentUser->assignRole(RoleName::Parent->value);

        $this->guardian = Guardian::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->parentUser->id,
            'relationship' => 'mother',
        ]);

        $this->guardian->students()->attach($this->student->id, [
            'institution_id' => $this->institution->id,
            'relationship' => 'mother',
            'is_primary' => true,
        ]);

        // Class
        $this->class = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Advanced Theoretical Physics',
            'kind' => ClassKind::InstituteClass,
            'fee_amount' => 8000.00,
            'capacity' => 25,
            'status' => 'active',
        ]);

        // Enroll Albert in Class
        $this->student->classes()->attach($this->class->id, [
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'enrolled_at' => now()->subMonth(),
        ]);

        // Assignment
        $this->assignment = Assignment::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->class->id,
            'created_by' => $this->teacher->id,
            'title' => 'General Relativity Field Equations',
            'description' => 'Derive the Einstein field equations and apply to Schwarzschild metric.',
            'instructions' => "1. State geodesic equation.\n2. Calculate Ricci curvature tensor.\n3. Show energy-momentum conservation.",
            'status' => AssignmentStatus::Published->value,
            'allowed_file_types' => ['pdf', 'docx'],
            'max_file_size_mb' => 20,
            'deadline' => now()->addDays(5),
            'allow_late' => true,
            'resubmission_limit' => 3,
            'total_marks' => 100,
            'pass_marks' => 50,
        ]);

        AssignmentAttachment::create([
            'assignment_id' => $this->assignment->id,
            'type' => 'link',
            'title' => 'Annalen der Physik Reference',
            'url' => 'https://example.org/relativity.pdf',
            'sort_order' => 0,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('assignments.show', $this->assignment))
            ->assertRedirect(route('login'));
    }

    public function test_unenrolled_student_is_forbidden(): void
    {
        $this->actingAs($this->unenrolledStudentUser)
            ->get(route('assignments.show', $this->assignment))
            ->assertForbidden();
    }

    public function test_enrolled_student_can_view_assignment_details(): void
    {
        $this->actingAs($this->studentUser)
            ->get(route('assignments.show', $this->assignment))
            ->assertOk()
            ->assertSee('General Relativity Field Equations')
            ->assertSee('Annalen der Physik Reference')
            ->assertSee('PDF, DOCX')
            ->assertSee('20 MB')
            ->assertSee(__('Submit Coursework'));
    }

    public function test_student_can_upload_initial_submission_on_time(): void
    {
        $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";
        $file = UploadedFile::fake()->createWithContent('relativity_derivation.pdf', $pdfContent);

        Livewire::actingAs($this->studentUser)
            ->test(AssignmentShow::class, ['assignment' => $this->assignment])
            ->set('submissionFile', $file)
            ->set('submissionNote', 'Included full tensor derivations.')
            ->call('submitWork')
            ->assertHasNoErrors()
            ->assertSet('submissionError', '');

        $submission = $this->assignment->submissionForStudent($this->student->id);
        $this->assertNotNull($submission);
        $this->assertEquals(AssignmentSubmissionStatus::Submitted, $submission->status);
        $this->assertEquals(1, $submission->version);
        $this->assertFalse($submission->is_late);
        $this->assertEquals('Included full tensor derivations.', $submission->note);
        $this->assertEquals($this->studentUser->id, $submission->submitted_by);

        $this->assertEquals(AssignmentSubmissionStatus::Submitted, $this->assignment->statusForStudent($this->student->id));

        $this->assertTrue(
            AuditActivity::where('event', 'assignment.submitted')
                ->where('subject_id', $submission->id)
                ->exists()
        );
    }

    public function test_file_type_verification_strictly_enforces_per_assignment_whitelist(): void
    {
        // Assignment allows ONLY pdf, docx
        // Uploading a PNG image should be rejected by magic bytes/extension check
        $file = UploadedFile::fake()->image('picture.png', 100, 100);

        $test = Livewire::actingAs($this->studentUser)
            ->test(AssignmentShow::class, ['assignment' => $this->assignment])
            ->set('submissionFile', $file)
            ->call('submitWork');

        $this->assertStringContainsString('only accepts: pdf, docx', (string) $test->get('submissionError'));

        $this->assertNull($this->assignment->submissionForStudent($this->student->id));
    }

    public function test_file_size_exceeding_max_size_is_rejected(): void
    {
        // max_file_size_mb is 20MB. Attempting 25MB (25600 KB) should fail Livewire validation.
        $file = UploadedFile::fake()->create('huge_file.pdf', 25600);

        Livewire::actingAs($this->studentUser)
            ->test(AssignmentShow::class, ['assignment' => $this->assignment])
            ->set('submissionFile', $file)
            ->call('submitWork')
            ->assertHasErrors(['submissionFile']);
    }

    public function test_student_can_submit_late_when_allow_late_is_true(): void
    {
        $this->assignment->update([
            'deadline' => now()->subHours(2),
            'allow_late' => true,
        ]);

        $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";
        $file = UploadedFile::fake()->createWithContent('late_relativity.pdf', $pdfContent);

        Livewire::actingAs($this->studentUser)
            ->test(AssignmentShow::class, ['assignment' => $this->assignment])
            ->set('submissionFile', $file)
            ->call('submitWork')
            ->assertHasNoErrors()
            ->assertSet('submissionError', '');

        $submission = $this->assignment->submissionForStudent($this->student->id);
        $this->assertNotNull($submission);
        $this->assertEquals(AssignmentSubmissionStatus::Late, $submission->status);
        $this->assertTrue($submission->is_late);
        $this->assertEquals(1, $submission->version);
    }

    public function test_student_cannot_submit_past_deadline_when_allow_late_is_false(): void
    {
        $this->assignment->update([
            'deadline' => now()->subHours(2),
            'allow_late' => false,
        ]);

        $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";
        $file = UploadedFile::fake()->createWithContent('late_relativity.pdf', $pdfContent);

        Livewire::actingAs($this->studentUser)
            ->test(AssignmentShow::class, ['assignment' => $this->assignment])
            ->assertSee(__('Submissions are currently closed for this assignment.'))
            ->set('submissionFile', $file)
            ->call('submitWork')
            ->assertForbidden();

        $this->assertNull($this->assignment->submissionForStudent($this->student->id));
        $this->assertEquals(AssignmentSubmissionStatus::NotSubmitted, $this->assignment->statusForStudent($this->student->id));
    }

    public function test_student_can_replace_submission_creating_new_version(): void
    {
        $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";
        $v1File = UploadedFile::fake()->createWithContent('v1.pdf', $pdfContent);

        Livewire::actingAs($this->studentUser)
            ->test(AssignmentShow::class, ['assignment' => $this->assignment])
            ->set('submissionFile', $v1File)
            ->set('submissionNote', 'Version 1')
            ->call('submitWork');

        $v1Submission = $this->assignment->submissionForStudent($this->student->id);
        $this->assertEquals(1, $v1Submission->version);

        // Replace with Version 2
        $v2File = UploadedFile::fake()->createWithContent('v2_refined.pdf', $pdfContent);

        Livewire::actingAs($this->studentUser)
            ->test(AssignmentShow::class, ['assignment' => $this->assignment])
            ->call('openReplaceModal')
            ->assertSet('showReplaceModal', true)
            ->set('replacementFile', $v2File)
            ->set('replacementNote', 'Version 2 with corrected Bianchi identity')
            ->call('replaceWork')
            ->assertHasNoErrors()
            ->assertSet('showReplaceModal', false);

        $current = $this->assignment->submissionForStudent($this->student->id);
        $this->assertNotNull($current);
        $this->assertEquals(2, $current->version);
        $this->assertEquals('Version 2 with corrected Bianchi identity', $current->note);

        // Historical all submissions count
        $all = $this->assignment->allSubmissionsForStudent($this->student->id);
        $this->assertCount(2, $all);

        $this->assertTrue(
            AuditActivity::where('event', 'assignment.submission_replaced')
                ->where('subject_id', $current->id)
                ->exists()
        );
    }

    public function test_student_cannot_exceed_resubmission_limit(): void
    {
        $this->assignment->update([
            'resubmission_limit' => 1, // Only 1 attempt allowed
        ]);

        $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";
        $v1File = UploadedFile::fake()->createWithContent('v1.pdf', $pdfContent);

        Livewire::actingAs($this->studentUser)
            ->test(AssignmentShow::class, ['assignment' => $this->assignment])
            ->set('submissionFile', $v1File)
            ->call('submitWork');

        // Attempting to replace should be forbidden because resubmission limit is reached
        Livewire::actingAs($this->studentUser)
            ->test(AssignmentShow::class, ['assignment' => $this->assignment])
            ->call('openReplaceModal')
            ->assertForbidden();

        $this->assertEquals(1, $this->assignment->submissionForStudent($this->student->id)->version);
    }

    public function test_student_can_edit_note_on_unlocked_submission(): void
    {
        $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";
        $file = UploadedFile::fake()->createWithContent('v1.pdf', $pdfContent);

        Livewire::actingAs($this->studentUser)
            ->test(AssignmentShow::class, ['assignment' => $this->assignment])
            ->set('submissionFile', $file)
            ->set('submissionNote', 'Initial note')
            ->call('submitWork');

        $submission = $this->assignment->submissionForStudent($this->student->id);

        Livewire::actingAs($this->studentUser)
            ->test(AssignmentShow::class, ['assignment' => $this->assignment])
            ->call('openEditNoteModal')
            ->assertSet('showEditNoteModal', true)
            ->assertSet('editingNote', 'Initial note')
            ->set('editingNote', 'Updated note with new observations')
            ->call('saveNote')
            ->assertHasNoErrors()
            ->assertSet('showEditNoteModal', false);

        $this->assertEquals('Updated note with new observations', $submission->fresh()->note);
    }

    public function test_student_can_soft_delete_submission(): void
    {
        $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";
        $file = UploadedFile::fake()->createWithContent('to_delete.pdf', $pdfContent);

        Livewire::actingAs($this->studentUser)
            ->test(AssignmentShow::class, ['assignment' => $this->assignment])
            ->set('submissionFile', $file)
            ->call('submitWork');

        $submission = $this->assignment->submissionForStudent($this->student->id);
        $this->assertNotNull($submission);

        Livewire::actingAs($this->studentUser)
            ->test(AssignmentShow::class, ['assignment' => $this->assignment])
            ->call('confirmDelete')
            ->assertSet('showDeleteModal', true)
            ->call('deleteSubmission')
            ->assertHasNoErrors()
            ->assertSet('showDeleteModal', false);

        $this->assertTrue($submission->fresh()->trashed());
        $this->assertNull($this->assignment->submissionForStudent($this->student->id));
        $this->assertEquals(AssignmentSubmissionStatus::NotSubmitted, $this->assignment->statusForStudent($this->student->id));
    }

    public function test_parent_can_submit_and_replace_on_behalf_of_enrolled_child(): void
    {
        $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";
        $file = UploadedFile::fake()->createWithContent('child_homework.pdf', $pdfContent);

        Livewire::actingAs($this->parentUser)
            ->test(AssignmentShow::class, [
                'assignment' => $this->assignment,
                'student' => $this->student->id,
            ])
            ->assertSee('Albert Einstein')
            ->set('submissionFile', $file)
            ->set('submissionNote', 'Submitted by mother Pauline')
            ->call('submitWork')
            ->assertHasNoErrors();

        $submission = $this->assignment->submissionForStudent($this->student->id);
        $this->assertNotNull($submission);
        $this->assertEquals($this->parentUser->id, $submission->submitted_by);
        $this->assertEquals('Submitted by mother Pauline', $submission->note);
    }

    public function test_graded_submission_locks_modifications(): void
    {
        $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";
        $file = UploadedFile::fake()->createWithContent('exam.pdf', $pdfContent);

        Livewire::actingAs($this->studentUser)
            ->test(AssignmentShow::class, ['assignment' => $this->assignment])
            ->set('submissionFile', $file)
            ->call('submitWork');

        /** @var AssignmentSubmission $submission */
        $submission = $this->assignment->submissionForStudent($this->student->id);

        // Teacher grades submission
        $submission->markGraded($this->teacher, 95, 'Flawless mathematical formulation!');

        $submission->refresh();
        $this->assertTrue($submission->isGraded());
        $this->assertTrue($submission->isLocked());
        $this->assertFalse($submission->canBeModified());

        // Student attempt to replace or delete should be forbidden
        Livewire::actingAs($this->studentUser)
            ->test(AssignmentShow::class, ['assignment' => $this->assignment])
            ->call('openReplaceModal')
            ->assertForbidden();

        Livewire::actingAs($this->studentUser)
            ->test(AssignmentShow::class, ['assignment' => $this->assignment])
            ->call('confirmDelete')
            ->assertForbidden();
    }

    public function test_returned_submission_allows_revision_reupload(): void
    {
        $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";
        $file = UploadedFile::fake()->createWithContent('draft.pdf', $pdfContent);

        Livewire::actingAs($this->studentUser)
            ->test(AssignmentShow::class, ['assignment' => $this->assignment])
            ->set('submissionFile', $file)
            ->call('submitWork');

        /** @var AssignmentSubmission $submission */
        $submission = $this->assignment->submissionForStudent($this->student->id);

        // Teacher returns for correction
        $submission->markReturned($this->teacher, 'Missing boundary condition in section 2. Please revise.');

        $submission->refresh();
        $this->assertEquals(AssignmentSubmissionStatus::Returned, $submission->status);
        $this->assertTrue($submission->isReturned());
        $this->assertTrue($submission->canBeModified());

        // Student re-uploads revised version 2
        $revisedFile = UploadedFile::fake()->createWithContent('draft_v2.pdf', $pdfContent);

        Livewire::actingAs($this->studentUser)
            ->test(AssignmentShow::class, ['assignment' => $this->assignment])
            ->call('openReplaceModal')
            ->set('replacementFile', $revisedFile)
            ->set('replacementNote', 'Boundary conditions added.')
            ->call('replaceWork')
            ->assertHasNoErrors();

        $active = $this->assignment->submissionForStudent($this->student->id);
        $this->assertEquals(2, $active->version);
        $this->assertEquals(AssignmentSubmissionStatus::Submitted, $active->status);
    }

    public function test_cross_institution_isolation_prevents_viewing_foreign_assignment(): void
    {
        $foreignAssignment = Assignment::create([
            'institution_id' => $this->otherInstitution->id,
            'class_id' => $this->class->id,
            'created_by' => $this->teacher->id,
            'title' => 'Foreign University Assignment',
            'status' => AssignmentStatus::Published->value,
            'allowed_file_types' => ['pdf'],
            'max_file_size_mb' => 10,
            'deadline' => now()->addDays(5),
            'allow_late' => false,
            'resubmission_limit' => 0,
            'total_marks' => 100,
        ]);

        $this->actingAs($this->studentUser)
            ->get(route('assignments.show', $foreignAssignment))
            ->assertNotFound();
    }
}
