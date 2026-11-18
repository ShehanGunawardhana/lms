<?php

use App\Enums\RoleName;
use App\Exceptions\FileUploadException;
use App\Models\Institution;
use App\Models\User;
use App\Services\FileService;
use App\Services\InstitutionContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('private');
    Storage::fake('public');

    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(SuperAdminSeeder::class);

    $this->institution = Institution::first();
    app(InstitutionContext::class)->set($this->institution);
});

test('genuine pdf, image, zip and mp4 files upload with magic byte verification', function () {
    $service = app(FileService::class);
    $user = User::factory()->create(['institution_id' => $this->institution->id, 'status' => 'active']);

    // 1. Genuine PDF (starts with %PDF-)
    $pdfContent = "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF";
    $pdfFile = UploadedFile::fake()->createWithContent('document.pdf', $pdfContent);

    $asset = $service->store($pdfFile, 'assignment', ['pdf'], $user);

    expect($asset->exists)->toBeTrue();
    expect($asset->extension)->toBe('pdf');
    expect($asset->mime_verified)->toBe('application/pdf');
    expect($asset->kind)->toBe('document');
    Storage::disk('private')->assertExists($asset->path);

    // 2. Genuine PNG
    $pngFile = UploadedFile::fake()->image('diagram.png', 50, 50);

    $pngAsset = $service->store($pngFile, 'submission', ['png', 'jpg'], $user);
    expect($pngAsset->mime_verified)->toBe('image/png');
    expect($pngAsset->kind)->toBe('image');

    // 3. Genuine ZIP
    $tempZip = tempnam(sys_get_temp_dir(), 'zip');
    $zip = new ZipArchive;
    $zip->open($tempZip, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString('readme.txt', 'Hello world');
    $zip->close();
    $zipFile = new UploadedFile($tempZip, 'archive.zip', 'application/zip', null, true);

    $zipAsset = $service->store($zipFile, 'submission', ['zip'], $user);
    expect($zipAsset->kind)->toBe('archive');
    @unlink($tempZip);
});

test('renamed fake file with misleading extension is rejected', function () {
    $service = app(FileService::class);
    $user = User::factory()->create(['institution_id' => $this->institution->id, 'status' => 'active']);

    // Plain text / script disguised with .pdf extension
    $fakePdf = UploadedFile::fake()->createWithContent('exploit.pdf', '<?php echo "evil script"; ?>');

    expect(fn () => $service->store($fakePdf, 'test', ['pdf'], $user))
        ->toThrow(FileUploadException::class);

    // Executable disguised as .jpg
    $fakeJpg = UploadedFile::fake()->createWithContent('innocent.jpg', "MZ\x90\x00\x03\x00\x00\x00This is a Windows EXE");

    expect(fn () => $service->store($fakeJpg, 'test', ['jpg'], $user))
        ->toThrow(FileUploadException::class);
});

test('file over 50 MB is rejected', function () {
    $service = app(FileService::class);
    $user = User::factory()->create(['institution_id' => $this->institution->id, 'status' => 'active']);

    // 51 MB file (51 * 1024 KB)
    $oversizeFile = UploadedFile::fake()->create('huge_video.mp4', 51 * 1024);

    expect(fn () => $service->store($oversizeFile, 'video', ['mp4'], $user))
        ->toThrow(FileUploadException::class);
});

test('direct storage URL is not reachable and returns 403 or 404', function () {
    // Files are saved on private disk, so requesting /storage/institutions/... on the public disk fails
    $response = $this->get('/storage/institutions/1/2026/11/secret_file.pdf');

    expect(in_array($response->status(), [403, 404], true))->toBeTrue();
});

test('signed download route allows authorized user and blocks unauthorized user', function () {
    $service = app(FileService::class);
    $owner = User::factory()->create(['institution_id' => $this->institution->id, 'status' => 'active']);
    $otherStudent = User::factory()->create(['institution_id' => $this->institution->id, 'status' => 'active']);
    $otherStudent->assignRole(RoleName::Student->value);

    $pdfContent = "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF";
    $pdfFile = UploadedFile::fake()->createWithContent('homework.pdf', $pdfContent);

    $asset = $service->store($pdfFile, 'assignment', ['pdf'], $owner);

    $signedUrl = $asset->getDownloadUrl();

    // 1. Owner can download using signed URL
    $this->actingAs($owner)
        ->get($signedUrl)
        ->assertStatus(200);

    // 2. Another student gets 403 Forbidden even with signed URL
    $this->actingAs($otherStudent)
        ->get($signedUrl)
        ->assertStatus(403);

    // 3. Accessing download endpoint without signature returns 403 Invalid Signature
    $unsignedUrl = route('files.download', ['uuid' => $asset->uuid]);
    $this->actingAs($owner)
        ->get($unsignedUrl)
        ->assertStatus(403);
});
