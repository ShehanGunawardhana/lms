<?php

namespace Tests\Feature;

use App\Enums\AssignmentStatus;
use App\Enums\AssignmentSubmissionStatus;
use App\Enums\ClassKind;
use App\Enums\InstitutionType;
use App\Enums\RoleName;
use App\Livewire\Student\AssignmentList;
use App\Livewire\Student\AssignmentShow;
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
use Tests\TestCase;

class StudentAssignmentViewTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected Institution $otherInstitution;

    protected User $teacher;

    protected User $studentUser1;

    protected User $studentUser2;

    protected User $parentUser;

    protected Student $student1;

    protected Student $student2;

    protected Guardian $guardian;

    protected SchoolClass $class1;

    protected SchoolClass $class2;

    protected Assignment $assignment1;

    protected Assignment $assignment2;

    protected Assignment $assignment3;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');
        Storage::fake('public');

        $this->institution = Institution::create([
            'name' => 'Royal Science University',
            'slug' => 'royal-science',
            'code' => 'RSU',
            'type' => InstitutionType::Institute->value,
            'is_active' => true,
        ]);

        $this->otherInstitution = Institution::create([
            'name' => 'Pacific Institute',
            'slug' => 'pacific-inst',
            'code' => 'PCI',
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

        // Student 1 (Enrolled in Class 1 & 2)
        $this->studentUser1 = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Isaac Newton',
            'status' => 'active',
        ]);
        $this->studentUser1->assignRole(RoleName::Student->value);

        $this->student1 = Student::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->studentUser1->id,
            'student_code' => 'STU-NEWTON',
            'first_name' => 'Isaac',
            'last_name' => 'Newton',
            'dob' => now()->subYears(18),
            'status' => 'active',
            'kind' => 'institute',
        ]);

        // Student 2 (Enrolled in Class 1 only)
        $this->studentUser2 = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Gottfried Leibniz',
            'status' => 'active',
        ]);
        $this->studentUser2->assignRole(RoleName::Student->value);

        $this->student2 = Student::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->studentUser2->id,
            'student_code' => 'STU-LEIBNIZ',
            'first_name' => 'Gottfried',
            'last_name' => 'Leibniz',
            'dob' => now()->subYears(19),
            'status' => 'active',
            'kind' => 'institute',
        ]);

        // Parent / Guardian with both children
        $this->parentUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Hannah Ayscough',
            'status' => 'active',
        ]);
        $this->parentUser->assignRole(RoleName::Parent->value);

        $this->guardian = Guardian::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->parentUser->id,
            'relationship' => 'mother',
        ]);

        $this->guardian->students()->attach($this->student1->id, [
            'institution_id' => $this->institution->id,
            'relationship' => 'mother',
            'is_primary' => true,
        ]);
        $this->guardian->students()->attach($this->student2->id, [
            'institution_id' => $this->institution->id,
            'relationship' => 'guardian',
            'is_primary' => false,
        ]);

        // Classes
        $this->class1 = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Classical Mechanics & Calculus',
            'kind' => ClassKind::InstituteClass,
            'fee_amount' => 7500.00,
            'capacity' => 25,
            'status' => 'active',
        ]);

        $this->class2 = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Optics & Light Theory',
            'kind' => ClassKind::InstituteClass,
            'fee_amount' => 6000.00,
            'capacity' => 25,
            'status' => 'active',
        ]);

        // Enroll Student 1 in Class 1 & 2
        $this->student1->classes()->attach($this->class1->id, [
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'enrolled_at' => now()->subMonth(),
        ]);
        $this->student1->classes()->attach($this->class2->id, [
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'enrolled_at' => now()->subMonth(),
        ]);

        // Enroll Student 2 in Class 1 only
        $this->student2->classes()->attach($this->class1->id, [
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'enrolled_at' => now()->subMonth(),
        ]);

        // Assignment 1: Graded with High Marks (Passed)
        $this->assignment1 = Assignment::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->class1->id,
            'created_by' => $this->teacher->id,
            'title' => 'Principia Mathematica Section 1',
            'description' => 'Derivation of the laws of planetary motion',
            'status' => AssignmentStatus::Published,
            'allowed_file_types' => ['pdf', 'docx'],
            'max_file_size_mb' => 25,
            'deadline' => now()->addDays(5),
            'allow_late' => true,
            'resubmission_limit' => 3,
            'total_marks' => 100,
            'pass_marks' => 50,
        ]);

        // Assignment 2: Returned for Correction
        $this->assignment2 = Assignment::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->class1->id,
            'created_by' => $this->teacher->id,
            'title' => 'Gravitational Constant Lab Report',
            'description' => 'Experimental measurements of G',
            'status' => AssignmentStatus::Published,
            'allowed_file_types' => ['pdf'],
            'max_file_size_mb' => 20,
            'deadline' => now()->addDays(3),
            'allow_late' => false,
            'resubmission_limit' => 2,
            'total_marks' => 50,
            'pass_marks' => 25,
        ]);

        // Assignment 3: To Do (Not submitted)
        $this->assignment3 = Assignment::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->class2->id,
            'created_by' => $this->teacher->id,
            'title' => 'Prism Color Dispersion Analysis',
            'description' => 'Spectrum experiments and chromatic aberration study',
            'status' => AssignmentStatus::Published,
            'allowed_file_types' => ['pdf', 'zip'],
            'max_file_size_mb' => 30,
            'deadline' => now()->addDays(7),
            'allow_late' => true,
            'resubmission_limit' => 3,
            'total_marks' => 80,
            'pass_marks' => 40,
        ]);

        // Submissions for Student 1
        // Submission 1: Graded (94 / 100)
        AssignmentSubmission::create([
            'institution_id' => $this->institution->id,
            'assignment_id' => $this->assignment1->id,
            'student_id' => $this->student1->id,
            'submitted_by' => $this->studentUser1->id,
            'status' => AssignmentSubmissionStatus::Graded,
            'version' => 1,
            'is_late' => false,
            'marks' => 94,
            'feedback' => 'Flawless geometrical proofs of inverse square law.',
            'reviewed_by' => $this->teacher->id,
            'reviewed_at' => now()->subDay(),
            'submitted_at' => now()->subDays(2),
        ]);

        // Submission 2: Returned for Correction
        AssignmentSubmission::create([
            'institution_id' => $this->institution->id,
            'assignment_id' => $this->assignment2->id,
            'student_id' => $this->student1->id,
            'submitted_by' => $this->studentUser1->id,
            'status' => AssignmentSubmissionStatus::Returned,
            'version' => 1,
            'is_late' => false,
            'feedback' => 'Error margins were omitted from the pendulum data. Please recalculate standard deviations.',
            'reviewed_by' => $this->teacher->id,
            'reviewed_at' => now()->subHours(6),
            'submitted_at' => now()->subDay(),
        ]);
    }

    public function test_guest_is_redirected_from_assignments(): void
    {
        $this->get(route('student.assignments.index'))
            ->assertRedirect(route('login'));
    }

    public function test_student_and_parent_can_access_student_assignments_hub(): void
    {
        $this->actingAs($this->studentUser1)
            ->get(route('student.assignments.index'))
            ->assertOk()
            ->assertSee('Coursework & Assignments')
            ->assertSee('Isaac Newton');

        $this->actingAs($this->parentUser)
            ->get(route('student.assignments.index'))
            ->assertOk()
            ->assertSee('Coursework & Assignments');
    }

    public function test_student_sees_their_enrolled_class_assignments(): void
    {
        Livewire::actingAs($this->studentUser1)
            ->test(AssignmentList::class)
            ->assertSee('Principia Mathematica Section 1')
            ->assertSee('Gravitational Constant Lab Report')
            ->assertSee('Prism Color Dispersion Analysis')
            ->assertSee('Classical Mechanics & Calculus')
            ->assertSee('Optics & Light Theory');
    }

    public function test_assignments_hub_calculates_metrics_and_status_counts(): void
    {
        Livewire::actingAs($this->studentUser1)
            ->test(AssignmentList::class)
            ->assertSee('Total Coursework')
            ->assertSee('3') // 3 assignments across enrolled classes
            ->assertSee('Graded')
            ->assertSee('1') // assignment1 is graded
            ->assertSee('Needs Revision')
            ->assertSee('1') // assignment2 is returned
            ->assertSee('To Do')
            ->assertSee('1'); // assignment3 is not submitted
    }

    public function test_student_can_filter_assignments_by_status_and_class_and_search(): void
    {
        // 1. Filter by Graded
        Livewire::actingAs($this->studentUser1)
            ->test(AssignmentList::class)
            ->set('statusFilter', 'graded')
            ->assertSee('Principia Mathematica Section 1')
            ->assertDontSee('Gravitational Constant Lab Report')
            ->assertDontSee('Prism Color Dispersion Analysis');

        // 2. Filter by Needs Revision (Returned)
        Livewire::actingAs($this->studentUser1)
            ->test(AssignmentList::class)
            ->set('statusFilter', 'returned')
            ->assertSee('Gravitational Constant Lab Report')
            ->assertDontSee('Principia Mathematica Section 1')
            ->assertDontSee('Prism Color Dispersion Analysis');

        // 3. Filter by To Do (Missing submission)
        Livewire::actingAs($this->studentUser1)
            ->test(AssignmentList::class)
            ->set('statusFilter', 'todo')
            ->assertSee('Prism Color Dispersion Analysis')
            ->assertDontSee('Principia Mathematica Section 1')
            ->assertDontSee('Gravitational Constant Lab Report');

        // 4. Search filter
        Livewire::actingAs($this->studentUser1)
            ->test(AssignmentList::class)
            ->set('search', 'Prism')
            ->assertSee('Prism Color Dispersion Analysis')
            ->assertDontSee('Principia Mathematica Section 1');
    }

    public function test_student_view_reflects_graded_marks_score_percentage_and_pass_fail_badge(): void
    {
        // 1. Hub / List View reflects marks and feedback
        Livewire::actingAs($this->studentUser1)
            ->test(AssignmentList::class)
            ->assertSee('94 / 100')
            ->assertSee('Passed')
            ->assertSee('Flawless geometrical proofs of inverse square law.');

        // 2. Detailed AssignmentShow View reflects full grading results card (§8, §14)
        Livewire::actingAs($this->studentUser1)
            ->test(AssignmentShow::class, ['assignment' => $this->assignment1])
            ->assertSee('Official Grading & Marks Result')
            ->assertSee('94')
            ->assertSee('/ 100')
            ->assertSee('(94%)')
            ->assertSee('Passed')
            ->assertSee('Flawless geometrical proofs of inverse square law.')
            ->assertSee('Graded by Prof. Richard Feynman')
            ->assertSee('Graded submission is finalized and locked against further edits.');
    }

    public function test_student_view_reflects_returned_status_with_lecturer_reason_and_upload_action(): void
    {
        // 1. Hub / List View reflects revision requested
        Livewire::actingAs($this->studentUser1)
            ->test(AssignmentList::class)
            ->assertSee('Revision Requested by Lecturer')
            ->assertSee('Error margins were omitted from the pendulum data.');

        // 2. Detailed AssignmentShow View reflects return callout with lecturer name and action button
        Livewire::actingAs($this->studentUser1)
            ->test(AssignmentShow::class, ['assignment' => $this->assignment2])
            ->assertSee('Returned for Revision by Lecturer')
            ->assertSee('Prof. Richard Feynman')
            ->assertSee('Error margins were omitted from the pendulum data.')
            ->assertSee('Upload Corrected Version (v2)');
    }

    public function test_student_can_view_historical_versions_with_past_feedback_and_marks(): void
    {
        // Add historical version 1 and active version 2 for assignment 1
        AssignmentSubmission::create([
            'institution_id' => $this->institution->id,
            'assignment_id' => $this->assignment1->id,
            'student_id' => $this->student1->id,
            'submitted_by' => $this->studentUser1->id,
            'note' => 'Original draft calculation before review.',
            'status' => AssignmentSubmissionStatus::Returned,
            'version' => 1,
            'is_late' => false,
            'feedback' => 'Initial review note: check calculus integrals.',
            'submitted_at' => now()->subDays(3),
        ]);

        Livewire::actingAs($this->studentUser1)
            ->test(AssignmentShow::class, ['assignment' => $this->assignment1])
            ->set('showHistoryModal', true)
            ->assertSee('Submission Version History')
            ->assertSee('Original draft calculation before review.')
            ->assertSee('Initial review note: check calculus integrals.');
    }

    public function test_parent_can_switch_children_and_view_individual_status_marks_and_feedback(): void
    {
        // Student 2 has submission for Assignment 1 (Failed: 40 / 100, pass is 50)
        AssignmentSubmission::create([
            'institution_id' => $this->institution->id,
            'assignment_id' => $this->assignment1->id,
            'student_id' => $this->student2->id,
            'submitted_by' => $this->studentUser2->id,
            'status' => AssignmentSubmissionStatus::Graded,
            'version' => 1,
            'is_late' => false,
            'marks' => 40,
            'feedback' => 'Notation was confusing. Please review flux formulas.',
            'reviewed_by' => $this->teacher->id,
            'reviewed_at' => now()->subDay(),
            'submitted_at' => now()->subDays(2),
        ]);

        // Parent switches to Child 1 (Isaac: Passed 94/100)
        Livewire::actingAs($this->parentUser)
            ->test(AssignmentList::class, ['student' => $this->student1->id])
            ->assertSee('Isaac Newton')
            ->assertSee('94 / 100')
            ->assertSee('Passed')
            ->assertSee('Flawless geometrical proofs of inverse square law.')
            // Parent switches to Child 2 (Gottfried: Did Not Pass 40/100)
            ->call('selectStudent', $this->student2->id)
            ->assertSee('Gottfried Leibniz')
            ->assertSee('40 / 100')
            ->assertSee('Did Not Pass')
            ->assertSee('Notation was confusing. Please review flux formulas.');
    }

    public function test_multi_institution_isolation_prevents_viewing_assignments_from_other_institution(): void
    {
        $otherClass = SchoolClass::create([
            'institution_id' => $this->otherInstitution->id,
            'title' => 'Pacific Physics',
            'kind' => ClassKind::InstituteClass,
            'fee_amount' => 5000.00,
            'capacity' => 20,
            'status' => 'active',
        ]);

        $otherAssignment = Assignment::create([
            'institution_id' => $this->otherInstitution->id,
            'class_id' => $otherClass->id,
            'created_by' => $this->teacher->id,
            'title' => 'Foreign Pacific Assignment',
            'status' => AssignmentStatus::Published,
            'allowed_file_types' => ['pdf'],
            'max_file_size_mb' => 10,
            'deadline' => now()->addDays(2),
            'allow_late' => false,
            'resubmission_limit' => 1,
            'total_marks' => 50,
        ]);

        // 1. Assignment from other institution is NOT listed in Student 1's coursework list
        Livewire::actingAs($this->studentUser1)
            ->test(AssignmentList::class)
            ->assertDontSee('Foreign Pacific Assignment');

        // 2. Direct show view of other institution assignment throws 404
        $this->actingAs($this->studentUser1)
            ->get(route('assignments.show', $otherAssignment))
            ->assertNotFound();
    }
}
