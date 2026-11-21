<?php

namespace Tests\Feature;

use App\Enums\ActivityStatus;
use App\Enums\ClassKind;
use App\Enums\RoleName;
use App\Livewire\Teacher\ActivityManager;
use App\Models\Activity;
use App\Models\ActivityVideo;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ActivityVideoIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected User $teacher;

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

        session(['current_institution_id' => $this->institution->id]);

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->teacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Teacher Sarah',
            'status' => 'active',
        ]);
        $this->teacher->assignRole(RoleName::Teacher->value);

        $this->nurseryGroup = SchoolClass::create([
            'institution_id' => $this->institution->id,
            'teacher_id' => $this->teacher->id,
            'title' => 'Little Butterflies (3-4 yrs)',
            'kind' => ClassKind::NurseryGroup,
            'fee_amount' => 18000.00,
            'capacity' => 15,
            'status' => 'active',
        ]);
    }

    public function test_teacher_can_preview_and_add_multiple_videos_to_activity(): void
    {
        Http::fake([
            'https://www.youtube.com/oembed?url=https%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3DdQw4w9WgXcQ*' => Http::response([
                'title' => 'Nursery Sing-Along Video 1',
                'thumbnail_url' => 'https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg',
            ], 200),
            'https://www.youtube.com/oembed?url=https%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3DL_LUpnjgPso*' => Http::response([
                'title' => 'Sensory Activity Video 2',
                'thumbnail_url' => 'https://img.youtube.com/vi/L_LUpnjgPso/hqdefault.jpg',
            ], 200),
        ]);

        Livewire::actingAs($this->teacher)
            ->test(ActivityManager::class)
            ->call('openCreateModal')
            ->set('classId', $this->nurseryGroup->id)
            ->set('title', 'Musical Movement Morning')
            ->set('instructions', '1. Watch video 1.\n2. Dance along.\n3. Complete finger gestures.')
            ->set('ageGroup', '3-4 years')
            ->set('publishDate', now()->toDateString())
            // 1. Enter first YouTube URL and check preview
            ->set('newVideoUrl', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ')
            ->assertSet('pendingVideoPreview.video_id', 'dQw4w9WgXcQ')
            ->assertSet('pendingVideoPreview.title', 'Nursery Sing-Along Video 1')
            ->call('addVideo')
            ->assertCount('videos', 1)
            // 2. Enter second YouTube URL (short form)
            ->set('newVideoUrl', 'https://youtu.be/L_LUpnjgPso')
            ->call('addVideo')
            ->assertCount('videos', 2)
            ->call('saveActivity')
            ->assertHasNoErrors();

        $activity = Activity::where('title', 'Musical Movement Morning')->firstOrFail();
        $this->assertCount(2, $activity->videos);

        $video1 = $activity->videos[0];
        $this->assertEquals('dQw4w9WgXcQ', $video1->video_id);
        $this->assertEquals(0, $video1->sort_order);
        $this->assertStringContainsString('youtube-nocookie.com', $video1->embedUrl());

        $video2 = $activity->videos[1];
        $this->assertEquals('L_LUpnjgPso', $video2->video_id);
        $this->assertEquals(1, $video2->sort_order);
    }

    public function test_teacher_can_reorder_and_remove_videos(): void
    {
        $component = Livewire::actingAs($this->teacher)
            ->test(ActivityManager::class)
            ->call('openCreateModal')
            ->set('videos', [
                [
                    'id' => null,
                    'youtube_url' => 'https://youtu.be/videoA11111',
                    'video_id' => 'videoA11111',
                    'title' => 'Video A',
                    'thumbnail_url' => 'https://img.youtube.com/vi/videoA11111/hqdefault.jpg',
                    'sort_order' => 0,
                ],
                [
                    'id' => null,
                    'youtube_url' => 'https://youtu.be/videoB22222',
                    'video_id' => 'videoB22222',
                    'title' => 'Video B',
                    'thumbnail_url' => 'https://img.youtube.com/vi/videoB22222/hqdefault.jpg',
                    'sort_order' => 1,
                ],
            ]);

        // Move second video up
        $component->call('moveVideoUp', 1);
        $videos = $component->get('videos');
        $this->assertEquals('videoB22222', $videos[0]['video_id']);
        $this->assertEquals('videoA11111', $videos[1]['video_id']);

        // Remove video
        $component->call('removeVideo', 0);
        $videosAfterRemove = $component->get('videos');
        $this->assertCount(1, $videosAfterRemove);
        $this->assertEquals('videoA11111', $videosAfterRemove[0]['video_id']);
    }

    public function test_edit_modal_loads_existing_videos_and_syncs_updates(): void
    {
        $activity = Activity::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->nurseryGroup->id,
            'created_by' => $this->teacher->id,
            'title' => 'Colors and Shapes',
            'instructions' => 'Watch and learn colors.',
            'age_group' => '3-4 years',
            'status' => ActivityStatus::Published->value,
            'publish_date' => now()->toDateString(),
        ]);

        $video = ActivityVideo::create([
            'activity_id' => $activity->id,
            'youtube_url' => 'https://youtu.be/origVideo11',
            'video_id' => 'origVideo11',
            'title' => 'Original Video',
            'thumbnail_url' => 'https://img.youtube.com/vi/origVideo11/hqdefault.jpg',
            'sort_order' => 0,
        ]);

        Livewire::actingAs($this->teacher)
            ->test(ActivityManager::class)
            ->call('openEditModal', $activity->id)
            ->assertCount('videos', 1)
            ->call('removeVideo', 0)
            ->call('saveActivity')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('activity_videos', [
            'id' => $video->id,
        ]);
    }

    public function test_teacher_can_open_and_close_watch_modal(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(ActivityManager::class)
            ->call('watchVideo', 'dQw4w9WgXcQ', 'Fun Dance Routine')
            ->assertSet('showWatchModal', true)
            ->assertSet('watchVideoId', 'dQw4w9WgXcQ')
            ->assertSet('watchVideoTitle', 'Fun Dance Routine')
            ->call('closeWatchModal')
            ->assertSet('showWatchModal', false)
            ->assertSet('watchVideoId', null)
            ->assertSet('watchVideoTitle', null);
    }

    public function test_youtube_player_blade_component_renders_privacy_enhanced_iframe(): void
    {
        $html = Blade::render('<x-youtube-player videoId="dQw4w9WgXcQ" title="Demo Video" />');

        $this->assertStringContainsString('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $html);
        $this->assertStringContainsString('title="Demo Video"', $html);
        $this->assertStringContainsString('allowfullscreen', $html);
        $this->assertStringContainsString('referrerpolicy="strict-origin-when-cross-origin"', $html);
    }
}
