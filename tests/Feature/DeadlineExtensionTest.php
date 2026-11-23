<?php

namespace Tests\Feature;

use App\Enums\ActivityStatus;
use App\Enums\ClassKind;
use App\Enums\RoleName;
use App\Livewire\Parent\ParentActivityFeed;
use App\Livewire\Parent\ParentActivityShow;
use App\Livewire\Teacher\ActivityManager;
use App\Models\Activity;
use App\Models\DeadlineExtension;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DeadlineExtensionTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected Institution $otherInstitution;

    protected User $teacher;

    protected User $otherTeacher;

    protected User $parentOne;

    protected User $parentTwo;

    protected Guardian $guardianOne;

    protected Guardian $guardianTwo;

    protected Student $childOne;

    protected Student $childTwo;

    protected SchoolClass $playgroup;

    protected Activity $activity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institution = Institution::create([
            'name' => 'Royal Blossoms Nursery',
            'slug' => 'royal-blossoms',
            'code' => 'RBN',
            'type' => 'nursery',
            'is_active' => true,
        ]);

        $this->otherInstitution = Institution::create([
            'name' => 'Other Nursery',
            'slug' => 'other-nursery',
            'code' => 'OIN',
            'type' => 'nursery',
            'is_active' => true,
        ]);

        session(['current_institution_id' => $this->institution->id]);

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->teacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Teacher Sarah',
            'status' => 'active',
        ]);
        $this->teacher->assignRole(RoleName::Teacher->value);

        $this->otherTeacher = User::factory()->create([
            'institution_id' => $this->otherInstitution->id,
            'name' => 'Teacher Dave',
            'status' => 'active',
        ]);
        $this->otherTeacher->assignRole(RoleName::Teacher->value);

        $this->playgroup = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Playgroup Sunflowers',
            'kind' => ClassKind::NurseryGroup,
            'capacity' => 15,
            'status' => 'active',
        ]);

        // Parent 1 and Child 1
        $this->parentOne = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Eleanor Vance',
            'status' => 'active',
        ]);
        $this->parentOne->assignRole(RoleName::Parent->value);

        $this->guardianOne = Guardian::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->parentOne->id,
            'relationship' => 'mother',
        ]);

        $this->childOne = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => 'NUR-2026-0001',
            'first_name' => 'Oliver',
            'last_name' => 'Vance',
            'dob' => now()->subYears(3),
            'status' => 'active',
            'kind' => 'nursery',
        ]);

        $this->guardianOne->students()->attach($this->childOne->id, [
            'institution_id' => $this->institution->id,
            'relationship' => 'mother',
            'is_primary' => true,
        ]);

        Enrollment::create([
            'institution_id' => $this->institution->id,
            'student_id' => $this->childOne->id,
            'class_id' => $this->playgroup->id,
            'status' => 'active',
            'source' => 'admin',
            'enrolled_at' => now(),
        ]);

        // Parent 2 and Child 2 (in same class)
        $this->parentTwo = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Marcus Aurelius',
            'status' => 'active',
        ]);
        $this->parentTwo->assignRole(RoleName::Parent->value);

        $this->guardianTwo = Guardian::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->parentTwo->id,
            'relationship' => 'father',
        ]);

        $this->childTwo = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => 'NUR-2026-0002',
            'first_name' => 'Lucius',
            'last_name' => 'Aurelius',
            'dob' => now()->subYears(3),
            'status' => 'active',
            'kind' => 'nursery',
        ]);

        $this->guardianTwo->students()->attach($this->childTwo->id, [
            'institution_id' => $this->institution->id,
            'relationship' => 'father',
            'is_primary' => true,
        ]);

        Enrollment::create([
            'institution_id' => $this->institution->id,
            'student_id' => $this->childTwo->id,
            'class_id' => $this->playgroup->id,
            'status' => 'active',
            'source' => 'admin',
            'enrolled_at' => now(),
        ]);

        // Base activity with deadline
        $this->activity = Activity::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->playgroup->id,
            'created_by' => $this->teacher->id,
            'title' => 'Sensory Clay Creations',
            'instructions' => 'Sculpt 3 cute animals.',
            'status' => ActivityStatus::Published,
            'publish_date' => now()->toDateString(),
            'deadline' => now()->addDays(3),
            'allow_late' => false,
        ]);
    }

    public function test_teacher_can_open_extension_modal(): void
    {
        $this->actingAs($this->teacher);

        Livewire::test(ActivityManager::class)
            ->call('openExtensionModal', $this->activity->id)
            ->assertSet('showExtensionModal', true)
            ->assertSet('extendingActivityId', $this->activity->id)
            ->assertSet('extensionTarget', 'everyone')
            ->assertSee('Sensory Clay Creations')
            ->assertSee('Playgroup Sunflowers');
    }

    public function test_teacher_can_extend_deadline_for_everyone_with_reason_and_audit(): void
    {
        $this->actingAs($this->teacher);
        $newDeadline = now()->addDays(5)->startOfMinute();

        Livewire::test(ActivityManager::class)
            ->call('openExtensionModal', $this->activity->id)
            ->set('extensionTarget', 'everyone')
            ->set('newDeadline', $newDeadline->format('Y-m-d\TH:i'))
            ->set('extensionReason', 'Heavy storms disrupted internet access across town.')
            ->call('saveExtension')
            ->assertHasNoErrors()
            ->assertSet('showExtensionModal', false);

        $this->assertDatabaseHas('deadline_extensions', [
            'institution_id' => $this->institution->id,
            'extendable_id' => $this->activity->id,
            'student_id' => null,
            'extended_by' => $this->teacher->id,
            'reason' => 'Heavy storms disrupted internet access across town.',
        ]);

        // Verify effective deadline updated for everyone
        $this->activity->refresh();
        $this->assertTrue($this->activity->isExtendedForStudent());
        $this->assertEquals(
            $newDeadline->toIso8601String(),
            $this->activity->effectiveDeadlineForStudent($this->childOne->id)?->toIso8601String()
        );
        $this->assertEquals(
            $newDeadline->toIso8601String(),
            $this->activity->effectiveDeadlineForStudent($this->childTwo->id)?->toIso8601String()
        );
    }

    public function test_teacher_can_extend_deadline_for_individual_child_only(): void
    {
        $this->actingAs($this->teacher);
        $newDeadline = now()->addDays(7)->startOfMinute();

        Livewire::test(ActivityManager::class)
            ->call('openExtensionModal', $this->activity->id)
            ->set('extensionTarget', 'individual')
            ->set('extensionStudentId', $this->childOne->id)
            ->set('newDeadline', $newDeadline->format('Y-m-d\TH:i'))
            ->set('extensionReason', 'Oliver was down with high fever and requested medical extension.')
            ->call('saveExtension')
            ->assertHasNoErrors()
            ->assertSet('showExtensionModal', false);

        $this->assertDatabaseHas('deadline_extensions', [
            'institution_id' => $this->institution->id,
            'extendable_id' => $this->activity->id,
            'student_id' => $this->childOne->id,
            'extended_by' => $this->teacher->id,
            'reason' => 'Oliver was down with high fever and requested medical extension.',
        ]);

        $this->activity->refresh();

        // Child One gets extended deadline
        $this->assertTrue($this->activity->isExtendedForStudent($this->childOne->id));
        $this->assertEquals(
            $newDeadline->toIso8601String(),
            $this->activity->effectiveDeadlineForStudent($this->childOne->id)?->toIso8601String()
        );

        // Child Two does NOT get extended deadline; keeps original deadline
        $this->assertFalse($this->activity->isExtendedForStudent($this->childTwo->id));
        $this->assertEquals(
            $this->activity->deadline?->toIso8601String(),
            $this->activity->effectiveDeadlineForStudent($this->childTwo->id)?->toIso8601String()
        );
    }

    public function test_extension_validation_rejects_past_or_earlier_deadline_and_empty_reason(): void
    {
        $this->actingAs($this->teacher);

        // Earlier than current deadline
        $earlierDeadline = now()->addDay()->format('Y-m-d\TH:i');

        Livewire::test(ActivityManager::class)
            ->call('openExtensionModal', $this->activity->id)
            ->set('newDeadline', $earlierDeadline)
            ->set('extensionReason', 'Some valid reason')
            ->call('saveExtension')
            ->assertSet('showExtensionModal', true)
            ->assertSee('must be strictly after the current deadline');

        // Short reason
        Livewire::test(ActivityManager::class)
            ->call('openExtensionModal', $this->activity->id)
            ->set('newDeadline', now()->addDays(6)->format('Y-m-d\TH:i'))
            ->set('extensionReason', 'bad')
            ->call('saveExtension')
            ->assertHasErrors(['extensionReason']);
    }

    public function test_time_travel_extension_locks_child_without_extension_while_allowing_child_with_extension(): void
    {
        $originalDeadline = now()->addDays(2);
        $this->activity->update([
            'deadline' => $originalDeadline,
            'allow_late' => false,
        ]);

        $extendedDeadline = now()->addDays(6);

        // Grant extension to childOne only
        DeadlineExtension::create([
            'institution_id' => $this->institution->id,
            'extendable_type' => Activity::class,
            'extendable_id' => $this->activity->id,
            'student_id' => $this->childOne->id,
            'extended_by' => $this->teacher->id,
            'original_deadline' => $originalDeadline,
            'extended_deadline' => $extendedDeadline,
            'reason' => 'Granted extra time',
        ]);

        // Travel to day 4 (past original deadline, but before childOne extended deadline)
        $this->travelTo(now()->addDays(4));

        $this->activity->refresh();

        // Child Two (no extension) is past deadline and locked out
        $this->assertTrue($this->activity->isPastDeadlineForStudent($this->childTwo->id));
        $this->assertFalse($this->activity->canAcceptSubmissionsForStudent($this->childTwo->id));

        // Child One (has extension) is NOT past deadline and CAN submit!
        $this->assertFalse($this->activity->isPastDeadlineForStudent($this->childOne->id));
        $this->assertTrue($this->activity->canAcceptSubmissionsForStudent($this->childOne->id));

        // Travel past extended deadline (day 7)
        $this->travelTo(now()->addDays(7));

        // Now both are expired
        $this->assertTrue($this->activity->isPastDeadlineForStudent($this->childOne->id));
        $this->assertFalse($this->activity->canAcceptSubmissionsForStudent($this->childOne->id));
    }

    public function test_parent_feed_and_show_pages_display_live_extended_deadline_and_badge(): void
    {
        $newDeadline = now()->addDays(8);

        DeadlineExtension::create([
            'institution_id' => $this->institution->id,
            'extendable_type' => Activity::class,
            'extendable_id' => $this->activity->id,
            'student_id' => $this->childOne->id,
            'extended_by' => $this->teacher->id,
            'original_deadline' => $this->activity->deadline,
            'extended_deadline' => $newDeadline,
            'reason' => 'Doctor requested home rest for 5 days.',
        ]);

        $this->actingAs($this->parentOne);

        // Feed page displays "Extended" badge
        Livewire::test(ParentActivityFeed::class)
            ->assertSee('Sensory Clay Creations')
            ->assertSee('Extended');

        // Show page displays extension notice banner
        Livewire::test(ParentActivityShow::class, ['activity' => $this->activity])
            ->assertSee('Sensory Clay Creations')
            ->assertSee('Extended Deadline')
            ->assertSee('Doctor requested home rest for 5 days.');
    }

    public function test_teacher_can_view_extension_history_log_modal(): void
    {
        DeadlineExtension::create([
            'institution_id' => $this->institution->id,
            'extendable_type' => Activity::class,
            'extendable_id' => $this->activity->id,
            'student_id' => $this->childOne->id,
            'extended_by' => $this->teacher->id,
            'original_deadline' => $this->activity->deadline,
            'extended_deadline' => now()->addDays(5),
            'reason' => 'Oliver family travel bereavement.',
        ]);

        $this->actingAs($this->teacher);

        Livewire::test(ActivityManager::class)
            ->call('openExtensionLogModal', $this->activity->id)
            ->assertSet('showExtensionLogModal', true)
            ->assertSee('Extension Audit History')
            ->assertSee('Oliver Vance')
            ->assertSee('Oliver family travel bereavement.')
            ->assertSee('Teacher Sarah');
    }

    public function test_unauthorized_user_cannot_extend_deadlines(): void
    {
        // Parent cannot extend
        Livewire::actingAs($this->parentOne)
            ->test(ActivityManager::class)
            ->call('openExtensionModal', $this->activity->id)
            ->assertForbidden();
    }

    public function test_multi_institution_isolation_in_deadline_extensions(): void
    {
        $otherClass = SchoolClass::create([
            'institution_id' => $this->otherInstitution->id,
            'title' => 'Other Nursery Class',
            'kind' => ClassKind::NurseryGroup,
            'capacity' => 10,
            'status' => 'active',
        ]);

        $otherActivity = Activity::create([
            'institution_id' => $this->otherInstitution->id,
            'class_id' => $otherClass->id,
            'created_by' => $this->otherTeacher->id,
            'title' => 'Secret Other Institution Activity',
            'instructions' => 'Other instructions',
            'status' => ActivityStatus::Published->value,
            'publish_date' => now()->toDateString(),
        ]);

        // Teacher from institution 1 cannot access activity from other institution
        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($this->teacher)
            ->test(ActivityManager::class)
            ->call('openExtensionModal', $otherActivity->id);
    }
}
