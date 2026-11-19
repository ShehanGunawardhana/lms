<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Livewire\Admin\AcademicRolloverManager;
use App\Models\AcademicYear;
use App\Models\ClassSchedule;
use App\Models\Enrollment;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Services\AcademicRolloverService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class AcademicRolloverTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected User $admin;

    protected User $teacher;

    protected User $studentUser;

    protected AcademicYear $sourceYear;

    protected AcademicYear $targetYear;

    protected SchoolClass $class1;

    protected SchoolClass $class2;

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

        $this->sourceYear = AcademicYear::create([
            'institution_id' => $this->institution->id,
            'name' => '2026 Academic Year',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
            'status' => 'active',
            'is_current' => true,
        ]);

        $this->targetYear = AcademicYear::create([
            'institution_id' => $this->institution->id,
            'name' => '2027 Academic Year',
            'starts_on' => '2027-01-01',
            'ends_on' => '2027-12-31',
            'status' => 'upcoming',
            'is_current' => false,
        ]);

        // Class 1 in source year
        $this->class1 = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'academic_year_id' => $this->sourceYear->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Pure Mathematics 2026',
            'subject' => 'Mathematics',
            'kind' => 'institute_class',
            'fee_amount' => 12500,
            'capacity' => 50,
            'status' => 'active',
        ]);

        // Class 1 timetable slot
        ClassSchedule::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->class1->id,
            'weekday' => 1, // Monday
            'starts_at' => '08:30:00',
            'ends_at' => '11:30:00',
            'room' => 'Hall 201',
        ]);

        // Class 2 in source year
        $this->class2 = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'academic_year_id' => $this->sourceYear->id,
            'title' => 'Nursery Little Pandas',
            'subject' => 'Early Childhood',
            'kind' => 'nursery_group',
            'capacity' => 20,
            'status' => 'active',
        ]);
    }

    public function test_guest_or_non_admin_cannot_access_rollover_wizard(): void
    {
        $this->get(route('admin.academics.rollover'))
            ->assertRedirect('/login');

        $this->actingAs($this->teacher)
            ->get(route('admin.academics.rollover'))
            ->assertForbidden();

        $this->actingAs($this->studentUser)
            ->get(route('admin.academics.rollover'))
            ->assertForbidden();
    }

    public function test_admin_can_render_academic_rollover_wizard(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.academics.rollover'));

        $response->assertOk();
        $response->assertSee('Year-End Academic Rollover Wizard');
        $response->assertSee('Pure Mathematics 2026');
        $response->assertSee('Nursery Little Pandas');
        $response->assertSee('Execute Year-End Rollover');
    }

    public function test_rollover_service_duplicates_classes_schedules_and_archives_source_year(): void
    {
        $student = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => 'STU-99',
            'first_name' => 'Kavindu',
            'last_name' => 'Perera',
            'dob' => '2008-01-01',
            'status' => 'active',
            'kind' => 'institute',
        ]);

        Enrollment::create([
            'institution_id' => $this->institution->id,
            'student_id' => $student->id,
            'class_id' => $this->class1->id,
            'status' => 'active',
            'source' => 'admin',
            'enrolled_at' => now(),
        ]);

        $service = app(AcademicRolloverService::class);
        $result = $service->performRollover($this->sourceYear, $this->targetYear, [
            'copy_schedules' => true,
            'rollover_students' => true,
            'archive_source_year' => true,
            'archive_source_classes' => true,
            'complete_source_enrollments' => true,
            'set_target_as_current' => true,
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame(2, $result['copied_classes']);
        $this->assertSame(1, $result['copied_schedules']);
        $this->assertSame(1, $result['rolled_over_students']);

        // Target year is now active and current
        $this->assertTrue($this->targetYear->fresh()->is_current);
        $this->assertSame('active', $this->targetYear->fresh()->status);

        // Source year is now archived and no longer current
        $this->assertFalse($this->sourceYear->fresh()->is_current);
        $this->assertSame('archived', $this->sourceYear->fresh()->status);

        // Source classes are archived
        $this->assertSame('archived', $this->class1->fresh()->status);
        $this->assertSame('archived', $this->class2->fresh()->status);

        // New classes exist in target year
        $newClasses = SchoolClass::where('academic_year_id', $this->targetYear->id)->get();
        $this->assertCount(2, $newClasses);

        $newClass1 = $newClasses->firstWhere('title', 'Pure Mathematics 2026');
        $this->assertNotNull($newClass1);
        $this->assertSame('active', $newClass1->status);
        $this->assertSame($this->teacher->id, $newClass1->teacher_id);

        // Schedule was copied
        $copiedSlot = ClassSchedule::where('class_id', $newClass1->id)->first();
        $this->assertNotNull($copiedSlot);
        $this->assertSame(1, $copiedSlot->weekday);
        $this->assertSame('08:30:00', $copiedSlot->starts_at);
        $this->assertSame('Hall 201', $copiedSlot->room);

        // Student was rolled over into new class
        $newEnrollment = Enrollment::where('class_id', $newClass1->id)
            ->where('student_id', $student->id)
            ->first();
        $this->assertNotNull($newEnrollment);
        $this->assertSame('active', $newEnrollment->status->value);
        $this->assertSame('rollover', $newEnrollment->source);

        // Audit log created
        $activity = Activity::where('event', 'academic.rollover')->first();
        $this->assertNotNull($activity);
    }

    public function test_livewire_rollover_workflow_execution(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(AcademicRolloverManager::class)
            ->set('sourceYearId', $this->sourceYear->id)
            ->set('targetYearId', $this->targetYear->id)
            ->assertCount('selectedClassIds', 2)
            ->set('copySchedules', true)
            ->set('rolloverStudents', false)
            ->call('executeRollover')
            ->assertHasNoErrors()
            ->assertSee('Academic Rollover Completed Successfully!')
            ->assertSee('Classes Created');

        $this->assertDatabaseHas('school_classes', [
            'academic_year_id' => $this->targetYear->id,
            'title' => 'Pure Mathematics 2026',
            'status' => 'active',
        ]);
    }

    public function test_quick_create_target_year_modal(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(AcademicRolloverManager::class)
            ->call('openCreateYearModal')
            ->assertSet('showCreateYearModal', true)
            ->set('newYearName', '2028 Academic Year')
            ->set('newYearStartsOn', '2028-01-01')
            ->set('newYearEndsOn', '2028-12-31')
            ->call('createTargetYear')
            ->assertHasNoErrors()
            ->assertSet('showCreateYearModal', false);

        $newYear = AcademicYear::where('name', '2028 Academic Year')->first();
        $this->assertNotNull($newYear);
        $this->assertSame('upcoming', $newYear->status);
    }

    public function test_rollover_fails_if_source_and_target_are_same_year(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(AcademicRolloverManager::class)
            ->set('sourceYearId', $this->sourceYear->id)
            ->set('targetYearId', $this->sourceYear->id)
            ->call('executeRollover')
            ->assertHasErrors(['targetYearId']);
    }

    public function test_multi_institution_isolation_in_rollover(): void
    {
        $otherInstitution = Institution::create([
            'name' => 'Other Institution',
            'slug' => 'other-inst',
            'code' => 'OI',
            'type' => 'institute',
            'is_active' => true,
        ]);

        $otherYear = AcademicYear::create([
            'institution_id' => $otherInstitution->id,
            'name' => 'Other 2027 Year',
            'starts_on' => '2027-01-01',
            'ends_on' => '2027-12-31',
        ]);

        $this->actingAs($this->admin);

        Livewire::test(AcademicRolloverManager::class)
            ->assertDontSee('Other 2027 Year');
    }
}
