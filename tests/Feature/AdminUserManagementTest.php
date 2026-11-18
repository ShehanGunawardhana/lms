<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Enums\UserStatus;
use App\Livewire\Admin\UserManager;
use App\Models\Institution;
use App\Models\User;
use App\Services\InstitutionContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminSeeder::class);

        $this->institution = Institution::first();
        app(InstitutionContext::class)->set($this->institution);

        $this->admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => UserStatus::Active->value,
            'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1'])),
            'two_factor_confirmed_at' => now(),
        ]);
        $this->admin->assignRole(RoleName::Admin->value);
    }

    public function test_guest_or_non_admin_cannot_access_user_manager(): void
    {
        $this->get(route('admin.users'))->assertRedirect(route('login'));

        $teacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => UserStatus::Active->value,
        ]);
        $teacher->assignRole(RoleName::Teacher->value);

        $this->actingAs($teacher)->get(route('admin.users'))->assertForbidden();
    }

    public function test_admin_without_two_factor_is_redirected(): void
    {
        $insecureAdmin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => UserStatus::Active->value,
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);
        $insecureAdmin->assignRole(RoleName::Admin->value);

        $this->actingAs($insecureAdmin)->get(route('admin.users'))
            ->assertRedirect(route('two-factor.setup'));
    }

    public function test_admin_with_two_factor_can_render_user_manager(): void
    {
        $this->actingAs($this->admin)->get(route('admin.users'))
            ->assertOk()
            ->assertSee('Institutional User Directory')
            ->assertSee('Total Users');
    }

    public function test_admin_can_search_users(): void
    {
        $userA = User::factory()->create([
            'name' => 'Alice Wonder',
            'email' => 'alice@wonderland.test',
            'phone' => '+94771112233',
            'institution_id' => $this->institution->id,
            'status' => UserStatus::Active->value,
        ]);

        $userB = User::factory()->create([
            'name' => 'Bob Builder',
            'email' => 'bob@builder.test',
            'phone' => '+94772223344',
            'institution_id' => $this->institution->id,
            'status' => UserStatus::Active->value,
        ]);

        $this->actingAs($this->admin);

        Livewire::test(UserManager::class)
            ->set('search', 'Alice')
            ->assertSee('Alice Wonder')
            ->assertDontSee('Bob Builder')
            ->set('search', 'builder.test')
            ->assertSee('Bob Builder')
            ->assertDontSee('Alice Wonder');
    }

    public function test_admin_can_filter_by_status(): void
    {
        $activeUser = User::factory()->create([
            'name' => 'Active User Person',
            'institution_id' => $this->institution->id,
            'status' => UserStatus::Active->value,
        ]);

        $suspendedUser = User::factory()->create([
            'name' => 'Suspended User Person',
            'institution_id' => $this->institution->id,
            'status' => UserStatus::Suspended->value,
        ]);

        $this->actingAs($this->admin);

        Livewire::test(UserManager::class)
            ->set('status', 'suspended')
            ->assertSee('Suspended User Person')
            ->assertDontSee('Active User Person')
            ->set('status', 'active')
            ->assertSee('Active User Person')
            ->assertDontSee('Suspended User Person');
    }

    public function test_admin_can_filter_by_role(): void
    {
        $teacher = User::factory()->create([
            'name' => 'Professor Plum',
            'institution_id' => $this->institution->id,
            'status' => UserStatus::Active->value,
        ]);
        $teacher->assignRole(RoleName::Teacher->value);

        $parent = User::factory()->create([
            'name' => 'Mrs Peacock',
            'institution_id' => $this->institution->id,
            'status' => UserStatus::Active->value,
        ]);
        $parent->assignRole(RoleName::Parent->value);

        $this->actingAs($this->admin);

        Livewire::test(UserManager::class)
            ->set('role', RoleName::Teacher->value)
            ->assertSee('Professor Plum')
            ->assertDontSee('Mrs Peacock')
            ->set('role', RoleName::Parent->value)
            ->assertSee('Mrs Peacock')
            ->assertDontSee('Professor Plum');
    }

    public function test_admin_can_suspend_user_with_mandatory_reason_and_audit(): void
    {
        $targetUser = User::factory()->create([
            'name' => 'Problematic Account',
            'institution_id' => $this->institution->id,
            'status' => UserStatus::Active->value,
        ]);
        $targetUser->assignRole(RoleName::Student->value);

        $this->actingAs($this->admin);

        Livewire::test(UserManager::class)
            ->call('openSuspendModal', $targetUser->id)
            ->assertSet('showSuspendModal', true)
            ->set('suspensionReason', '')
            ->call('suspendUser')
            ->assertHasErrors(['suspensionReason'])
            ->set('suspensionReason', 'Violated campus safety policy and computer code of conduct.')
            ->call('suspendUser')
            ->assertSet('showSuspendModal', false);

        $targetUser->refresh();
        $this->assertEquals(UserStatus::Suspended->value, $targetUser->status);

        // Verify audit log
        $activity = Activity::where('event', 'user.suspended')
            ->where('subject_id', $targetUser->id)
            ->first();

        $this->assertNotNull($activity);
        $this->assertEquals($this->admin->id, $activity->causer_id);
        $this->assertStringContainsString('Violated campus safety', $activity->description);

        // Verify suspended user cannot log in
        auth()->logout();
        $this->post(route('login'), [
            'email' => $targetUser->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');
    }

    public function test_admin_can_reactivate_suspended_user_and_audit(): void
    {
        $suspendedUser = User::factory()->create([
            'name' => 'Repentant Account',
            'institution_id' => $this->institution->id,
            'status' => UserStatus::Suspended->value,
        ]);
        $suspendedUser->assignRole(RoleName::Student->value);

        $this->actingAs($this->admin);

        Livewire::test(UserManager::class)
            ->call('reactivateUser', $suspendedUser->id);

        $suspendedUser->refresh();
        $this->assertEquals(UserStatus::Active->value, $suspendedUser->status);

        // Verify audit log
        $activity = Activity::where('event', 'user.reactivated')
            ->where('subject_id', $suspendedUser->id)
            ->first();

        $this->assertNotNull($activity);
        $this->assertEquals($this->admin->id, $activity->causer_id);
    }

    public function test_admin_can_deactivate_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Former Faculty',
            'institution_id' => $this->institution->id,
            'status' => UserStatus::Active->value,
        ]);
        $user->assignRole(RoleName::Teacher->value);

        $this->actingAs($this->admin);

        Livewire::test(UserManager::class)
            ->call('openDeactivateModal', $user->id)
            ->assertSet('showDeactivateModal', true)
            ->set('deactivationReason', 'Resigned effective end of term.')
            ->call('deactivateUser')
            ->assertSet('showDeactivateModal', false);

        $user->refresh();
        $this->assertEquals(UserStatus::Deactivated->value, $user->status);

        // Verify audit log
        $activity = Activity::where('event', 'user.deactivated')
            ->where('subject_id', $user->id)
            ->first();

        $this->assertNotNull($activity);
        $this->assertEquals($this->admin->id, $activity->causer_id);

        // Verify deactivated user cannot log in
        auth()->logout();
        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');
    }

    public function test_admin_cannot_suspend_themselves(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(UserManager::class)
            ->call('openSuspendModal', $this->admin->id)
            ->assertForbidden();
    }

    public function test_admin_cannot_suspend_super_admin(): void
    {
        $superAdmin = User::role(RoleName::SuperAdmin->value)->first();
        $this->assertNotNull($superAdmin);

        $this->actingAs($this->admin);

        Livewire::test(UserManager::class)
            ->call('openSuspendModal', $superAdmin->id)
            ->assertForbidden();
    }

    public function test_multi_institution_isolation_prevents_viewing_other_institution_users(): void
    {
        $otherInstitution = Institution::create([
            'name' => 'Other Academy',
            'slug' => 'other-academy',
            'timezone' => 'Asia/Colombo',
            'locale' => 'en',
            'type' => 'nursery',
        ]);

        $otherUser = User::factory()->create([
            'name' => 'Foreign User Person',
            'institution_id' => $otherInstitution->id,
            'status' => UserStatus::Active->value,
        ]);

        $this->actingAs($this->admin);

        Livewire::test(UserManager::class)
            ->assertDontSee('Foreign User Person');
    }
}
