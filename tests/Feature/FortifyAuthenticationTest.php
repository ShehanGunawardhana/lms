<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Institution;
use App\Models\User;
use App\Services\InstitutionContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FortifyAuthenticationTest extends TestCase
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

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertSee(__('Sign In'))
            ->assertSee(__('Remember me on this device'));
    }

    public function test_register_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertOk()
            ->assertSee(__('Create Parent Account'))
            ->assertSee(__('Register Parent Account'));
    }

    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertOk()
            ->assertSee(__('Forgot Password'))
            ->assertSee(__('Send Reset Link'));
    }

    public function test_admin_logs_in_and_is_redirected_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        $admin->assignRole(RoleName::Admin->value);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_teacher_logs_in_and_is_redirected_to_teacher_dashboard(): void
    {
        $teacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        $teacher->assignRole(RoleName::Teacher->value);

        $response = $this->post('/login', [
            'email' => $teacher->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($teacher);
        $response->assertRedirect(route('teacher.dashboard'));
    }

    public function test_parent_logs_in_and_is_redirected_to_parent_dashboard(): void
    {
        $parent = User::factory()->create([
            'institution_id' => $this->institution->id,
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        $parent->assignRole(RoleName::Parent->value);

        $response = $this->post('/login', [
            'email' => $parent->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($parent);
        $response->assertRedirect(route('parent.dashboard'));
    }

    public function test_suspended_user_is_blocked_from_logging_in(): void
    {
        $suspended = User::factory()->create([
            'institution_id' => $this->institution->id,
            'password' => Hash::make('password'),
            'status' => 'suspended',
        ]);
        $suspended->assignRole(RoleName::Student->value);

        $response = $this->post('/login', [
            'email' => $suspended->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_pending_user_is_redirected_to_awaiting_approval(): void
    {
        $pending = User::factory()->create([
            'institution_id' => $this->institution->id,
            'password' => Hash::make('password'),
            'status' => 'pending',
        ]);
        $pending->assignRole(RoleName::Parent->value);

        $response = $this->post('/login', [
            'email' => $pending->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($pending);
        $response->assertRedirect(route('awaiting-approval'));
    }

    public function test_user_can_register_as_parent_with_pending_status(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Parent',
            'email' => 'john.parent@example.com',
            'phone' => '+1555123456',
            'consent_terms' => '1',
            'consent_child_safety' => '1',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $user = User::where('email', 'john.parent@example.com')->first();
        expect($user)->not->toBeNull();
        expect($user->status)->toBe('pending');
        expect($user->hasRole(RoleName::Parent->value))->toBeTrue();

        $response->assertRedirect(route('awaiting-approval'));
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
