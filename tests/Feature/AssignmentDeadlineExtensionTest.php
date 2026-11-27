<?php

namespace Tests\Feature;

use App\Enums\AssignmentStatus;
use App\Enums\AssignmentSubmissionStatus;
use App\Enums\ClassKind;
use App\Enums\InstitutionType;
use App\Enums\RoleName;
use App\Livewire\Student\AssignmentShow;
use App\Livewire\Teacher\AssignmentManager;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Guardian;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Services\DeadlineExtensionService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity as AuditActivity;
use Tests\TestCase;

class AssignmentDeadlineExtensionTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected Institution $otherInstitution;

    protected User $teacher;

    protected User $studentUser;

    protected User $secondStudentUser;

    protected User $unenrolledStudentUser;

    protected User $parentUser;

    protected Student $student;

    protected Student $secondStudent;

    protected Student $unenrolledStudent;

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

        // Student 1 (Albert)
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

        // Student 2 (Marie)
        $this->secondStudentUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Marie Curie',
            'status' => 'active',
        ]);
        $this->secondStudentUser->assignRole(RoleName::Student->value);

        $this->secondStudent = Student::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->secondStudentUser->id,
            'student_code' => 'INS-26-0002',
            'first_name' => 'Marie',
            'last_name' => 'Curie',
            'dob' => now()->subYears(16),
            'status' => 'active',
            'kind' => 'institute',
        ]);

        // Unenrolled Student (Isaac)
        $this->unenrolledStudentUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Isaac Newton',
            'status' => 'active',
        ]);
        $this->unenrolledStudentUser->assignRole(RoleName::Student->value);

        $this->unenrolledStudent = Student::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->unenrolledStudentUser->id,
            'student_code' => 'INS-26-0003',
            'first_name' => 'Isaac',
            'last_name' => 'Newton',
            'dob' => now()->subYears(17),
            'status' => 'active',
            'kind' => 'institute',
        ]);

        // Parent & Guardian of Albert
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

        // Enroll Albert & Marie
        $this->student->classes()->attach($this->class->id, [
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'enrolled_at' => now()->subMonth(),
        ]);

        $this->secondStudent->classes()->attach($this->class->id, [
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'enrolled_at' => now()->subMonth(),
        ]);

        // Assignment
        $this->assignment = Assignment::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->class->id,
            'created_by' => $this->teacher->id,
            'title' => 'Quantum Mechanics Problem Set 1',
            'description' => 'Comprehensive problem set on wave mechanics',
            'instructions' => 'Submit PDF solutions with step-by-step proofs',
            'status' => AssignmentStatus::Published,
            'allowed_file_types' => ['pdf', 'docx'],
            'max_file_size_mb' => 25,
            'deadline' => now()->addDays(3)->setTime(17, 0),
            'allow_late' => true,
            'resubmission_limit' => 3,
            'total_marks' => 100,
            'pass_marks' => 50,
        ]);
    }

    public function test_guest_cannot_extend_assignment_deadline(): void
    {
        $this->get(route('teacher.assignments'))
            ->assertRedirect(route('login'));
    }

    public function test_student_cannot_extend_assignment_deadline(): void
    {
        $this->actingAs($this->studentUser)
            ->get(route('teacher.assignments'))
            ->assertForbidden();
    }

    public function test_teacher_can_extend_deadline_for_everyone(): void
    {
        $originalDeadline = $this->assignment->deadline;
        $newDeadline = $originalDeadline->copy()->addDays(4);

        Livewire::actingAs($this->teacher)
            ->test(AssignmentManager::class)
            ->call('openExtensionModal', $this->assignment->id)
            ->assertSet('showExtensionModal', true)
            ->assertSet('extensionTarget', 'everyone')
            ->set('extensionNewDeadline', $newDeadline->format('Y-m-d\TH:i'))
            ->set('extensionReason', 'Severe weather closure; extra research time approved for all students.')
            ->call('saveExtension')
            ->assertHasNoErrors()
            ->assertSet('showExtensionModal', false);

        $this->assertDatabaseHas('deadline_extensions', [
            'institution_id' => $this->institution->id,
            'extendable_type' => Assignment::class,
            'extendable_id' => $this->assignment->id,
            'student_id' => null,
            'extended_by' => $this->teacher->id,
            'reason' => 'Severe weather closure; extra research time approved for all students.',
        ]);

        // Effective deadline for everyone updated
        $this->assignment->refresh();
        $this->assertTrue($this->assignment->isExtendedForStudent());
        $this->assertTrue($this->assignment->isExtendedForStudent($this->student->id));
        $this->assertTrue($this->assignment->isExtendedForStudent($this->secondStudent->id));

        $this->assertEquals(
            $newDeadline->format('Y-m-d H:i'),
            $this->assignment->effectiveDeadlineForStudent()->format('Y-m-d H:i')
        );
        $this->assertEquals(
            $newDeadline->format('Y-m-d H:i'),
            $this->assignment->effectiveDeadlineForStudent($this->student->id)->format('Y-m-d H:i')
        );
    }

    public function test_teacher_can_extend_deadline_for_individual_student(): void
    {
        $originalDeadline = $this->assignment->deadline;
        $albertNewDeadline = $originalDeadline->copy()->addDays(5);

        Livewire::actingAs($this->teacher)
            ->test(AssignmentManager::class)
            ->call('openExtensionModal', $this->assignment->id)
            ->set('extensionTarget', 'individual')
            ->set('extensionStudentId', $this->student->id)
            ->set('extensionNewDeadline', $albertNewDeadline->format('Y-m-d\TH:i'))
            ->set('extensionReason', 'Approved medical leave with doctor certificate.')
            ->call('saveExtension')
            ->assertHasNoErrors()
            ->assertSet('showExtensionModal', false);

        $this->assertDatabaseHas('deadline_extensions', [
            'institution_id' => $this->institution->id,
            'extendable_type' => Assignment::class,
            'extendable_id' => $this->assignment->id,
            'student_id' => $this->student->id,
            'extended_by' => $this->teacher->id,
            'reason' => 'Approved medical leave with doctor certificate.',
        ]);

        $this->assignment->refresh();

        // Albert has individual extension
        $this->assertTrue($this->assignment->isExtendedForStudent($this->student->id));
        $this->assertEquals(
            $albertNewDeadline->format('Y-m-d H:i'),
            $this->assignment->effectiveDeadlineForStudent($this->student->id)->format('Y-m-d H:i')
        );

        // Marie has NO extension and keeps original deadline
        $this->assertFalse($this->assignment->isExtendedForStudent($this->secondStudent->id));
        $this->assertEquals(
            $originalDeadline->format('Y-m-d H:i'),
            $this->assignment->effectiveDeadlineForStudent($this->secondStudent->id)->format('Y-m-d H:i')
        );
    }

    public function test_individual_extension_takes_precedence_over_class_wide_extension(): void
    {
        $originalDeadline = $this->assignment->deadline;
        $classWideDeadline = $originalDeadline->copy()->addDays(2);
        $albertDeadline = $originalDeadline->copy()->addDays(6);

        $service = app(DeadlineExtensionService::class);

        // Class-wide extension first
        $service->extendAssignmentDeadline(
            assignment: $this->assignment,
            extendedBy: $this->teacher,
            newDeadline: $classWideDeadline,
            reason: 'Class wide delay',
            studentId: null
        );

        // Individual extension for Albert
        $service->extendAssignmentDeadline(
            assignment: $this->assignment,
            extendedBy: $this->teacher,
            newDeadline: $albertDeadline,
            reason: 'Medical extension for Albert',
            studentId: $this->student->id
        );

        $this->assignment->refresh();

        // Albert gets his individual deadline
        $this->assertEquals(
            $albertDeadline->format('Y-m-d H:i'),
            $this->assignment->effectiveDeadlineForStudent($this->student->id)->format('Y-m-d H:i')
        );

        // Marie gets the class-wide deadline
        $this->assertEquals(
            $classWideDeadline->format('Y-m-d H:i'),
            $this->assignment->effectiveDeadlineForStudent($this->secondStudent->id)->format('Y-m-d H:i')
        );
    }

    public function test_extension_requires_valid_reason_and_future_date_after_current_deadline(): void
    {
        $originalDeadline = $this->assignment->deadline;

        // 1. Past date fails
        Livewire::actingAs($this->teacher)
            ->test(AssignmentManager::class)
            ->call('openExtensionModal', $this->assignment->id)
            ->set('extensionTarget', 'everyone')
            ->set('extensionNewDeadline', now()->subDay()->format('Y-m-d\TH:i'))
            ->set('extensionReason', 'Valid reason here')
            ->call('saveExtension')
            ->assertSet('showExtensionModal', true)
            ->assertSee('cannot be in the past');

        // 2. Date earlier than current deadline fails
        Livewire::actingAs($this->teacher)
            ->test(AssignmentManager::class)
            ->call('openExtensionModal', $this->assignment->id)
            ->set('extensionTarget', 'everyone')
            ->set('extensionNewDeadline', $originalDeadline->copy()->subHour()->format('Y-m-d\TH:i'))
            ->set('extensionReason', 'Valid reason here')
            ->call('saveExtension')
            ->assertSet('showExtensionModal', true)
            ->assertSee('must be strictly after the current deadline');

        // 3. Empty reason fails validation
        Livewire::actingAs($this->teacher)
            ->test(AssignmentManager::class)
            ->call('openExtensionModal', $this->assignment->id)
            ->set('extensionTarget', 'everyone')
            ->set('extensionNewDeadline', $originalDeadline->copy()->addDays(2)->format('Y-m-d\TH:i'))
            ->set('extensionReason', '')
            ->call('saveExtension')
            ->assertHasErrors(['extensionReason']);
    }

    public function test_unenrolled_student_cannot_be_granted_extension(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The specified student is not enrolled in this assignment class.');

        $service = app(DeadlineExtensionService::class);
        $service->extendAssignmentDeadline(
            assignment: $this->assignment,
            extendedBy: $this->teacher,
            newDeadline: $this->assignment->deadline->copy()->addDays(3),
            reason: 'Invalid extension attempt',
            studentId: $this->unenrolledStudent->id
        );
    }

    public function test_extending_assignment_deadline_creates_audit_log_and_dispatches_notifications(): void
    {
        $newDeadline = $this->assignment->deadline->copy()->addDays(4);

        $service = app(DeadlineExtensionService::class);
        $extension = $service->extendAssignmentDeadline(
            assignment: $this->assignment,
            extendedBy: $this->teacher,
            newDeadline: $newDeadline,
            reason: 'Campus laboratory server maintenance',
            studentId: $this->student->id
        );

        // Audit Log (§14)
        $this->assertDatabaseHas('activity_log', [
            'event' => 'assignment.deadline_extended',
            'subject_type' => Assignment::class,
            'subject_id' => $this->assignment->id,
            'causer_id' => $this->teacher->id,
        ]);

        $audit = AuditActivity::where('event', 'assignment.deadline_extended')
            ->where('subject_id', $this->assignment->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($audit);
        $this->assertEquals($this->student->id, $audit->properties['student_id'] ?? null);
        $this->assertEquals('student', $audit->properties['target'] ?? null);
        $this->assertEquals('Campus laboratory server maintenance', $audit->properties['reason'] ?? null);

        // Notifications dispatched (§8, §12)
        // Albert (student) should receive notification
        $this->assertTrue($this->studentUser->notifications()->exists());
        $studentNotif = $this->studentUser->notifications()->first();
        $this->assertNotNull($studentNotif);
        $this->assertStringContainsString('Quantum Mechanics Problem Set 1', $studentNotif->data['title'] ?? '');
        $this->assertStringContainsString('Campus laboratory server maintenance', $studentNotif->data['message'] ?? '');

        // Pauline (parent/guardian) should receive notification
        $this->assertTrue($this->parentUser->notifications()->exists());
        $parentNotif = $this->parentUser->notifications()->first();
        $this->assertNotNull($parentNotif);
        $this->assertStringContainsString('Quantum Mechanics Problem Set 1', $parentNotif->data['title'] ?? '');
        $this->assertStringContainsString('Campus laboratory server maintenance', $parentNotif->data['message'] ?? '');
        $this->assertEquals(route('assignments.show', ['assignment' => $this->assignment->id]), $parentNotif->data['url'] ?? null);
    }

    public function test_student_and_parent_assignment_view_reflects_extended_deadline_and_reason(): void
    {
        $newDeadline = $this->assignment->deadline->copy()->addDays(3);

        $service = app(DeadlineExtensionService::class);
        $service->extendAssignmentDeadline(
            assignment: $this->assignment,
            extendedBy: $this->teacher,
            newDeadline: $newDeadline,
            reason: 'Physics Symposium travel granted',
            studentId: $this->student->id
        );

        // Student View
        Livewire::actingAs($this->studentUser)
            ->test(AssignmentShow::class, ['assignment' => $this->assignment])
            ->assertSee('Deadline Extended')
            ->assertSee('Physics Symposium travel granted')
            ->assertSee($newDeadline->format('M d, Y - h:i A'));

        // Parent View
        Livewire::actingAs($this->parentUser)
            ->test(AssignmentShow::class, ['assignment' => $this->assignment, 'student' => $this->student->id])
            ->assertSee('Deadline Extended')
            ->assertSee('Physics Symposium travel granted')
            ->assertSee($newDeadline->format('M d, Y - h:i A'));
    }

    public function test_student_with_extension_can_submit_after_original_deadline_without_late_flag(): void
    {
        // 1. Assignment deadline was 1 hour ago
        $this->assignment->update([
            'deadline' => now()->subHour(),
        ]);

        // 2. Albert was granted an extension till 2 days in future
        $albertNewDeadline = now()->addDays(2);
        $service = app(DeadlineExtensionService::class);
        $service->extendAssignmentDeadline(
            assignment: $this->assignment,
            extendedBy: $this->teacher,
            newDeadline: $albertNewDeadline,
            reason: 'Medical recovery period',
            studentId: $this->student->id
        );

        // 3. Albert submits now (past original deadline, but within extended deadline)
        $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";
        $file = UploadedFile::fake()->createWithContent('proofs.pdf', $pdfContent);

        Livewire::actingAs($this->studentUser)
            ->test(AssignmentShow::class, ['assignment' => $this->assignment])
            ->set('submissionFile', $file)
            ->set('submissionNote', 'Completed problem set')
            ->call('submitWork')
            ->assertHasNoErrors()
            ->assertSet('submissionError', '');

        /** @var AssignmentSubmission $submission */
        $submission = AssignmentSubmission::where('assignment_id', $this->assignment->id)
            ->where('student_id', $this->student->id)
            ->first();

        $this->assertNotNull($submission);
        $this->assertFalse($submission->is_late, 'Submission should NOT be flagged as late because Albert has a valid extension');
        $this->assertEquals(AssignmentSubmissionStatus::Submitted, $submission->status);
    }

    public function test_teacher_can_view_extension_history_log(): void
    {
        $service = app(DeadlineExtensionService::class);

        $service->extendAssignmentDeadline(
            assignment: $this->assignment,
            extendedBy: $this->teacher,
            newDeadline: $this->assignment->deadline->copy()->addDays(2),
            reason: 'Campus-wide weather holiday',
            studentId: null
        );

        $service->extendAssignmentDeadline(
            assignment: $this->assignment,
            extendedBy: $this->teacher,
            newDeadline: $this->assignment->deadline->copy()->addDays(4),
            reason: 'Special academic competition exemption',
            studentId: $this->student->id
        );

        Livewire::actingAs($this->teacher)
            ->test(AssignmentManager::class)
            ->assertSee('2 extensions')
            ->call('openExtensionLogModal', $this->assignment->id)
            ->assertSet('showExtensionLogModal', true)
            ->assertSee('Campus-wide weather holiday')
            ->assertSee('Special academic competition exemption')
            ->assertSee('Albert Einstein')
            ->assertSee('Class-wide (Everyone)')
            ->call('closeExtensionLogModal')
            ->assertSet('showExtensionLogModal', false);
    }

    public function test_multi_institution_isolation_prevents_extending_another_institution_assignment(): void
    {
        $otherTeacher = User::factory()->create([
            'institution_id' => $this->otherInstitution->id,
            'status' => 'active',
        ]);
        $otherTeacher->assignRole(RoleName::Teacher->value);

        // Assignment belonging to other institution
        $otherClass = SchoolClass::create([
            'institution_id' => $this->otherInstitution->id,
            'title' => 'Metro College Math',
            'kind' => ClassKind::InstituteClass,
            'fee_amount' => 5000.00,
            'capacity' => 20,
            'status' => 'active',
        ]);

        $otherAssignment = Assignment::create([
            'institution_id' => $this->otherInstitution->id,
            'class_id' => $otherClass->id,
            'created_by' => $otherTeacher->id,
            'title' => 'Other Institution Test',
            'status' => AssignmentStatus::Published,
            'allowed_file_types' => ['pdf'],
            'max_file_size_mb' => 10,
            'deadline' => now()->addDays(2),
            'allow_late' => false,
            'resubmission_limit' => 1,
            'total_marks' => 50,
        ]);

        // Attempting to open extension modal for other institution assignment fails with 404
        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($this->teacher)
            ->test(AssignmentManager::class)
            ->call('openExtensionModal', $otherAssignment->id);
    }
}
