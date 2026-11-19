<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Livewire\Admin\AcademicStructureManager;
use App\Livewire\Admin\ClassScheduleManager;
use App\Models\AcademicYear;
use App\Models\ClassSchedule;
use App\Models\ClassSession;
use App\Models\Enrollment;
use App\Models\Holiday;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Notifications\SessionCancelledNotification;
use App\Notifications\SessionRescheduledNotification;
use App\Services\ScheduleGenerator;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class HolidaysAndSessionCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected User $admin;

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
    }

    public function test_admin_can_view_and_create_holidays_in_academic_structure_manager(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(AcademicStructureManager::class)
            ->set('activeTab', 'holidays')
            ->assertSee('Holidays &amp; Closures', false)
            ->set('holidayName', 'National Independence Day')
            ->set('holidayDate', '2026-02-04')
            ->set('holidayAffects', 'all')
            ->set('holidayIsRecurring', true)
            ->set('holidayDescription', 'National public holiday')
            ->call('saveHoliday')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('holidays', [
            'institution_id' => $this->institution->id,
            'name' => 'National Independence Day',
            'date' => '2026-02-04 00:00:00',
            'affects' => 'all',
            'is_recurring' => 1,
        ]);
    }

    public function test_holiday_creation_with_apply_to_existing_sessions_automatically_cancels_matching_sessions(): void
    {
        $this->actingAs($this->admin);

        $year = AcademicYear::create([
            'institution_id' => $this->institution->id,
            'name' => '2026 Academic Year',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
            'is_current' => true,
        ]);

        $class = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'academic_year_id' => $year->id,
            'title' => 'Pure Mathematics 2026',
            'subject' => 'Mathematics',
            'kind' => 'institute_class',
            'fee_amount' => 12500,
            'capacity' => 50,
            'status' => 'active',
        ]);

        $session = ClassSession::create([
            'institution_id' => $this->institution->id,
            'class_id' => $class->id,
            'title' => 'Math Session 1',
            'date' => '2026-05-01',
            'starts_at' => '08:30:00',
            'ends_at' => '11:30:00',
            'status' => 'scheduled',
        ]);

        Livewire::test(AcademicStructureManager::class)
            ->set('activeTab', 'holidays')
            ->set('holidayName', 'May Day')
            ->set('holidayDate', '2026-05-01')
            ->set('holidayAffects', 'all')
            ->set('applyToExistingSessions', true)
            ->call('saveHoliday');

        $this->assertSame('cancelled', $session->fresh()->status);
        $this->assertSame('Holiday: May Day', $session->fresh()->cancel_reason);
    }

    public function test_holiday_affecting_only_nursery_does_not_cancel_institute_class_sessions(): void
    {
        $this->actingAs($this->admin);

        $year = AcademicYear::create([
            'institution_id' => $this->institution->id,
            'name' => '2026 Academic Year',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
            'is_current' => true,
        ]);

        $instituteClass = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'academic_year_id' => $year->id,
            'title' => 'Advanced Physics',
            'subject' => 'Physics',
            'kind' => 'institute_class',
            'fee_amount' => 15000,
            'capacity' => 40,
            'status' => 'active',
        ]);

        $instituteSession = ClassSession::create([
            'institution_id' => $this->institution->id,
            'class_id' => $instituteClass->id,
            'title' => 'Physics Session',
            'date' => '2026-06-15',
            'starts_at' => '10:00:00',
            'ends_at' => '12:00:00',
            'status' => 'scheduled',
        ]);

        Livewire::test(AcademicStructureManager::class)
            ->set('activeTab', 'holidays')
            ->set('holidayName', 'Nursery Teacher Workshop')
            ->set('holidayDate', '2026-06-15')
            ->set('holidayAffects', 'nursery')
            ->set('applyToExistingSessions', true)
            ->call('saveHoliday');

        // Institute session remains scheduled
        $this->assertSame('scheduled', $instituteSession->fresh()->status);
    }

    public function test_cancelling_session_manually_requires_reason_and_sends_notification(): void
    {
        Notification::fake();

        $this->actingAs($this->admin);

        $year = AcademicYear::create([
            'institution_id' => $this->institution->id,
            'name' => '2026 Academic Year',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
            'is_current' => true,
        ]);

        $class = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'academic_year_id' => $year->id,
            'title' => 'English Literature',
            'subject' => 'English',
            'kind' => 'institute_class',
            'fee_amount' => 10000,
            'capacity' => 30,
            'status' => 'active',
        ]);

        // Student and enrollment
        $studentUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $studentUser->assignRole(RoleName::Student->value);

        $student = Student::create([
            'institution_id' => $this->institution->id,
            'user_id' => $studentUser->id,
            'student_code' => 'STU-001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'dob' => '2008-05-10',
            'status' => 'active',
            'kind' => 'institute',
        ]);

        Enrollment::create([
            'institution_id' => $this->institution->id,
            'student_id' => $student->id,
            'class_id' => $class->id,
            'status' => 'active',
            'source' => 'direct',
            'enrolled_at' => now(),
        ]);

        $session = ClassSession::create([
            'institution_id' => $this->institution->id,
            'class_id' => $class->id,
            'title' => 'Literature Session 1',
            'date' => '2026-07-10',
            'starts_at' => '09:00:00',
            'ends_at' => '11:00:00',
            'status' => 'scheduled',
        ]);

        // Attempt cancellation without reason should fail validation
        Livewire::test(ClassScheduleManager::class, ['classId' => $class->id])
            ->call('openCancelModal', $session->id)
            ->set('cancelReason', '')
            ->call('cancelSession')
            ->assertHasErrors(['cancelReason']);

        // Cancel with valid reason
        Livewire::test(ClassScheduleManager::class, ['classId' => $class->id])
            ->call('openCancelModal', $session->id)
            ->set('cancelReason', 'Lecturer medical emergency')
            ->call('cancelSession')
            ->assertHasNoErrors();

        $this->assertSame('cancelled', $session->fresh()->status);
        $this->assertSame('Lecturer medical emergency', $session->fresh()->cancel_reason);

        Notification::assertSentTo($studentUser, SessionCancelledNotification::class, function ($notification) {
            return $notification->reason === 'Lecturer medical emergency';
        });
    }

    public function test_rescheduling_session_creates_new_session_and_notifies_enrolled_students(): void
    {
        Notification::fake();

        $this->actingAs($this->admin);

        $year = AcademicYear::create([
            'institution_id' => $this->institution->id,
            'name' => '2026 Academic Year',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
            'is_current' => true,
        ]);

        $class = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'academic_year_id' => $year->id,
            'title' => 'Chemistry Practical',
            'subject' => 'Chemistry',
            'kind' => 'institute_class',
            'fee_amount' => 14000,
            'capacity' => 25,
            'status' => 'active',
        ]);

        $studentUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $studentUser->assignRole(RoleName::Student->value);

        $student = Student::create([
            'institution_id' => $this->institution->id,
            'user_id' => $studentUser->id,
            'student_code' => 'STU-002',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'dob' => '2008-08-20',
            'status' => 'active',
            'kind' => 'institute',
        ]);

        Enrollment::create([
            'institution_id' => $this->institution->id,
            'student_id' => $student->id,
            'class_id' => $class->id,
            'status' => 'active',
            'source' => 'direct',
            'enrolled_at' => now(),
        ]);

        $originalSession = ClassSession::create([
            'institution_id' => $this->institution->id,
            'class_id' => $class->id,
            'title' => 'Chem Session 1',
            'date' => '2026-08-01',
            'starts_at' => '09:00:00',
            'ends_at' => '11:00:00',
            'status' => 'scheduled',
        ]);

        Livewire::test(ClassScheduleManager::class, ['classId' => $class->id])
            ->call('openRescheduleModal', $originalSession->id)
            ->set('rescheduleDate', '2026-08-05')
            ->set('rescheduleStartsAt', '14:00')
            ->set('rescheduleEndsAt', '16:00')
            ->set('rescheduleReason', 'Special makeup session due to lab maintenance')
            ->call('rescheduleSession')
            ->assertHasNoErrors();

        // Original session marked cancelled
        $this->assertSame('cancelled', $originalSession->fresh()->status);
        $this->assertStringContainsString('Special makeup session due to lab maintenance', $originalSession->fresh()->cancel_reason);

        // New replacement session created with link
        $newSession = ClassSession::where('rescheduled_from_id', $originalSession->id)->first();
        $this->assertNotNull($newSession);
        $this->assertSame('2026-08-05', $newSession->date->format('Y-m-d'));
        $this->assertSame('14:00:00', $newSession->starts_at);
        $this->assertSame('16:00:00', $newSession->ends_at);
        $this->assertSame('scheduled', $newSession->status);

        Notification::assertSentTo($studentUser, SessionRescheduledNotification::class);
    }

    public function test_schedule_generator_automatically_marks_sessions_falling_on_holidays_as_cancelled(): void
    {
        $year = AcademicYear::create([
            'institution_id' => $this->institution->id,
            'name' => '2026 Academic Year',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
            'is_current' => true,
        ]);

        $class = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'academic_year_id' => $year->id,
            'title' => 'Biology Revision',
            'subject' => 'Biology',
            'kind' => 'institute_class',
            'fee_amount' => 12000,
            'capacity' => 45,
            'status' => 'active',
        ]);

        // Create Monday slot (weekday = 1). In September 2026: 2026-09-07 is Monday
        ClassSchedule::create([
            'institution_id' => $this->institution->id,
            'class_id' => $class->id,
            'weekday' => 1,
            'starts_at' => '08:30:00',
            'ends_at' => '10:30:00',
            'room' => 'Bio Lab',
        ]);

        // Create holiday on Monday 2026-09-07
        Holiday::create([
            'institution_id' => $this->institution->id,
            'name' => 'Adhi Poson Poya Holiday',
            'date' => '2026-09-07',
            'affects' => 'all',
            'is_recurring' => false,
        ]);

        $generator = new ScheduleGenerator;
        $result = $generator->generateForClass($class, '2026-09-01', '2026-09-30');

        $this->assertGreaterThan(0, $result['created']);

        // Verify session on 2026-09-07 is automatically cancelled with holiday reason
        $holidaySession = ClassSession::where('class_id', $class->id)
            ->whereDate('date', '2026-09-07')
            ->first();

        $this->assertNotNull($holidaySession);
        $this->assertSame('cancelled', $holidaySession->status);
        $this->assertSame('Holiday: Adhi Poson Poya Holiday', $holidaySession->cancel_reason);
    }

    public function test_holidays_are_isolated_by_institution(): void
    {
        $otherInstitution = Institution::create([
            'name' => 'Other Academy',
            'slug' => 'other-academy',
            'code' => 'OA',
            'type' => 'institute',
            'is_active' => true,
        ]);

        Holiday::create([
            'institution_id' => $otherInstitution->id,
            'name' => 'Other Institution Founder Day',
            'date' => '2026-10-10',
            'affects' => 'all',
            'is_recurring' => false,
        ]);

        $this->actingAs($this->admin);

        Livewire::test(AcademicStructureManager::class)
            ->set('activeTab', 'holidays')
            ->assertDontSee('Other Institution Founder Day');
    }
}
