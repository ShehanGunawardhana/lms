<?php

namespace Tests\Feature;

use App\Enums\ActivityStatus;
use App\Enums\ClassKind;
use App\Enums\RoleName;
use App\Livewire\Teacher\ActivityManager;
use App\Models\Activity;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ActivityManagerTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected Institution $otherInstitution;

    protected User $teacher;

    protected User $admin;

    protected User $student;

    protected SchoolClass $nurseryGroup;

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
            'name' => 'Other International Nursery',
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

        $this->admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Admin Elena',
            'status' => 'active',
        ]);
        $this->admin->assignRole(RoleName::Admin->value);

        $this->student = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Little Timmy',
            'status' => 'active',
        ]);
        $this->student->assignRole(RoleName::Student->value);

        $this->nurseryGroup = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Little Spiders (2-3 yrs)',
            'kind' => ClassKind::NurseryGroup,
            'fee_amount' => 15000.00,
            'capacity' => 15,
            'status' => 'active',
        ]);
    }

    public function test_teacher_can_access_activity_manager_page(): void
    {
        $this->actingAs($this->teacher)
            ->get(route('teacher.activities'))
            ->assertOk()
            ->assertSee('Nursery Activities')
            ->assertSee('Create, organize, and publish engaging step-by-step activities');
    }

    public function test_teacher_can_create_a_new_draft_activity(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(ActivityManager::class)
            ->call('openCreateModal')
            ->set('classId', $this->nurseryGroup->id)
            ->set('title', 'Finger Painting Rainbows')
            ->set('description', 'Fun sensory painting session with primary colors.')
            ->set('instructions', "1. Mix safe food-grade paint.\n2. Help the child press palms onto paper.\n3. Make a sunny rainbow arc!")
            ->set('ageGroup', '2-3 years')
            ->set('publishDate', now()->toDateString())
            ->set('deadline', now()->addDays(5)->format('Y-m-d\TH:i'))
            ->set('status', 'draft')
            ->set('allowLate', true)
            ->call('saveActivity')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('activities', [
            'institution_id' => $this->institution->id,
            'class_id' => $this->nurseryGroup->id,
            'created_by' => $this->teacher->id,
            'title' => 'Finger Painting Rainbows',
            'age_group' => '2-3 years',
            'status' => ActivityStatus::Draft->value,
            'allow_late' => true,
        ]);
    }

    public function test_teacher_can_create_and_immediately_publish_activity(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(ActivityManager::class)
            ->call('openCreateModal')
            ->set('classId', $this->nurseryGroup->id)
            ->set('title', 'Playdough Shapes Explorer')
            ->set('instructions', 'Roll and cut circles, squares, and stars.')
            ->set('ageGroup', '2-3 years')
            ->set('publishDate', now()->toDateString())
            ->set('status', 'published')
            ->call('saveActivity')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('activities', [
            'title' => 'Playdough Shapes Explorer',
            'status' => ActivityStatus::Published->value,
        ]);
    }

    public function test_teacher_can_edit_an_existing_activity(): void
    {
        $activity = Activity::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->nurseryGroup->id,
            'created_by' => $this->teacher->id,
            'title' => 'Old Title',
            'instructions' => 'Original instructions step 1',
            'age_group' => '2-3 years',
            'status' => ActivityStatus::Draft->value,
            'publish_date' => now()->toDateString(),
        ]);

        Livewire::actingAs($this->teacher)
            ->test(ActivityManager::class)
            ->call('openEditModal', $activity->id)
            ->assertSet('title', 'Old Title')
            ->set('title', 'Updated Colorful Building Blocks')
            ->set('instructions', 'Stack 5 blocks and count them together.')
            ->call('saveActivity')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('activities', [
            'id' => $activity->id,
            'title' => 'Updated Colorful Building Blocks',
            'instructions' => 'Stack 5 blocks and count them together.',
        ]);
    }

    public function test_teacher_can_transition_activity_lifecycle_states(): void
    {
        $activity = Activity::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->nurseryGroup->id,
            'created_by' => $this->teacher->id,
            'title' => 'Water Play Exploration',
            'instructions' => 'Float and sink items in water tub.',
            'age_group' => '2-3 years',
            'status' => ActivityStatus::Draft->value,
            'publish_date' => now()->toDateString(),
        ]);

        $component = Livewire::actingAs($this->teacher)->test(ActivityManager::class);

        // 1. Publish draft -> published
        $component->call('publishActivity', $activity->id);
        $this->assertEquals(ActivityStatus::Published, $activity->fresh()->status);

        // 2. Close published -> closed
        $component->call('closeActivity', $activity->id);
        $this->assertEquals(ActivityStatus::Closed, $activity->fresh()->status);

        // 3. Reopen closed -> published
        $component->call('reopenActivity', $activity->id);
        $this->assertEquals(ActivityStatus::Published, $activity->fresh()->status);
    }

    public function test_teacher_can_delete_activity(): void
    {
        $activity = Activity::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->nurseryGroup->id,
            'created_by' => $this->teacher->id,
            'title' => 'Temporary Activity',
            'instructions' => 'Step 1',
            'age_group' => '2-3 years',
            'status' => ActivityStatus::Draft->value,
            'publish_date' => now()->toDateString(),
        ]);

        Livewire::actingAs($this->teacher)
            ->test(ActivityManager::class)
            ->call('confirmDelete', $activity->id)
            ->assertSet('showDeleteModal', true)
            ->call('deleteActivity')
            ->assertHasNoErrors();

        $this->assertSoftDeleted('activities', ['id' => $activity->id]);
    }

    public function test_filter_activities_by_status_and_search(): void
    {
        Activity::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->nurseryGroup->id,
            'created_by' => $this->teacher->id,
            'title' => 'Nursery Rhymes Singalong',
            'instructions' => 'Sing Baa Baa Black Sheep',
            'age_group' => '2-3 years',
            'status' => ActivityStatus::Published->value,
            'publish_date' => now()->toDateString(),
        ]);

        Activity::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->nurseryGroup->id,
            'created_by' => $this->teacher->id,
            'title' => 'Sensory Rice Bin Discovery',
            'instructions' => 'Find hidden plastic letters in colored rice',
            'age_group' => '2-3 years',
            'status' => ActivityStatus::Draft->value,
            'publish_date' => now()->toDateString(),
        ]);

        Livewire::actingAs($this->teacher)
            ->test(ActivityManager::class)
            ->set('statusFilter', 'draft')
            ->assertSee('Sensory Rice Bin Discovery')
            ->assertDontSee('Nursery Rhymes Singalong')
            ->set('statusFilter', 'all')
            ->set('search', 'Singalong')
            ->assertSee('Nursery Rhymes Singalong')
            ->assertDontSee('Sensory Rice Bin Discovery');
    }

    public function test_multi_institution_isolation_prevents_cross_tenant_access(): void
    {
        $otherClass = SchoolClass::create([
            'institution_id' => $this->otherInstitution->id,
            'title' => 'Other Nursery Class',
            'kind' => ClassKind::NurseryGroup,
            'fee_amount' => 10000.00,
            'capacity' => 10,
            'status' => 'active',
        ]);

        $otherActivity = Activity::create([
            'institution_id' => $this->otherInstitution->id,
            'class_id' => $otherClass->id,
            'created_by' => $this->teacher->id,
            'title' => 'Secret Other Institution Activity',
            'instructions' => 'Not for institution 1',
            'age_group' => '2-3 years',
            'status' => ActivityStatus::Published->value,
            'publish_date' => now()->toDateString(),
        ]);

        Livewire::actingAs($this->teacher)
            ->test(ActivityManager::class)
            ->assertDontSee('Secret Other Institution Activity');

        // Attempting to edit activity of other institution should fail with 404 / ModelNotFound
        $this->expectException(ModelNotFoundException::class);
        Livewire::actingAs($this->teacher)
            ->test(ActivityManager::class)
            ->call('openEditModal', $otherActivity->id);
    }

    public function test_unauthorized_user_cannot_access_activity_manager(): void
    {
        $this->actingAs($this->student)
            ->get(route('teacher.activities'))
            ->assertForbidden();
    }

    public function test_activity_model_methods(): void
    {
        $activity = new Activity([
            'status' => ActivityStatus::Published,
            'deadline' => now()->addDay(),
            'allow_late' => false,
        ]);

        $this->assertTrue($activity->isPublished());
        $this->assertFalse($activity->isDraft());
        $this->assertFalse($activity->isClosed());
        $this->assertFalse($activity->isPastDeadline());
        $this->assertTrue($activity->canAcceptSubmissions());

        // Past deadline without allow late
        $pastActivity = new Activity([
            'status' => ActivityStatus::Published,
            'deadline' => now()->subDay(),
            'allow_late' => false,
        ]);
        $this->assertTrue($pastActivity->isPastDeadline());
        $this->assertFalse($pastActivity->canAcceptSubmissions());

        // Past deadline with allow late
        $lateAllowedActivity = new Activity([
            'status' => ActivityStatus::Published,
            'deadline' => now()->subDay(),
            'allow_late' => true,
        ]);
        $this->assertTrue($lateAllowedActivity->isPastDeadline());
        $this->assertTrue($lateAllowedActivity->canAcceptSubmissions());
    }
}
