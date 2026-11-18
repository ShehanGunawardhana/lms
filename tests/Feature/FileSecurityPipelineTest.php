<?php

use App\Exceptions\FileUploadException;
use App\Jobs\ScanFileAsset;
use App\Models\Institution;
use App\Models\User;
use App\Services\FileService;
use App\Services\InstitutionContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('private');
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(SuperAdminSeeder::class);

    $this->institution = Institution::first();
    app(InstitutionContext::class)->set($this->institution);
});

test('image upload is sanitized and re-encoded', function () {
    $service = app(FileService::class);
    $user = User::factory()->create(['institution_id' => $this->institution->id, 'status' => 'active']);

    $imageFile = UploadedFile::fake()->image('profile.jpg', 200, 200);

    $asset = $service->store($imageFile, 'profile_photo', ['jpg'], $user);

    expect($asset->exists)->toBeTrue();
    expect($asset->kind)->toBe('image');
    expect($asset->extension)->toBe('jpg');
    Storage::disk('private')->assertExists($asset->path);
});

test('zip archive containing executable or script is rejected', function () {
    $service = app(FileService::class);
    $user = User::factory()->create(['institution_id' => $this->institution->id, 'status' => 'active']);

    // Build a ZIP in memory containing an evil script
    $tempZip = tempnam(sys_get_temp_dir(), 'zip');
    $zip = new ZipArchive;
    $zip->open($tempZip, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString('notes.txt', 'clean notes');
    $zip->addFromString('payload.exe', "MZ\x90\x00executable content");
    $zip->close();

    $zipFile = new UploadedFile($tempZip, 'project.zip', 'application/zip', null, true);

    expect(fn () => $service->store($zipFile, 'assignment', ['zip'], $user))
        ->toThrow(FileUploadException::class);

    @unlink($tempZip);
});

test('malware scanner detects eicar signature and quarantines file', function () {
    $service = app(FileService::class);
    $user = User::factory()->create(['institution_id' => $this->institution->id, 'status' => 'active']);

    Queue::fake([ScanFileAsset::class]);

    // Create a PDF containing the standard EICAR test string
    $eicar = 'X5O!P%@AP[4\PZX54(P^)7CC)7}$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!$H+H*';
    $infectedPdf = UploadedFile::fake()->createWithContent('sample.pdf', "%PDF-1.4\n".$eicar."\n%%EOF");

    $asset = $service->store($infectedPdf, 'material', ['pdf'], $user);

    Queue::assertPushed(ScanFileAsset::class);

    // Execute scan job synchronously
    $job = new ScanFileAsset($asset);
    $job->handle();

    $asset->refresh();

    expect($asset->scan_status)->toBe('infected');
    expect($asset->path)->toContain('quarantine/');
    Storage::disk('private')->assertExists($asset->path);

    // Quarantined file cannot be downloaded (HTTP 403)
    $this->actingAs($user)
        ->get($asset->getDownloadUrl())
        ->assertStatus(403);

    // Audit log records infection
    expect(Activity::where('event', 'security.file_infected')->exists())->toBeTrue();
});
