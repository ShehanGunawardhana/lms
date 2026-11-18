<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Institution;
use App\Models\User;
use App\Services\InstitutionContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicLandingPageTest extends TestCase
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

    public function test_guest_can_render_public_landing_page(): void
    {
        $response = $this->get(route('home', absolute: false) ?: '/');

        $response->assertOk();
        $response->assertSee($this->institution->name);
        $response->assertSee('Unified Education &amp; Nursery Excellence', false);
        $response->assertSee('Admissions &amp; Learning Portal Open', false);
        $response->assertSee('Register Parent Account');
        $response->assertSee('Staff &amp; Student Login', false);
    }

    public function test_landing_page_displays_institution_branding(): void
    {
        $this->institution->update(['name' => 'Royal Horizon International Academy']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Royal Horizon International Academy');
    }

    public function test_landing_page_renders_module_cards(): void
    {
        config(['portal.modules.nursery' => true]);
        config(['portal.modules.institute' => true]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Nursery &amp; Kindergarten Module', false);
        $response->assertSee('Tuition &amp; Academic Institute Module', false);
        $response->assertSee('Digital Pass &amp; QR Attendance', false);
        $response->assertSee('Bank Slip Reconciliations');
    }

    public function test_authenticated_user_sees_dashboard_shortcut(): void
    {
        $user = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $user->assignRole(RoleName::Teacher->value);

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertSee('Open My Workspace');
        $response->assertSee(route('dashboard'));
    }
}
