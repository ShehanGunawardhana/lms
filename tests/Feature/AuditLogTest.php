<?php

use App\Enums\RoleName;
use App\Livewire\Admin\AuditLogViewer;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(SuperAdminSeeder::class);
});

test('audit helper records event with causer and institution context', function () {
    $admin = User::factory()->create(['status' => 'active']);
    $admin->assignRole(RoleName::Admin->value);

    $this->actingAs($admin);

    $targetUser = User::factory()->create(['name' => 'John Parent', 'status' => 'pending']);

    audit('parent.approved', 'Approved parent account registration', $targetUser, ['reason' => 'Identity verified']);

    $log = Activity::where('event', 'parent.approved')->first();

    expect($log)->not->toBeNull();
    expect($log->description)->toBe('Approved parent account registration');
    expect($log->causer_id)->toBe($admin->id);
    expect($log->subject_id)->toBe($targetUser->id);
    expect($log->properties['reason'])->toBe('Identity verified');
});

test('unauthorized user gets 403 on audit viewer', function () {
    $student = User::factory()->create(['status' => 'active']);
    $student->assignRole(RoleName::Student->value);

    $this->actingAs($student)
        ->get('/admin/audit')
        ->assertStatus(403);
});

test('authorized admin can access audit viewer and livewire filters events', function () {
    $admin = User::factory()->create(['status' => 'active']);
    $admin->assignRole(RoleName::Admin->value);

    // Create sample audit entries
    activity()->event('payment.approved')->log('Approved bank slip #1042');
    activity()->event('student.registered')->log('Registered student John Doe');

    $this->actingAs($admin)
        ->get('/admin/audit')
        ->assertStatus(200)
        ->assertSee('System Audit Log')
        ->assertSee('Approved bank slip #1042');

    Livewire::actingAs($admin)
        ->test(AuditLogViewer::class)
        ->assertSee('Approved bank slip #1042')
        ->assertSee('Registered student John Doe')
        ->set('search', 'bank slip')
        ->assertSee('Approved bank slip #1042')
        ->assertDontSee('Registered student John Doe');
});
