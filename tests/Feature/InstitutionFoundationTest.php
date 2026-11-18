<?php

use App\Enums\InstitutionType;
use App\Models\Institution;
use App\Services\InstitutionContext;
use App\Services\SettingsService;
use App\Support\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

class TestBusinessModel extends Model
{
    use BelongsToInstitution;

    protected $table = 'test_business_models';

    protected $fillable = ['name', 'institution_id'];
}

beforeEach(function () {
    if (! Schema::hasTable('test_business_models')) {
        Schema::create('test_business_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });
    }

    Route::middleware(['module:nursery'])->get('/test-nursery-route', function () {
        return response()->json(['status' => 'ok']);
    });

    Route::middleware(['module:institute'])->get('/test-institute-route', function () {
        return response()->json(['status' => 'ok']);
    });
});

test('institution can be created with enum type and retrieved', function () {
    $institution = Institution::create([
        'name' => 'Royal Academy',
        'slug' => 'royal-academy',
        'type' => InstitutionType::Institute,
        'timezone' => 'Asia/Colombo',
        'locale' => 'en',
    ]);

    expect($institution->exists)->toBeTrue();
    expect($institution->type)->toBe(InstitutionType::Institute);
    expect($institution->type->label())->toBe(__('Tuition / Vocational Institute'));
});

test('settings service reads config defaults and allows database override with type casting', function () {
    $institution = Institution::create([
        'name' => 'Apex College',
        'slug' => 'apex-college',
        'type' => InstitutionType::Institute,
    ]);

    $context = app(InstitutionContext::class);
    $context->set($institution);

    $settings = app(SettingsService::class);

    // Reads default from config/portal.php
    expect($settings->get('attendance.window_before_min'))->toBe(30);
    expect($settings->get('uploads.max_size_mb'))->toBe(50);
    expect($settings->isModuleEnabled('nursery'))->toBeTrue();

    // Override in database
    $settings->set('attendance.window_before_min', 45);
    $settings->set('modules.nursery', false);
    $settings->set('custom.tags', ['science', 'math']);

    expect($settings->get('attendance.window_before_min'))->toBe(45);
    expect($settings->isModuleEnabled('nursery'))->toBeFalse();
    expect($settings->get('custom.tags'))->toBe(['science', 'math']);
});

test('belongs to institution trait scopes queries and sets institution_id on creation', function () {
    $inst1 = Institution::create(['name' => 'Campus Alpha', 'slug' => 'campus-alpha']);
    $inst2 = Institution::create(['name' => 'Campus Beta', 'slug' => 'campus-beta']);

    $context = app(InstitutionContext::class);

    // Switch to inst1
    $context->set($inst1);
    $record1 = TestBusinessModel::create(['name' => 'Item 1']);
    expect($record1->institution_id)->toBe($inst1->id);

    // Switch to inst2
    $context->set($inst2);
    $record2 = TestBusinessModel::create(['name' => 'Item 2']);
    expect($record2->institution_id)->toBe($inst2->id);

    // Query under inst2 only finds inst2 records
    expect(TestBusinessModel::count())->toBe(1);
    expect(TestBusinessModel::first()->name)->toBe('Item 2');

    // Switch back to inst1
    $context->set($inst1);
    expect(TestBusinessModel::count())->toBe(1);
    expect(TestBusinessModel::first()->name)->toBe('Item 1');

    // withoutInstitution scope returns all
    expect(TestBusinessModel::withoutInstitution()->count())->toBe(2);
});

test('ensure module enabled middleware blocks disabled module with 404', function () {
    $institution = Institution::create(['name' => 'Institute Only', 'slug' => 'institute-only']);
    app(InstitutionContext::class)->set($institution);

    $settings = app(SettingsService::class);
    $settings->set('modules.nursery', false);
    $settings->set('modules.institute', true);

    $this->get('/test-institute-route')->assertStatus(200);
    $this->get('/test-nursery-route')->assertStatus(404);
});
