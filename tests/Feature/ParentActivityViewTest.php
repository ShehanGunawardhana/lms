<?php

namespace Tests\Feature;

use App\Enums\ActivityStatus;
use App\Enums\ActivitySubmissionStatus;
use App\Enums\ClassKind;
use App\Enums\RoleName;
use App\Livewire\Parent\ParentActivityFeed;
use App\Models\Activity;
use App\Models\ActivityMaterial;
use App\Models\ActivitySubmission;
use App\Models\ActivityVideo;
use App\Models\Enrollment;
use App\Models\FileAsset;
use App\Models\Guardian;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ParentActivityViewTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected Institution $otherInstitution;

    protected User $parent;

    protected Guardian $guardian;

    protected Student $childOne;

    protected Student $childTwo;

    protected SchoolClass $playgroup;

    protected SchoolClass $toddlerGroup;

    protected User $teacher;

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

        $this->parent = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Eleanor Vance',
            'status' => 'active',
        ]);
        $this->parent->assignRole(RoleName::Parent->value);

        $this->guardian = Guardian::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->parent->id,
            'relationship' => 'mother',
        ]);

        $this->playgroup = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Playgroup Sunflowers',
            'kind' => ClassKind::NurseryGroup,
            'capacity' => 15,
            'status' => 'active',
        ]);

        $this->toddlerGroup = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Toddlers Buttercups',
            'kind' => ClassKind::NurseryGroup,
            'capacity' => 12,
            'status' => 'active',
        ]);

        // Child 1 in Playgroup
        $this->childOne = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => 'NUR-2026-0001-A1',
            'first_name' => 'Oliver',
            'last_name' => 'Vance',
            'dob' => now()->subYears(3),
            'status' => 'active',
            'kind' => 'nursery',
        ]);

        $this->guardian->students()->attach($this->childOne->id, [
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

        // Child 2 in ToddlerGroup
        $this->childTwo = Student::create([
            'institution_id' => $this->institution->id,
            'student_code' => 'NUR-2026-0002-B2',
            'first_name' => 'Maya',
            'last_name' => 'Vance',
            'dob' => now()->subYears(2),
            'status' => 'active',
            'kind' => 'nursery',
        ]);

        $this->guardian->students()->attach($this->childTwo->id, [
            'institution_id' => $this->institution->id,
            'relationship' => 'mother',
            'is_primary' => true,
        ]);

        Enrollment::create([
            'institution_id' => $this->institution->id,
            'student_id' => $this->childTwo->id,
            'class_id' => $this->toddlerGroup->id,
            'status' => 'active',
            'source' => 'admin',
            'enrolled_at' => now(),
        ]);
    }

    public function test_parent_can_access_activity_feed_and_view_child_activities(): void
    {
        $publishedActivity = Activity::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->playgroup->id,
            'created_by' => $this->teacher->id,
            'title' => 'Finger Painting Fun',
            'description' => 'Creative color mixing with edible paint.',
            'instructions' => 'Step 1: Spread paper. Step 2: Have fun painting.',
            'age_group' => '2-3 years',
            'status' => ActivityStatus::Published,
            'publish_date' => now()->toDateString(),
            'deadline' => now()->addDays(3),
        ]);

        $draftActivity = Activity::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->playgroup->id,
            'created_by' => $this->teacher->id,
            'title' => 'Unpublished Teacher Draft',
            'instructions' => 'Internal teacher draft note.',
            'status' => ActivityStatus::Draft,
        ]);

        $this->actingAs($this->parent);

        $response = $this->get(route('parent.activities'));
        $response->assertOk();
        $response->assertSee('Nursery Activities & Home Fun');
        $response->assertSee('Oliver Vance');
        $response->assertSee('Finger Painting Fun');
        $response->assertDontSee('Unpublished Teacher Draft');
    }

    public function test_parent_feed_multi_child_switching_updates_scoped_activities(): void
    {
        $playgroupActivity = Activity::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->playgroup->id,
            'created_by' => $this->teacher->id,
            'title' => 'Oliver Playgroup Alphabet Puzzle',
            'instructions' => 'Match letters A through E.',
            'status' => ActivityStatus::Published,
            'publish_date' => now()->toDateString(),
        ]);

        $toddlerActivity = Activity::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->toddlerGroup->id,
            'created_by' => $this->teacher->id,
            'title' => 'Maya Toddler Sensory Sand Play',
            'instructions' => 'Hide objects in colored sand.',
            'status' => ActivityStatus::Published,
            'publish_date' => now()->toDateString(),
        ]);

        $this->actingAs($this->parent);

        // Feed defaults to Child 1 (Oliver - Playgroup)
        Livewire::test(ParentActivityFeed::class)
            ->assertSet('selectedChildId', $this->childOne->id)
            ->assertSee('Oliver Playgroup Alphabet Puzzle')
            ->assertDontSee('Maya Toddler Sensory Sand Play')
            // Switch to Child 2 (Maya - Toddlers)
            ->call('selectChild', $this->childTwo->id)
            ->assertSet('selectedChildId', $this->childTwo->id)
            ->assertSee('Maya Toddler Sensory Sand Play')
            ->assertDontSee('Oliver Playgroup Alphabet Puzzle');
    }

    public function test_parent_without_children_sees_friendly_empty_state(): void
    {
        $emptyParent = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $emptyParent->assignRole(RoleName::Parent->value);

        $this->actingAs($emptyParent);

        Livewire::test(ParentActivityFeed::class)
            ->assertSee('No Enrolled Children Found');
    }

    public function test_parent_feed_search_and_status_filters(): void
    {
        $activityTodo = Activity::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->playgroup->id,
            'created_by' => $this->teacher->id,
            'title' => 'Water Play Exploration',
            'description' => 'Pouring and splashing water science.',
            'instructions' => 'Pour water between measuring cups.',
            'status' => ActivityStatus::Published,
        ]);

        $activitySubmitted = Activity::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->playgroup->id,
            'created_by' => $this->teacher->id,
            'title' => 'Shape Stacking Blocks',
            'description' => 'Building towers with blocks.',
            'instructions' => 'Stack 5 blocks high.',
            'status' => ActivityStatus::Published,
        ]);

        ActivitySubmission::create([
            'institution_id' => $this->institution->id,
            'activity_id' => $activitySubmitted->id,
            'student_id' => $this->childOne->id,
            'guardian_id' => $this->guardian->id,
            'status' => ActivitySubmissionStatus::Submitted,
            'submitted_at' => now(),
            'version' => 1,
        ]);

        $this->actingAs($this->parent);

        Livewire::test(ParentActivityFeed::class)
            ->assertSee('Water Play Exploration')
            ->assertSee('Shape Stacking Blocks')
            // Filter by todo (not_started)
            ->call('setStatusFilter', 'todo')
            ->assertSee('Water Play Exploration')
            ->assertDontSee('Shape Stacking Blocks')
            // Filter by submitted
            ->call('setStatusFilter', 'submitted')
            ->assertDontSee('Water Play Exploration')
            ->assertSee('Shape Stacking Blocks')
            // Search by keyword
            ->call('setStatusFilter', 'all')
            ->set('search', 'Stacking')
            ->assertSee('Shape Stacking Blocks')
            ->assertDontSee('Water Play Exploration');
    }

    public function test_parent_can_view_activity_show_page_with_details_countdown_videos_and_materials(): void
    {
        $activity = Activity::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->playgroup->id,
            'created_by' => $this->teacher->id,
            'title' => 'Storytime & Sound Walk',
            'description' => 'Listen carefully to outdoor sounds.',
            'instructions' => "Step 1: Go outside together.\nStep 2: Listen for birds and wind.",
            'age_group' => 'playgroup',
            'status' => ActivityStatus::Published,
            'publish_date' => now()->toDateString(),
            'deadline' => now()->addDays(5),
            'allow_late' => true,
        ]);

        ActivityVideo::create([
            'activity_id' => $activity->id,
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'video_id' => 'dQw4w9WgXcQ',
            'title' => 'Sing Along Music Video',
            'sort_order' => 1,
        ]);

        $fileAsset = FileAsset::create([
            'institution_id' => $this->institution->id,
            'uploaded_by' => $this->teacher->id,
            'uuid' => (string) Str::uuid(),
            'disk' => 'local',
            'path' => 'materials/sample-guide.pdf',
            'original_name' => 'sound-walk-worksheet.pdf',
            'extension' => 'pdf',
            'mime_verified' => 'application/pdf',
            'size' => 1024 * 300,
            'hash_sha256' => hash('sha256', 'sample-sound-walk-pdf'),
            'kind' => 'document',
            'purpose' => 'material',
            'scan_status' => 'clean',
        ]);

        ActivityMaterial::create([
            'activity_id' => $activity->id,
            'file_asset_id' => $fileAsset->id,
            'title' => 'Printable Sound Walk Sheet',
            'type' => 'file',
            'sort_order' => 1,
        ]);

        ActivityMaterial::create([
            'activity_id' => $activity->id,
            'title' => 'Online Bird Sounds Audio',
            'url' => 'https://example.com/bird-audio',
            'type' => 'link',
            'sort_order' => 2,
        ]);

        $this->actingAs($this->parent);

        $response = $this->get(route('parent.activities.show', $activity));
        $response->assertOk();
        $response->assertSee('Storytime & Sound Walk');
        $response->assertSee('Step 1: Go outside together.');
        $response->assertSee('Sing Along Music Video');
        $response->assertSee('youtube-nocookie.com/embed/dQw4w9WgXcQ');
        $response->assertSee('Printable Sound Walk Sheet');
        $response->assertSee('Online Bird Sounds Audio');
        $response->assertSee('Oliver Vance');
        $response->assertSee('To Do');
    }

    public function test_parent_cannot_view_draft_activity(): void
    {
        $draft = Activity::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->playgroup->id,
            'created_by' => $this->teacher->id,
            'title' => 'Hidden Draft Activity',
            'instructions' => 'Not published yet',
            'status' => ActivityStatus::Draft,
        ]);

        $this->actingAs($this->parent);

        $response = $this->get(route('parent.activities.show', $draft));
        $response->assertForbidden();
    }

    public function test_parent_cannot_view_activity_for_unrelated_class(): void
    {
        $unrelatedClass = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Junior Kindergarten Tigers',
            'kind' => ClassKind::NurseryGroup,
            'capacity' => 20,
            'status' => 'active',
        ]);

        $unrelatedActivity = Activity::create([
            'institution_id' => $this->institution->id,
            'class_id' => $unrelatedClass->id,
            'created_by' => $this->teacher->id,
            'title' => 'Tigers Advanced Math Counting',
            'instructions' => 'Count to 50.',
            'status' => ActivityStatus::Published,
        ]);

        $this->actingAs($this->parent);

        // Neither childOne nor childTwo is in Tigers class -> Forbidden
        $response = $this->get(route('parent.activities.show', $unrelatedActivity));
        $response->assertForbidden();
    }

    public function test_activity_show_displays_reviewed_submission_with_teacher_feedback(): void
    {
        $activity = Activity::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->playgroup->id,
            'created_by' => $this->teacher->id,
            'title' => 'Clay Animal Sculpting',
            'instructions' => 'Make a little clay cat or dog.',
            'status' => ActivityStatus::Published,
        ]);

        ActivitySubmission::create([
            'institution_id' => $this->institution->id,
            'activity_id' => $activity->id,
            'student_id' => $this->childOne->id,
            'guardian_id' => $this->guardian->id,
            'status' => ActivitySubmissionStatus::Reviewed,
            'submitted_at' => now()->subDay(),
            'reviewed_at' => now(),
            'reviewed_by' => $this->teacher->id,
            'feedback' => 'Splendid little clay kitten, Oliver! You are so creative!',
            'rating' => '⭐⭐⭐⭐⭐',
            'version' => 1,
        ]);

        $this->actingAs($this->parent);

        $response = $this->get(route('parent.activities.show', $activity));
        $response->assertOk();
        $response->assertSee('Reviewed');
        $response->assertSee('Splendid little clay kitten, Oliver! You are so creative!');
        $response->assertSee('⭐⭐⭐⭐⭐');
    }

    public function test_multi_institution_isolation_prevents_cross_tenant_access(): void
    {
        $otherClass = SchoolClass::create([
            'institution_id' => $this->otherInstitution->id,
            'title' => 'Other Institution Kindergarten',
            'kind' => ClassKind::NurseryGroup,
            'capacity' => 10,
            'status' => 'active',
        ]);

        $otherActivity = Activity::create([
            'institution_id' => $this->otherInstitution->id,
            'class_id' => $otherClass->id,
            'created_by' => $this->teacher->id,
            'title' => 'Other Institution Secret Activity',
            'instructions' => 'Other institution instructions.',
            'status' => ActivityStatus::Published,
        ]);

        $this->actingAs($this->parent);

        // Parent belongs to institution, otherActivity belongs to otherInstitution -> Not Found via tenant global scope
        $response = $this->get(route('parent.activities.show', $otherActivity));
        $response->assertNotFound();
    }

    public function test_general_activities_route_redirects_parent_to_parent_activities(): void
    {
        $this->actingAs($this->parent);

        $response = $this->get('/activities');
        $response->assertRedirect(route('parent.activities'));
    }
}
