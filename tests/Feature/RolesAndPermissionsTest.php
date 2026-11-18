<?php

use App\Enums\RoleName;
use App\Models\Institution;
use App\Models\User;
use App\Policies\BasePolicy;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

class DummyResourcePolicy extends BasePolicy
{
    public function view(User $user, mixed $model): bool
    {
        return $this->belongsToSameInstitution($user, $model);
    }
}

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(SuperAdminSeeder::class);

    Route::middleware(['web', 'role:teacher'])->get('/test-teacher-only', function () {
        return response()->json(['message' => 'welcome teacher']);
    });

    Route::middleware(['web', 'permission:printing.operate'])->get('/test-print-counter', function () {
        return response()->json(['message' => 'printing counter access']);
    });
});

test('super admin seeder creates active super admin user with role and institution', function () {
    $superAdmin = User::where('email', 'superadmin@portal.local')->first();

    expect($superAdmin)->not->toBeNull();
    expect($superAdmin->hasRole(RoleName::SuperAdmin->value))->toBeTrue();
    expect($superAdmin->institution)->not->toBeNull();
    expect($superAdmin->isActive())->toBeTrue();
    expect($superAdmin->can('users.create'))->toBeTrue();
});

test('roles have correct mapped permissions', function () {
    $teacherUser = User::factory()->create(['status' => 'active']);
    $teacherUser->assignRole(RoleName::Teacher->value);

    expect($teacherUser->can('activities.manage'))->toBeTrue();
    expect($teacherUser->can('quizzes.manage'))->toBeTrue();
    expect($teacherUser->can('settings.manage'))->toBeFalse(); // Only admin/superadmin

    $printUser = User::factory()->create(['status' => 'active']);
    $printUser->assignRole(RoleName::PrintingStaff->value);

    expect($printUser->can('printing.operate'))->toBeTrue();
    expect($printUser->can('attendance.scan'))->toBeFalse();
});

test('role middleware allows authorized role and denies others with 403', function () {
    $teacher = User::factory()->create(['status' => 'active']);
    $teacher->assignRole(RoleName::Teacher->value);

    $student = User::factory()->create(['status' => 'active']);
    $student->assignRole(RoleName::Student->value);

    // Teacher accessing teacher route -> 200
    $this->actingAs($teacher)
        ->get('/test-teacher-only')
        ->assertStatus(200);

    // Student accessing teacher route -> 403
    $this->actingAs($student)
        ->get('/test-teacher-only')
        ->assertStatus(403);
});

test('permission middleware verifies specific permission', function () {
    $printStaff = User::factory()->create(['status' => 'active']);
    $printStaff->assignRole(RoleName::PrintingStaff->value);

    $parent = User::factory()->create(['status' => 'active']);
    $parent->assignRole(RoleName::Parent->value);

    $this->actingAs($printStaff)
        ->get('/test-print-counter')
        ->assertStatus(200);

    $this->actingAs($parent)
        ->get('/test-print-counter')
        ->assertStatus(403);
});

test('base policy checks same institution and blocks suspended user', function () {
    $inst1 = Institution::create(['name' => 'Inst 1', 'slug' => 'inst-1']);
    $inst2 = Institution::create(['name' => 'Inst 2', 'slug' => 'inst-2']);

    $userInst1 = User::factory()->create(['institution_id' => $inst1->id, 'status' => 'active']);
    $suspendedUser = User::factory()->create(['institution_id' => $inst1->id, 'status' => 'suspended']);
    $superAdmin = User::where('email', 'superadmin@portal.local')->first();

    $policy = new DummyResourcePolicy;

    $model1 = (object) ['institution_id' => $inst1->id];
    $model2 = (object) ['institution_id' => $inst2->id];

    // Same institution
    expect($policy->view($userInst1, $model1))->toBeTrue();
    // Different institution -> 403 / false
    expect($policy->view($userInst1, $model2))->toBeFalse();

    // Super Admin bypasses
    expect($policy->before($superAdmin, 'view'))->toBeTrue();

    // Suspended user blocked
    expect($policy->before($suspendedUser, 'view'))->toBeFalse();
});
