<?php

namespace Tests\Feature;

use App\Enums\ClassKind;
use App\Enums\RoleName;
use App\Livewire\Admin\ClassManager;
use App\Models\AcademicYear;
use App\Models\Batch;
use App\Models\Enrollment;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ClassManagerTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected User $admin;

    protected User $teacher;

    protected User $student;

    protected AcademicYear $academicYear;

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
            'name' => 'Prof. Charles Xavier',
        ]);
        $this->teacher->assignRole(RoleName::Teacher->value);

        $this->student = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $this->student->assignRole(RoleName::Student->value);

        $this->academicYear = AcademicYear::create([
            'institution_id' => $this->institution->id,
            'name' => '2026 Academic Year',
            'code' => 'AY-2026',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
            'is_current' => true,
            'status' => 'active',
        ]);
    }

    public function test_guest_or_non_admin_cannot_access_class_manager(): void
    {
        $this->get(route('admin.classes'))->assertRedirect('/login');

        $this->actingAs($this->teacher)
            ->get(route('admin.classes'))
            ->assertForbidden();

        $this->actingAs($this->student)
            ->get(route('admin.classes'))
            ->assertForbidden();
    }

    public function test_admin_without_two_factor_is_redirected(): void
    {
        $unconfirmedAdmin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);
        $unconfirmedAdmin->assignRole(RoleName::Admin->value);

        $this->actingAs($unconfirmedAdmin)
            ->get(route('admin.classes'))
            ->assertRedirect(route('two-factor.setup'));
    }

    public function test_admin_with_two_factor_can_render_class_manager(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.classes'));

        $response->assertOk();
        $response->assertSee('Classes &amp; Nursery Groups', false);
        $response->assertSee('Total Classes');
        $response->assertSee('Institute Classes');
        $response->assertSee('Nursery Groups');
    }

    public function test_admin_can_create_institute_class_with_fee_capacity_and_lecturer(): void
    {
        $this->actingAs($this->admin);

        $batch = Batch::create([
            'institution_id' => $this->institution->id,
            'academic_year_id' => $this->academicYear->id,
            'name' => '2026 A/L Bio Science',
            'code' => 'AL-2026-BIO',
            'kind' => 'institute_batch',
            'status' => 'active',
        ]);

        Livewire::test(ClassManager::class)
            ->call('openCreateModal')
            ->assertSet('showModal', true)
            ->set('title', 'Advanced Molecular Biology')
            ->set('subject', 'Biology')
            ->set('kind', ClassKind::InstituteClass->value)
            ->set('academic_year_id', $this->academicYear->id)
            ->set('batch_id', $batch->id)
            ->set('teacher_id', $this->teacher->id)
            ->set('fee_amount', '17500.00')
            ->set('capacity', 60)
            ->set('room', 'Science Auditorium 1')
            ->set('online_url', 'https://zoom.us/j/999888777')
            ->set('description', 'Comprehensive cellular and molecular genetics.')
            ->set('status', 'active')
            ->call('saveClass')
            ->assertSet('showModal', false)
            ->assertHasNoErrors();

        $class = SchoolClass::where('title', 'Advanced Molecular Biology')->first();

        $this->assertNotNull($class);
        $this->assertSame('Biology', $class->subject);
        $this->assertEquals(17500.00, $class->fee_amount);
        $this->assertSame(60, $class->capacity);
        $this->assertSame($this->teacher->id, $class->teacher_id);
        $this->assertSame($batch->id, $class->batch_id);
        $this->assertSame('Science Auditorium 1', $class->room);

        // Audit log check
        $activity = Activity::where('event', 'class.created')
            ->where('subject_id', $class->id)
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame($this->admin->id, $activity->causer_id);
    }

    public function test_admin_can_create_nursery_group(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ClassManager::class)
            ->call('openCreateModal')
            ->set('title', 'Playgroup Butterflies')
            ->set('kind', ClassKind::NurseryGroup->value)
            ->set('capacity', 15)
            ->set('fee_amount', '0.00')
            ->set('room', 'Butterflies Activity Hall')
            ->set('status', 'active')
            ->call('saveClass')
            ->assertHasNoErrors();

        $group = SchoolClass::where('title', 'Playgroup Butterflies')->first();

        $this->assertNotNull($group);
        $this->assertSame(ClassKind::NurseryGroup->value, $group->kind instanceof ClassKind ? $group->kind->value : $group->kind);
        $this->assertSame(15, $group->capacity);
        $this->assertEquals(0.00, $group->fee_amount);
    }

    public function test_admin_can_update_class(): void
    {
        $this->actingAs($this->admin);

        $class = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'academic_year_id' => $this->academicYear->id,
            'title' => 'Pure Math 2026',
            'kind' => ClassKind::InstituteClass->value,
            'fee_amount' => 12000,
            'capacity' => 40,
            'room' => 'Hall 201',
            'status' => 'active',
        ]);

        Livewire::test(ClassManager::class)
            ->call('openEditModal', $class->id)
            ->assertSet('editingClassId', $class->id)
            ->set('title', 'Pure Mathematics Advanced 2026')
            ->set('fee_amount', '14000.00')
            ->set('capacity', 50)
            ->set('room', 'Hall 305')
            ->call('saveClass')
            ->assertHasNoErrors();

        $updated = $class->fresh();
        $this->assertSame('Pure Mathematics Advanced 2026', $updated->title);
        $this->assertEquals(14000.00, $updated->fee_amount);
        $this->assertSame(50, $updated->capacity);
        $this->assertSame('Hall 305', $updated->room);
    }

    public function test_admin_can_toggle_class_status(): void
    {
        $this->actingAs($this->admin);

        $class = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'title' => 'Applied Statistics',
            'kind' => ClassKind::InstituteClass->value,
            'fee_amount' => 8000,
            'capacity' => 30,
            'status' => 'active',
        ]);

        Livewire::test(ClassManager::class)
            ->call('toggleStatus', $class->id);

        $this->assertSame('archived', $class->fresh()->status);

        Livewire::test(ClassManager::class)
            ->call('toggleStatus', $class->id);

        $this->assertSame('active', $class->fresh()->status);
    }

    public function test_admin_cannot_delete_class_with_active_enrollments(): void
    {
        $this->actingAs($this->admin);

        $class = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'title' => 'Astronomy 101',
            'kind' => ClassKind::InstituteClass->value,
            'fee_amount' => 10000,
            'capacity' => 20,
            'status' => 'active',
        ]);

        $student = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => 'INS-26-0001',
            'first_name' => 'Galileo',
            'last_name' => 'Galilei',
            'dob' => '2008-02-15',
            'status' => 'active',
        ]);

        Enrollment::create([
            'institution_id' => $this->institution->id,
            'student_id' => $student->id,
            'class_id' => $class->id,
            'status' => 'active',
        ]);

        Livewire::test(ClassManager::class)
            ->call('deleteClass', $class->id)
            ->assertSee('Cannot delete class with 1 enrolled students');

        $this->assertDatabaseHas('school_classes', ['id' => $class->id]);
    }

    public function test_admin_can_delete_empty_class(): void
    {
        $this->actingAs($this->admin);

        $class = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'title' => 'Obsolete Pilot Class',
            'kind' => ClassKind::InstituteClass->value,
            'fee_amount' => 5000,
            'capacity' => 10,
            'status' => 'active',
        ]);

        Livewire::test(ClassManager::class)
            ->call('deleteClass', $class->id);

        $this->assertDatabaseMissing('school_classes', ['id' => $class->id]);

        $activity = Activity::where('event', 'class.deleted')->first();
        $this->assertNotNull($activity);
    }

    public function test_multi_institution_isolation_in_class_manager(): void
    {
        $otherInstitution = Institution::create([
            'name' => 'Horizon College',
            'slug' => 'horizon-college',
            'code' => 'HC',
            'type' => 'institute',
            'is_active' => true,
        ]);

        $otherClass = SchoolClass::create([
            'institution_id' => $otherInstitution->id,
            'title' => 'Confidential Quantum Mechanics',
            'kind' => ClassKind::InstituteClass->value,
            'fee_amount' => 50000,
            'capacity' => 10,
            'status' => 'active',
        ]);

        $this->actingAs($this->admin);

        Livewire::test(ClassManager::class)
            ->assertDontSee('Confidential Quantum Mechanics');
    }
}
