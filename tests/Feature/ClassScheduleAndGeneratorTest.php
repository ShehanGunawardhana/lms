<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Livewire\Admin\ClassScheduleManager;
use App\Models\AcademicYear;
use App\Models\ClassSchedule;
use App\Models\ClassSession;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\ScheduleGenerator;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ClassScheduleAndGeneratorTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected User $admin;

    protected User $teacher;

    protected User $student;

    protected SchoolClass $class;

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
        ]);
        $this->teacher->assignRole(RoleName::Teacher->value);

        $this->student = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $this->student->assignRole(RoleName::Student->value);

        $academicYear = AcademicYear::create([
            'institution_id' => $this->institution->id,
            'name' => '2026 Academic Year',
            'starts_on' => '2026-01-01',
            'ends_on' => '2026-12-31',
            'is_current' => true,
        ]);

        $this->class = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'academic_year_id' => $academicYear->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Pure Mathematics 2026',
            'subject' => 'Mathematics',
            'kind' => 'institute_class',
            'fee_amount' => 12500,
            'capacity' => 50,
            'room' => 'Hall 201',
            'status' => 'active',
        ]);
    }

    public function test_guest_or_non_admin_cannot_access_class_schedule(): void
    {
        $this->get(route('admin.classes.schedule', $this->class->id))->assertRedirect('/login');

        $this->actingAs($this->teacher)
            ->get(route('admin.classes.schedule', $this->class->id))
            ->assertForbidden();

        $this->actingAs($this->student)
            ->get(route('admin.classes.schedule', $this->class->id))
            ->assertForbidden();
    }

    public function test_admin_with_two_factor_can_render_class_schedule_manager(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.classes.schedule', $this->class->id));

        $response->assertOk();
        $response->assertSee('Schedule &amp; Session Planner', false);
        $response->assertSee('Pure Mathematics 2026');
        $response->assertSee('Weekly Recurring Timetable');
        $response->assertSee('Dated Session Generator &amp; Safe Sync', false);
    }

    public function test_admin_can_add_weekly_schedule_slots(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ClassScheduleManager::class, ['classId' => $this->class->id])
            ->call('openCreateSlotModal')
            ->assertSet('showSlotModal', true)
            ->set('slotWeekday', 1) // Monday
            ->set('slotStartsAt', '08:30')
            ->set('slotEndsAt', '11:30')
            ->set('slotRoom', 'Hall 401')
            ->set('slotOnlineUrl', 'https://zoom.us/j/123456789')
            ->call('saveSlot')
            ->assertSet('showSlotModal', false)
            ->assertHasNoErrors();

        $slot = ClassSchedule::where('class_id', $this->class->id)->first();

        $this->assertNotNull($slot);
        $this->assertSame(1, $slot->weekday);
        $this->assertSame('08:30:00', $slot->starts_at);
        $this->assertSame('11:30:00', $slot->ends_at);
        $this->assertSame('Hall 401', $slot->room);
        $this->assertSame('Monday', $slot->weekday_name);

        $activity = Activity::where('event', 'schedule.created')->first();
        $this->assertNotNull($activity);
    }

    public function test_schedule_generator_creates_dated_sessions(): void
    {
        // Monday schedule: 08:30 - 11:30
        ClassSchedule::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->class->id,
            'weekday' => 1,
            'starts_at' => '08:30:00',
            'ends_at' => '11:30:00',
            'room' => 'Hall 201',
        ]);

        // Wednesday schedule: 14:00 - 16:00
        ClassSchedule::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->class->id,
            'weekday' => 3,
            'starts_at' => '14:00:00',
            'ends_at' => '16:00:00',
            'room' => 'Lab 1',
        ]);

        $generator = app(ScheduleGenerator::class);

        // Run for a specific 2-week period: March 2, 2026 (Mon) to March 15, 2026 (Sun)
        // Mondays: March 2, March 9 (2 sessions)
        // Wednesdays: March 4, March 11 (2 sessions) -> Total 4 sessions
        $result = $generator->generateForClass($this->class, '2026-03-02', '2026-03-15');

        $this->assertSame(4, $result['created']);
        $this->assertSame(0, $result['existing']);
        $this->assertSame(4, ClassSession::where('class_id', $this->class->id)->count());

        // Re-running for same period is idempotent (preserves existing, creates 0)
        $rerunResult = $generator->generateForClass($this->class, '2026-03-02', '2026-03-15');
        $this->assertSame(0, $rerunResult['created']);
        $this->assertSame(4, $rerunResult['existing']);
        $this->assertSame(4, ClassSession::where('class_id', $this->class->id)->count());
    }

    public function test_safe_regeneration_never_deletes_sessions_with_attached_data(): void
    {
        $this->actingAs($this->admin);

        // Schedule slot: Monday 08:30
        $mondaySlot = ClassSchedule::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->class->id,
            'weekday' => 1,
            'starts_at' => '08:30:00',
            'ends_at' => '11:30:00',
        ]);

        // Generate sessions for March 2026
        $generator = app(ScheduleGenerator::class);
        $generator->generateForClass($this->class, '2026-03-01', '2026-03-31');

        $sessions = ClassSession::where('class_id', $this->class->id)->get();
        $this->assertNotEmpty($sessions);

        // Mark the first session as completed (simulating session with data/history)
        $sessionWithData = $sessions->first();
        $sessionWithData->update(['status' => 'completed']);

        // Now teacher/admin alters the schedule: removes Monday slot and adds Friday slot
        $mondaySlot->delete();

        ClassSchedule::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->class->id,
            'weekday' => 5, // Friday
            'starts_at' => '10:00:00',
            'ends_at' => '12:00:00',
        ]);

        // Run Safe Regenerate
        $result = $generator->regenerateForClass($this->class, '2026-03-01', '2026-03-31');

        // Completed/data session must still exist!
        $this->assertDatabaseHas('class_sessions', [
            'id' => $sessionWithData->id,
            'status' => 'completed',
        ]);

        $this->assertSame(1, $result['preserved_with_data']);
        $this->assertGreaterThan(0, $result['created']); // New Friday sessions created
    }

    public function test_admin_can_cancel_session_with_mandatory_reason(): void
    {
        $this->actingAs($this->admin);

        $session = ClassSession::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->class->id,
            'date' => '2026-04-14',
            'starts_at' => '09:00:00',
            'ends_at' => '12:00:00',
            'status' => 'scheduled',
        ]);

        Livewire::test(ClassScheduleManager::class, ['classId' => $this->class->id])
            ->call('openCancelModal', $session->id)
            ->assertSet('showCancelModal', true)
            ->set('cancelReason', 'Sinhala and Tamil New Year Public Holiday observed.')
            ->call('cancelSession')
            ->assertSet('showCancelModal', false)
            ->assertHasNoErrors();

        $updated = $session->fresh();
        $this->assertSame('cancelled', $updated->status);
        $this->assertSame('Sinhala and Tamil New Year Public Holiday observed.', $updated->cancel_reason);

        $activity = Activity::where('event', 'session.cancelled')->first();
        $this->assertNotNull($activity);
    }

    public function test_multi_institution_isolation_in_schedule_manager(): void
    {
        $otherInstitution = Institution::create([
            'name' => 'Metro College',
            'slug' => 'metro-college',
            'code' => 'MC',
            'type' => 'institute',
            'is_active' => true,
        ]);

        $otherClass = SchoolClass::create([
            'institution_id' => $otherInstitution->id,
            'title' => 'Other College Quantum Physics',
            'kind' => 'institute_class',
            'fee_amount' => 30000,
            'capacity' => 20,
            'status' => 'active',
        ]);

        $this->actingAs($this->admin);

        // Cannot view other institution's class schedule
        $this->get(route('admin.classes.schedule', $otherClass->id))
            ->assertNotFound();
    }
}
