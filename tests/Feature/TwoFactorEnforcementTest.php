<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Livewire\Auth\TwoFactorSetup;
use App\Models\Institution;
use App\Models\User;
use App\Services\InstitutionContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Livewire\Livewire;
use Tests\TestCase;

class TwoFactorEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminSeeder::class);

        $this->institution = Institution::first();
        app(InstitutionContext::class)->set($this->institution);
    }

    public function test_admin_without_two_factor_is_redirected_to_setup(): void
    {
        $admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);
        $admin->assignRole(RoleName::Admin->value);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertRedirect(route('two-factor.setup'));
        $response->assertSessionHas('warning');
    }

    public function test_teacher_without_two_factor_can_access_dashboard_freely(): void
    {
        $teacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);
        $teacher->assignRole(RoleName::Teacher->value);

        $response = $this->actingAs($teacher)->get(route('teacher.dashboard'));

        $response->assertOk();
    }

    public function test_admin_with_confirmed_two_factor_can_access_dashboard(): void
    {
        $admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
            'two_factor_confirmed_at' => now(),
        ]);
        $admin->assignRole(RoleName::Admin->value);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
    }

    public function test_admin_can_setup_and_confirm_two_factor_via_livewire(): void
    {
        $admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $admin->assignRole(RoleName::Admin->value);

        $this->actingAs($admin);

        // 1. Enable 2FA
        Livewire::test(TwoFactorSetup::class)
            ->call('enable')
            ->assertSet('showingQr', true);

        $admin->refresh();
        expect($admin->two_factor_secret)->not->toBeNull();

        // 2. Mock provider to verify confirmation code
        $provider = $this->mock(TwoFactorAuthenticationProvider::class);
        $provider->shouldReceive('verify')->andReturn(true);

        // 3. Confirm 2FA
        Livewire::test(TwoFactorSetup::class)
            ->set('code', '123456')
            ->call('confirm')
            ->assertHasNoErrors()
            ->assertSet('showingRecoveryCodes', true);

        $admin->refresh();
        expect($admin->two_factor_confirmed_at)->not->toBeNull();
        expect($admin->recoveryCodes())->not->toBeEmpty();
    }

    public function test_admin_cannot_disable_two_factor(): void
    {
        $admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
            'two_factor_confirmed_at' => now(),
        ]);
        $admin->assignRole(RoleName::Admin->value);

        $this->actingAs($admin);

        Livewire::test(TwoFactorSetup::class)
            ->call('disable')
            ->assertSee(__('Two-factor authentication is mandatory for administrators and cannot be disabled.'));

        $admin->refresh();
        expect($admin->two_factor_secret)->not->toBeNull();
    }
}
