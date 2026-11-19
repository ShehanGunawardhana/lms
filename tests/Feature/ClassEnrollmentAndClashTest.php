<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Livewire\Admin\ClassEnrollmentManager;
use App\Models\AcademicYear;
use App\Models\ClassSchedule;
use App\Models\Enrollment;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Services\EnrollmentService;
use App\Services\ScheduleClashDetector;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ClassEnrollmentAndClashTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected User $admin;

    protected User $teacher;

    protected User $studentUser;

    protected SchoolClass $class;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institution = Institution::create([
            'name' => 'Royal Academic Institute',
            'slug' => 'royal-academic',
            'code' => 'RAI',
            'type' => 'institute',
            'is_active' => true,
        ]);

        session(['current_institution_id' => $this->institution->id]);

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'two_factor_secret' => 'SECRET',
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        ]);
        $this->admin->assignRole(RoleName::Admin->value);

        $this->teacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $this->teacher->assignRole(RoleName::Teacher->value);

        $this->studentUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $this->studentUser->assignRole(RoleName::Student->value);

        $this->student = Student::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->studentUser->id,
            'student_code' => 'STU-001',
            'first_name' => 'Amara',
            'last_name' => 'Perera',
            'dob' => '2008-03-15',
            'status' => 'active',
            'kind' => 'institute',
        ]);

        $year = AcademicYear::create([
            'institution_id' => $this->institution->id,
            'name' => '2026 Academic Year',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
            'is_current' => true,
        ]);

        $this->class = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'academic_year_id' => $year->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Pure Mathematics 2026',
            'subject' => 'Mathematics',
            'kind' => 'institute_class',
            'fee_amount' => 12500,
            'capacity' => 2, // low capacity for easy testing
            'status' => 'active',
        ]);
    }

    public function test_guest_or_non_admin_cannot_access_class_enrollments(): void
    {
        $this->get(route('admin.classes.enrollments', $this->class->id))
            ->assertRedirect('/login');

        $this->actingAs($this->teacher)
            ->get(route('admin.classes.enrollments', $this->class->id))
            ->assertForbidden();

        $this->actingAs($this->studentUser)
            ->get(route('admin.classes.enrollments', $this->class->id))
            ->assertForbidden();
    }

    public function test_admin_can_render_class_enrollment_manager(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.classes.enrollments', $this->class->id));

        $response->assertOk();
        $response->assertSee('Class Student Roster &amp; Enrollments', false);
        $response->assertSee('Pure Mathematics 2026');
        $response->assertSee('Active Students');
        $response->assertSee('Waiting List');
        $response->assertSee('Dropped / Inactive');
    }

    public function test_admin_can_enroll_student_in_class_under_capacity(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ClassEnrollmentManager::class, ['classId' => $this->class->id])
            ->call('openEnrollModal')
            ->assertSet('showEnrollModal', true)
            ->call('selectStudent', $this->student->id)
            ->assertSet('selectedStudentId', $this->student->id)
            ->set('enrollSource', 'admin')
            ->set('enrollNotes', 'Enrolled during orientation')
            ->call('enrollStudent')
            ->assertHasNoErrors()
            ->assertSet('showEnrollModal', false);

        $enrollment = Enrollment::where('class_id', $this->class->id)
            ->where('student_id', $this->student->id)
            ->first();

        $this->assertNotNull($enrollment);
        $this->assertSame('active', $enrollment->status->value);
        $this->assertSame('admin', $enrollment->source);
        $this->assertSame('Enrolled during orientation', $enrollment->notes);

        $activity = Activity::where('event', 'enrollment.created')->first();
        $this->assertNotNull($activity);
    }

    public function test_capacity_limit_enforces_waiting_list(): void
    {
        $this->actingAs($this->admin);

        // Student 1
        $student1 = $this->student;

        // Student 2
        $student2 = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => 'STU-002',
            'first_name' => 'Nimal',
            'last_name' => 'Silva',
            'dob' => '2008-04-12',
            'status' => 'active',
            'kind' => 'institute',
        ]);

        // Student 3 (should be waitlisted)
        $student3 = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => 'STU-003',
            'first_name' => 'Kasun',
            'last_name' => 'Rajapaksa',
            'dob' => '2008-06-20',
            'status' => 'active',
            'kind' => 'institute',
        ]);

        $service = app(EnrollmentService::class);

        // Enroll student 1 and 2 (fills capacity of 2)
        $res1 = $service->enroll($this->class, $student1);
        $this->assertTrue($res1['success']);
        $this->assertSame('active', $res1['status']);

        $res2 = $service->enroll($this->class, $student2);
        $this->assertTrue($res2['success']);
        $this->assertSame('active', $res2['status']);

        // Enroll student 3 -> should be waitlisted
        $res3 = $service->enroll($this->class, $student3);
        $this->assertTrue($res3['success']);
        $this->assertSame('waitlisted', $res3['status']);

        $waitlistedEnrollment = Enrollment::where('class_id', $this->class->id)
            ->where('student_id', $student3->id)
            ->first();

        $this->assertNotNull($waitlistedEnrollment);
        $this->assertSame('waitlisted', $waitlistedEnrollment->status->value);

        // UI renders waitlisted student
        Livewire::test(ClassEnrollmentManager::class, ['classId' => $this->class->id])
            ->set('activeTab', 'waitlisted')
            ->assertSee('Kasun Rajapaksa')
            ->assertSee('#1');
    }

    public function test_admin_can_promote_waitlisted_student_when_seat_opens(): void
    {
        $this->actingAs($this->admin);

        $student1 = $this->student;
        $student2 = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => 'STU-002',
            'first_name' => 'Nimal',
            'last_name' => 'Silva',
            'dob' => '2008-04-12',
            'status' => 'active',
            'kind' => 'institute',
        ]);
        $student3 = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => 'STU-003',
            'first_name' => 'Kasun',
            'last_name' => 'Rajapaksa',
            'dob' => '2008-06-20',
            'status' => 'active',
            'kind' => 'institute',
        ]);

        $service = app(EnrollmentService::class);
        $service->enroll($this->class, $student1);
        $service->enroll($this->class, $student2);
        $res3 = $service->enroll($this->class, $student3);

        $waitlistedEnrollment = $res3['enrollment'];

        // Promoting while full should fail
        Livewire::test(ClassEnrollmentManager::class, ['classId' => $this->class->id])
            ->call('promoteWaitlisted', $waitlistedEnrollment->id);

        $this->assertSame('waitlisted', $waitlistedEnrollment->fresh()->status->value);

        // Drop student 1 to free a seat
        $enrollment1 = Enrollment::where('student_id', $student1->id)->where('class_id', $this->class->id)->first();
        $service->drop($enrollment1, 'Student withdrew');

        // Now promoting should succeed
        Livewire::test(ClassEnrollmentManager::class, ['classId' => $this->class->id])
            ->call('promoteWaitlisted', $waitlistedEnrollment->id);

        $this->assertSame('active', $waitlistedEnrollment->fresh()->status->value);

        $activity = Activity::where('event', 'enrollment.promoted')->first();
        $this->assertNotNull($activity);
    }

    public function test_admin_can_drop_student_with_reason(): void
    {
        $this->actingAs($this->admin);

        $service = app(EnrollmentService::class);
        $res = $service->enroll($this->class, $this->student);
        $enrollment = $res['enrollment'];

        Livewire::test(ClassEnrollmentManager::class, ['classId' => $this->class->id])
            ->call('openDropModal', $enrollment->id)
            ->assertSet('showDropModal', true)
            ->set('dropReason', 'Relocating to another city')
            ->call('confirmDrop')
            ->assertSet('showDropModal', false);

        $this->assertSame('dropped', $enrollment->fresh()->status->value);
        $this->assertSame('Relocating to another city', $enrollment->fresh()->notes);
        $this->assertNotNull($enrollment->fresh()->dropped_at);

        $activity = Activity::where('event', 'enrollment.dropped')->first();
        $this->assertNotNull($activity);
    }

    public function test_schedule_clash_detection_warns_on_overlapping_classes(): void
    {
        $this->actingAs($this->admin);

        // 1. Add weekly schedule slot to Class A (Pure Maths): Monday 08:30 - 11:30
        ClassSchedule::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->class->id,
            'weekday' => 1, // Monday
            'starts_at' => '08:30:00',
            'ends_at' => '11:30:00',
            'room' => 'Hall 201',
        ]);

        // 2. Create Class B (Applied Physics) with overlapping Monday slot: 10:00 - 12:00
        $classB = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'academic_year_id' => $this->class->academic_year_id,
            'title' => 'Applied Physics 2026',
            'subject' => 'Physics',
            'kind' => 'institute_class',
            'capacity' => 30,
            'status' => 'active',
        ]);

        ClassSchedule::create([
            'institution_id' => $this->institution->id,
            'class_id' => $classB->id,
            'weekday' => 1, // Monday
            'starts_at' => '10:00:00',
            'ends_at' => '12:00:00',
            'room' => 'Physics Lab',
        ]);

        // 3. Enroll student actively in Class A
        $service = app(EnrollmentService::class);
        $service->enroll($this->class, $this->student);

        // 4. Clash detector service directly
        $detector = app(ScheduleClashDetector::class);
        $clashes = $detector->detectClashes($this->student, $classB);

        $this->assertCount(1, $clashes);
        $this->assertSame($this->class->id, $clashes[0]['conflicting_class_id']);
        $this->assertSame('Pure Mathematics 2026', $clashes[0]['conflicting_class_title']);
        $this->assertSame('Monday', $clashes[0]['weekday_name']);

        // 5. Test Livewire component live clash warning on Class B
        Livewire::test(ClassEnrollmentManager::class, ['classId' => $classB->id])
            ->call('openEnrollModal')
            ->call('selectStudent', $this->student->id)
            ->assertCount('detectedClashes', 1)
            ->assertSee('Schedule Clash Warning!')
            ->assertSee('Pure Mathematics 2026')
            // Attempt enrollment without acknowledging clash fails
            ->call('enrollStudent')
            ->assertHasErrors(['acknowledgeClash'])
            // Check acknowledge box and proceed
            ->set('acknowledgeClash', true)
            ->call('enrollStudent')
            ->assertHasNoErrors();

        // Student is now enrolled in Class B despite clash override
        $this->assertTrue(
            Enrollment::where('class_id', $classB->id)
                ->where('student_id', $this->student->id)
                ->where('status', 'active')
                ->exists()
        );
    }

    public function test_non_overlapping_classes_do_not_trigger_clash(): void
    {
        // Class A: Monday 08:30 - 11:30
        ClassSchedule::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->class->id,
            'weekday' => 1, // Monday
            'starts_at' => '08:30:00',
            'ends_at' => '11:30:00',
        ]);

        // Class C: Tuesday 08:30 - 11:30 (Different day)
        $classC = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'academic_year_id' => $this->class->academic_year_id,
            'title' => 'Chemistry Revision',
            'subject' => 'Chemistry',
            'kind' => 'institute_class',
            'capacity' => 30,
            'status' => 'active',
        ]);

        ClassSchedule::create([
            'institution_id' => $this->institution->id,
            'class_id' => $classC->id,
            'weekday' => 2, // Tuesday
            'starts_at' => '08:30:00',
            'ends_at' => '11:30:00',
        ]);

        $service = app(EnrollmentService::class);
        $service->enroll($this->class, $this->student);

        $detector = app(ScheduleClashDetector::class);
        $clashes = $detector->detectClashes($this->student, $classC);

        $this->assertEmpty($clashes);
    }

    public function test_multi_institution_isolation(): void
    {
        $otherInstitution = Institution::create([
            'name' => 'Other Institution',
            'slug' => 'other-inst',
            'code' => 'OI',
            'type' => 'institute',
            'is_active' => true,
        ]);

        $otherClass = SchoolClass::create([
            'institution_id' => $otherInstitution->id,
            'title' => 'Other School Class',
            'subject' => 'Art',
            'kind' => 'institute_class',
            'capacity' => 20,
            'status' => 'active',
        ]);

        $this->actingAs($this->admin);

        // Attempting to access other institution's class enrollments returns 404
        $this->get(route('admin.classes.enrollments', $otherClass->id))
            ->assertNotFound();
    }
}
