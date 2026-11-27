<?php

namespace Tests\Feature;

use App\Enums\AssignmentStatus;
use App\Enums\AssignmentSubmissionStatus;
use App\Enums\ClassKind;
use App\Enums\InstitutionType;
use App\Enums\RoleName;
use App\Livewire\Teacher\AssignmentReviewManager;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Guardian;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity as AuditActivity;
use Tests\TestCase;

class AssignmentReviewTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected Institution $otherInstitution;

    protected User $teacher;

    protected User $adminUser;

    protected User $studentUser1;

    protected User $studentUser2;

    protected User $parentUser1;

    protected Student $student1;

    protected Student $student2;

    protected Student $student3; // Missing submission

    protected Guardian $guardian1;

    protected SchoolClass $class;

    protected Assignment $assignment;

    protected AssignmentSubmission $submission1;

    protected AssignmentSubmission $submission2;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');
        Storage::fake('public');

        $this->institution = Institution::create([
            'name' => 'Imperial Institute of Technology',
            'slug' => 'imperial-tech',
            'code' => 'IIT',
            'type' => InstitutionType::Institute->value,
            'is_active' => true,
        ]);

        $this->otherInstitution = Institution::create([
            'name' => 'Northern Polytechnic',
            'slug' => 'northern-poly',
            'code' => 'NP',
            'type' => InstitutionType::Institute->value,
            'is_active' => true,
        ]);

        session(['current_institution_id' => $this->institution->id]);

        $this->seed(RolesAndPermissionsSeeder::class);

        // Teacher
        $this->teacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Prof. Richard Feynman',
            'status' => 'active',
        ]);
        $this->teacher->assignRole(RoleName::Teacher->value);

        // Admin
        $this->adminUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Dean Thomas',
            'status' => 'active',
            'two_factor_secret' => 'secret123',
            'two_factor_confirmed_at' => now(),
        ]);
        $this->adminUser->assignRole(RoleName::Admin->value);

        // Student 1 (Enrolled, Has Submission)
        $this->studentUser1 = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Richard Feynman Jr.',
            'status' => 'active',
        ]);
        $this->studentUser1->assignRole(RoleName::Student->value);

        $this->student1 = Student::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->studentUser1->id,
            'student_code' => 'STU-001',
            'first_name' => 'Richard',
            'last_name' => 'Feynman Jr.',
            'dob' => now()->subYears(18),
            'status' => 'active',
            'kind' => 'institute',
        ]);

        // Student 2 (Enrolled, Has Late Submission)
        $this->studentUser2 = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Ada Lovelace',
            'status' => 'active',
        ]);
        $this->studentUser2->assignRole(RoleName::Student->value);

        $this->student2 = Student::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->studentUser2->id,
            'student_code' => 'STU-002',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'dob' => now()->subYears(19),
            'status' => 'active',
            'kind' => 'institute',
        ]);

        // Student 3 (Enrolled, NO submission yet)
        $this->student3 = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => 'STU-003',
            'first_name' => 'Charles',
            'last_name' => 'Babbage',
            'dob' => now()->subYears(19),
            'status' => 'active',
            'kind' => 'institute',
        ]);

        // Parent / Guardian for Student 1
        $this->parentUser1 = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Mary Feynman',
            'status' => 'active',
        ]);
        $this->parentUser1->assignRole(RoleName::Parent->value);

        $this->guardian1 = Guardian::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->parentUser1->id,
            'relationship' => 'mother',
        ]);
        $this->guardian1->students()->attach($this->student1->id, [
            'institution_id' => $this->institution->id,
            'relationship' => 'mother',
            'is_primary' => true,
        ]);

        // Class
        $this->class = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Electrodynamics & Optics',
            'kind' => ClassKind::InstituteClass,
            'fee_amount' => 6000.00,
            'capacity' => 30,
            'status' => 'active',
        ]);

        // Enroll students in class
        $this->student1->classes()->attach($this->class->id, [
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'enrolled_at' => now()->subMonth(),
        ]);
        $this->student2->classes()->attach($this->class->id, [
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'enrolled_at' => now()->subMonth(),
        ]);
        $this->student3->classes()->attach($this->class->id, [
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'enrolled_at' => now()->subMonth(),
        ]);

        // Assignment
        $this->assignment = Assignment::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->class->id,
            'created_by' => $this->teacher->id,
            'title' => 'Maxwell Equations Midterm Essay',
            'description' => 'Detailed analysis of electromagnetic wave propagation.',
            'status' => AssignmentStatus::Published,
            'allowed_file_types' => ['pdf', 'docx'],
            'max_file_size_mb' => 20,
            'deadline' => now()->addDays(5),
            'allow_late' => true,
            'resubmission_limit' => 3,
            'total_marks' => 100,
            'pass_marks' => 45,
        ]);

        // Submissions for Student 1 & 2
        $this->submission1 = AssignmentSubmission::create([
            'institution_id' => $this->institution->id,
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student1->id,
            'submitted_by' => $this->studentUser1->id,
            'note' => 'First attempt with mathematical proofs.',
            'status' => AssignmentSubmissionStatus::Submitted,
            'version' => 1,
            'is_late' => false,
            'submitted_at' => now()->subDay(),
        ]);

        $this->submission2 = AssignmentSubmission::create([
            'institution_id' => $this->institution->id,
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student2->id,
            'submitted_by' => $this->studentUser2->id,
            'note' => 'Submitted slightly late due to lab schedule.',
            'status' => AssignmentSubmissionStatus::Late,
            'version' => 1,
            'is_late' => true,
            'submitted_at' => now()->subHours(2),
        ]);
    }

    public function test_guest_cannot_access_review_screen(): void
    {
        $this->get(route('teacher.assignments.reviews', $this->assignment))
            ->assertRedirect(route('login'));
    }

    public function test_student_cannot_access_review_screen(): void
    {
        $this->actingAs($this->studentUser1)
            ->get(route('teacher.assignments.reviews', $this->assignment))
            ->assertForbidden();
    }

    public function test_teacher_and_admin_can_access_review_screen(): void
    {
        $this->actingAs($this->teacher)
            ->get(route('teacher.assignments.reviews', $this->assignment))
            ->assertOk()
            ->assertSee('Maxwell Equations Midterm Essay')
            ->assertSee('Electrodynamics & Optics');

        $this->actingAs($this->adminUser)
            ->get(route('teacher.assignments.reviews', $this->assignment))
            ->assertOk();
    }

    public function test_teacher_can_view_enrolled_students_roster_and_metrics(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(AssignmentReviewManager::class, ['assignment' => $this->assignment])
            ->assertSee('Total Enrolled')
            ->assertSee('3') // 3 students enrolled
            ->assertSee('Pending Review')
            ->assertSee('2') // submission1 & submission2
            ->assertSee('Missing')
            ->assertSee('1') // student3 has not submitted
            ->assertSee('Richard Feynman Jr.')
            ->assertSee('Ada Lovelace')
            ->assertSee('Charles Babbage');
    }

    public function test_search_and_status_filtering_works_on_roster(): void
    {
        // 1. Search by name
        Livewire::actingAs($this->teacher)
            ->test(AssignmentReviewManager::class, ['assignment' => $this->assignment])
            ->set('search', 'Lovelace')
            ->assertSee('Ada Lovelace')
            ->assertDontSee('Richard Feynman Jr.')
            ->assertDontSee('Charles Babbage');

        // 2. Filter by Missing (only student 3)
        Livewire::actingAs($this->teacher)
            ->test(AssignmentReviewManager::class, ['assignment' => $this->assignment])
            ->set('statusFilter', 'missing')
            ->assertSee('Charles Babbage')
            ->assertDontSee('Richard Feynman Jr.')
            ->assertDontSee('Ada Lovelace');

        // 3. Filter by Pending
        Livewire::actingAs($this->teacher)
            ->test(AssignmentReviewManager::class, ['assignment' => $this->assignment])
            ->set('statusFilter', 'pending')
            ->assertSee('Richard Feynman Jr.')
            ->assertSee('Ada Lovelace')
            ->assertDontSee('Charles Babbage');
    }

    public function test_teacher_can_grade_and_accept_submission_with_marks_and_feedback(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(AssignmentReviewManager::class, ['assignment' => $this->assignment])
            ->call('openGradeModal', $this->student1->id)
            ->assertSet('showGradeModal', true)
            ->assertSet('selectedStudentId', $this->student1->id)
            ->set('reviewMarks', 92)
            ->set('reviewFeedback', 'Exceptional derivation of Poynting vector.')
            ->call('submitGrade')
            ->assertHasNoErrors()
            ->assertSet('showGradeModal', false);

        $this->submission1->refresh();

        $this->assertEquals(AssignmentSubmissionStatus::Graded, $this->submission1->status);
        $this->assertEquals(92, $this->submission1->marks);
        $this->assertEquals('Exceptional derivation of Poynting vector.', $this->submission1->feedback);
        $this->assertEquals($this->teacher->id, $this->submission1->reviewed_by);
        $this->assertNotNull($this->submission1->reviewed_at);

        // Audit Log (§14)
        $this->assertDatabaseHas('activity_log', [
            'event' => 'assignment.submission_graded',
            'subject_type' => AssignmentSubmission::class,
            'subject_id' => $this->submission1->id,
            'causer_id' => $this->teacher->id,
        ]);

        $audit = AuditActivity::where('event', 'assignment.submission_graded')->latest('id')->first();
        $this->assertNotNull($audit);
        $this->assertEquals(92, $audit->properties['marks'] ?? null);

        // Student and Guardian Notifications Dispatched
        $this->assertTrue($this->studentUser1->notifications()->exists());
        $studentNotif = $this->studentUser1->notifications()->first();
        $this->assertNotNull($studentNotif);
        $this->assertStringContainsString('Maxwell Equations Midterm Essay', $studentNotif->data['title'] ?? '');
        $this->assertStringContainsString('92/100', $studentNotif->data['message'] ?? '');

        $this->assertTrue($this->parentUser1->notifications()->exists());
        $parentNotif = $this->parentUser1->notifications()->first();
        $this->assertNotNull($parentNotif);
        $this->assertStringContainsString('92/100', $parentNotif->data['message'] ?? '');
    }

    public function test_teacher_cannot_award_marks_exceeding_total_marks(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(AssignmentReviewManager::class, ['assignment' => $this->assignment])
            ->call('openGradeModal', $this->student1->id)
            ->set('reviewMarks', 120) // Total is 100
            ->set('reviewFeedback', 'Invalid high marks')
            ->call('submitGrade')
            ->assertHasErrors(['reviewMarks'])
            ->assertSet('showGradeModal', true);

        // Negative marks rejected
        Livewire::actingAs($this->teacher)
            ->test(AssignmentReviewManager::class, ['assignment' => $this->assignment])
            ->call('openGradeModal', $this->student1->id)
            ->set('reviewMarks', -5)
            ->call('submitGrade')
            ->assertHasErrors(['reviewMarks']);
    }

    public function test_teacher_can_return_submission_for_correction_with_mandatory_reason(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(AssignmentReviewManager::class, ['assignment' => $this->assignment])
            ->call('openReturnModal', $this->student1->id)
            ->assertSet('showReturnModal', true)
            ->set('returnReason', 'Please revise Section 2 calculations and include vector diagrams.')
            ->call('confirmReturn')
            ->assertHasNoErrors()
            ->assertSet('showReturnModal', false);

        $this->submission1->refresh();

        $this->assertEquals(AssignmentSubmissionStatus::Returned, $this->submission1->status);
        $this->assertEquals('Please revise Section 2 calculations and include vector diagrams.', $this->submission1->feedback);
        $this->assertEquals($this->teacher->id, $this->submission1->reviewed_by);

        // Audit Log (§14)
        $this->assertDatabaseHas('activity_log', [
            'event' => 'assignment.submission_returned',
            'subject_type' => AssignmentSubmission::class,
            'subject_id' => $this->submission1->id,
            'causer_id' => $this->teacher->id,
        ]);

        // Notification dispatched
        $this->assertTrue($this->studentUser1->notifications()->exists());
        $notif = $this->studentUser1->notifications()->latest('id')->first();
        $this->assertNotNull($notif);
        $this->assertStringContainsString('returned for correction', $notif->data['message'] ?? '');
    }

    public function test_return_requires_meaningful_reason(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(AssignmentReviewManager::class, ['assignment' => $this->assignment])
            ->call('openReturnModal', $this->student1->id)
            ->set('returnReason', '')
            ->call('confirmReturn')
            ->assertHasErrors(['returnReason']);

        Livewire::actingAs($this->teacher)
            ->test(AssignmentReviewManager::class, ['assignment' => $this->assignment])
            ->call('openReturnModal', $this->student1->id)
            ->set('returnReason', 'fix') // min 5 chars
            ->call('confirmReturn')
            ->assertHasErrors(['returnReason']);
    }

    public function test_teacher_can_reopen_graded_submission(): void
    {
        $this->submission1->markGraded($this->teacher, 80, 'Initial score');

        Livewire::actingAs($this->teacher)
            ->test(AssignmentReviewManager::class, ['assignment' => $this->assignment])
            ->call('reopenSubmission', $this->submission1->id);

        $this->submission1->refresh();

        $this->assertEquals(AssignmentSubmissionStatus::Submitted, $this->submission1->status);
        $this->assertNull($this->submission1->reviewed_by);
        $this->assertNull($this->submission1->reviewed_at);

        $this->assertDatabaseHas('activity_log', [
            'event' => 'assignment.submission_reopened',
            'subject_id' => $this->submission1->id,
        ]);
    }

    public function test_teacher_can_view_submission_version_history_modal(): void
    {
        // Create second version for student 1
        AssignmentSubmission::create([
            'institution_id' => $this->institution->id,
            'assignment_id' => $this->assignment->id,
            'student_id' => $this->student1->id,
            'submitted_by' => $this->studentUser1->id,
            'note' => 'Second corrected attempt with improved diagrams.',
            'status' => AssignmentSubmissionStatus::Submitted,
            'version' => 2,
            'is_late' => false,
            'submitted_at' => now(),
        ]);

        Livewire::actingAs($this->teacher)
            ->test(AssignmentReviewManager::class, ['assignment' => $this->assignment])
            ->assertSee('v1–v2')
            ->call('openHistoryModal', $this->student1->id)
            ->assertSet('showHistoryModal', true)
            ->assertSee('First attempt with mathematical proofs.')
            ->assertSee('Second corrected attempt with improved diagrams.')
            ->call('closeHistoryModal')
            ->assertSet('showHistoryModal', false);
    }

    public function test_multi_institution_isolation_prevents_reviewing_another_institution_assignment(): void
    {
        $otherTeacher = User::factory()->create([
            'institution_id' => $this->otherInstitution->id,
            'status' => 'active',
        ]);
        $otherTeacher->assignRole(RoleName::Teacher->value);

        $otherClass = SchoolClass::create([
            'institution_id' => $this->otherInstitution->id,
            'title' => 'Other Poly Class',
            'kind' => ClassKind::InstituteClass,
            'fee_amount' => 5000.00,
            'capacity' => 20,
            'status' => 'active',
        ]);

        $otherAssignment = Assignment::create([
            'institution_id' => $this->otherInstitution->id,
            'class_id' => $otherClass->id,
            'created_by' => $otherTeacher->id,
            'title' => 'Foreign Assignment',
            'status' => AssignmentStatus::Published,
            'allowed_file_types' => ['pdf'],
            'max_file_size_mb' => 10,
            'deadline' => now()->addDays(2),
            'allow_late' => false,
            'resubmission_limit' => 1,
            'total_marks' => 50,
        ]);

        $this->actingAs($this->teacher)
            ->get(route('teacher.assignments.reviews', $otherAssignment))
            ->assertNotFound();
    }
}
