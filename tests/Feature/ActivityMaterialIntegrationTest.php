<?php

namespace Tests\Feature;

use App\Enums\ActivityStatus;
use App\Enums\ClassKind;
use App\Enums\RoleName;
use App\Livewire\Teacher\ActivityManager;
use App\Models\Activity;
use App\Models\ActivityMaterial;
use App\Models\FileAsset;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ActivityMaterialIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected Institution $otherInstitution;

    protected User $teacher;

    protected User $parentUser;

    protected SchoolClass $nurseryGroup;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

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
            'code' => 'OTH',
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

        $this->parentUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Parent John',
            'status' => 'active',
        ]);
        $this->parentUser->assignRole(RoleName::Parent->value);

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

    public function test_teacher_can_attach_web_links_to_activity(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(ActivityManager::class)
            ->call('openCreateModal')
            ->set('classId', $this->nurseryGroup->id)
            ->set('title', 'Phonics and Sound Explorer')
            ->set('instructions', '1. Listen to sounds.\n2. Open the interactive phonics game link.')
            ->set('ageGroup', '3-4 years')
            ->set('publishDate', now()->toDateString())
            // Add link material
            ->set('materialTitle', 'PBS Kids Phonics Sound Board')
            ->set('materialUrl', 'https://pbskids.org/games/reading')
            ->call('addLinkMaterial')
            ->assertCount('materials', 1)
            ->call('saveActivity')
            ->assertHasNoErrors();

        $activity = Activity::where('title', 'Phonics and Sound Explorer')->firstOrFail();
        $this->assertCount(1, $activity->materials);

        $material = $activity->materials[0];
        $this->assertEquals('link', $material->type);
        $this->assertEquals('PBS Kids Phonics Sound Board', $material->title);
        $this->assertEquals('https://pbskids.org/games/reading', $material->url);
        $this->assertTrue($material->isLink());
        $this->assertFalse($material->isFile());
        $this->assertEquals('🔗', $material->icon());
        $this->assertEquals('https://pbskids.org/games/reading', $material->downloadUrl());
    }

    public function test_link_material_validation(): void
    {
        Livewire::actingAs($this->teacher)
            ->test(ActivityManager::class)
            ->call('openCreateModal')
            ->set('materialTitle', '')
            ->set('materialUrl', 'https://example.com')
            ->call('addLinkMaterial')
            ->assertSet('materialError', 'Please provide a title for the learning resource.')
            ->assertCount('materials', 0)
            ->set('materialTitle', 'Good Title')
            ->set('materialUrl', 'not-a-valid-url')
            ->call('addLinkMaterial')
            ->assertSet('materialError', 'Please provide a valid web link (e.g. https://...).')
            ->assertCount('materials', 0);
    }

    public function test_teacher_can_upload_and_attach_file_materials(): void
    {
        // Create genuine PDF content with %PDF- header for magic bytes verification
        $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";
        $file = UploadedFile::fake()->createWithContent('SensoryWorksheet.pdf', $pdfContent);

        Livewire::actingAs($this->teacher)
            ->test(ActivityManager::class)
            ->call('openCreateModal')
            ->set('classId', $this->nurseryGroup->id)
            ->set('title', 'Handwriting & Tracing Fun')
            ->set('instructions', 'Print the attached worksheet and trace the letter lines.')
            ->set('ageGroup', '3-4 years')
            ->set('publishDate', now()->toDateString())
            // Upload file
            ->set('materialTitle', 'Printable Tracing Worksheet')
            ->set('materialFile', $file)
            ->call('uploadFileMaterial')
            ->assertCount('materials', 1)
            ->call('saveActivity')
            ->assertHasNoErrors();

        $activity = Activity::where('title', 'Handwriting & Tracing Fun')->firstOrFail();
        $this->assertCount(1, $activity->materials);

        $material = $activity->materials[0];
        $this->assertEquals('file', $material->type);
        $this->assertEquals('Printable Tracing Worksheet', $material->title);
        $this->assertNotNull($material->file_asset_id);
        $this->assertTrue($material->isFile());
        $this->assertEquals('📄', $material->icon());
        $this->assertNotNull($material->downloadUrl());

        $fileAsset = $material->fileAsset;
        $this->assertNotNull($fileAsset);
        $this->assertEquals('pdf', $fileAsset->extension);
        $this->assertEquals('material', $fileAsset->purpose);
    }

    public function test_teacher_can_reorder_and_remove_materials(): void
    {
        $component = Livewire::actingAs($this->teacher)
            ->test(ActivityManager::class)
            ->call('openCreateModal')
            ->set('materials', [
                [
                    'id' => null,
                    'type' => 'link',
                    'title' => 'Resource 1',
                    'url' => 'https://example.com/1',
                    'file_asset_id' => null,
                    'file_name' => null,
                    'file_size' => null,
                    'sort_order' => 0,
                ],
                [
                    'id' => null,
                    'type' => 'link',
                    'title' => 'Resource 2',
                    'url' => 'https://example.com/2',
                    'file_asset_id' => null,
                    'file_name' => null,
                    'file_size' => null,
                    'sort_order' => 1,
                ],
            ]);

        // Move second material up
        $component->call('moveMaterialUp', 1);
        $materials = $component->get('materials');
        $this->assertEquals('Resource 2', $materials[0]['title']);
        $this->assertEquals('Resource 1', $materials[1]['title']);

        // Remove first material
        $component->call('removeMaterial', 0);
        $materialsAfter = $component->get('materials');
        $this->assertCount(1, $materialsAfter);
        $this->assertEquals('Resource 1', $materialsAfter[0]['title']);
    }

    public function test_edit_modal_loads_existing_materials_and_syncs(): void
    {
        $activity = Activity::create([
            'institution_id' => $this->institution->id,
            'class_id' => $this->nurseryGroup->id,
            'created_by' => $this->teacher->id,
            'title' => 'Gardening and Plants',
            'instructions' => 'Plant seeds in small pots.',
            'age_group' => '3-4 years',
            'status' => ActivityStatus::Published->value,
            'publish_date' => now()->toDateString(),
        ]);

        $material = ActivityMaterial::create([
            'activity_id' => $activity->id,
            'type' => 'link',
            'title' => 'Plant Care Guide Link',
            'url' => 'https://kidsgardening.org',
            'sort_order' => 0,
        ]);

        Livewire::actingAs($this->teacher)
            ->test(ActivityManager::class)
            ->call('openEditModal', $activity->id)
            ->assertCount('materials', 1)
            ->call('removeMaterial', 0)
            ->call('saveActivity')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('activity_materials', [
            'id' => $material->id,
        ]);
    }

    public function test_signed_download_route_allows_authorized_institution_users(): void
    {
        $fileAsset = FileAsset::create([
            'institution_id' => $this->institution->id,
            'uuid' => (string) Str::uuid(),
            'disk' => 'local',
            'path' => 'materials/test-doc.pdf',
            'original_name' => 'WeeklyStory.pdf',
            'extension' => 'pdf',
            'mime_verified' => 'application/pdf',
            'size' => 102400,
            'hash_sha256' => hash('sha256', 'sample'),
            'kind' => 'document',
            'scan_status' => 'clean',
            'uploaded_by' => $this->teacher->id,
            'purpose' => 'material',
        ]);

        Storage::disk('local')->put('materials/test-doc.pdf', '%PDF-1.4 test document');

        $downloadUrl = $fileAsset->getDownloadUrl();

        // 1. Teacher can download
        $this->actingAs($this->teacher)
            ->get($downloadUrl)
            ->assertOk()
            ->assertDownload('WeeklyStory.pdf');

        // 2. Parent in same institution can download learning material
        $this->actingAs($this->parentUser)
            ->get($downloadUrl)
            ->assertOk()
            ->assertDownload('WeeklyStory.pdf');

        // 3. User in different institution is forbidden
        $otherUser = User::factory()->create([
            'institution_id' => $this->otherInstitution->id,
            'status' => 'active',
        ]);
        $otherUser->assignRole(RoleName::Parent->value);

        $this->actingAs($otherUser)
            ->get($downloadUrl)
            ->assertForbidden();
    }
}
