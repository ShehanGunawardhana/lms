<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChunkUploadTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');
        Storage::fake('local');

        $this->institution = Institution::create([
            'name' => 'Apex Academy',
            'slug' => 'apex',
            'type' => 'university',
            'email' => 'admin@apex.edu',
        ]);

        $this->user = User::factory()->create([
            'institution_id' => $this->institution->id,
        ]);
    }

    public function test_guest_cannot_upload_chunks(): void
    {
        $response = $this->postJson(route('files.chunk-upload'), []);

        $response->assertUnauthorized();
    }

    public function test_empty_request_returns_bad_request(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('files.chunk-upload'), []);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    public function test_successful_single_chunk_or_standard_upload(): void
    {
        $content = "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF";
        $file = UploadedFile::fake()->createWithContent('document.pdf', $content);

        $response = $this->actingAs($this->user)->postJson(route('files.chunk-upload'), [
            'file' => $file,
            'purpose' => 'syllabus',
            'allowed_types' => 'pdf,png',
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'original_name' => 'document.pdf',
            ]);

        $this->assertDatabaseHas('file_assets', [
            'original_name' => 'document.pdf',
            'purpose' => 'syllabus',
            'institution_id' => $this->institution->id,
            'uploaded_by' => $this->user->id,
        ]);
    }

    public function test_rejected_upload_with_disallowed_type(): void
    {
        $file = UploadedFile::fake()->create('script.sh', 50, 'text/x-shellscript');

        $response = $this->actingAs($this->user)->postJson(route('files.chunk-upload'), [
            'file' => $file,
            'purpose' => 'syllabus',
            'allowed_types' => 'pdf,png',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
            ]);
    }
}
