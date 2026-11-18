<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\User;
use App\Services\InstitutionContext;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class LocalizationAndTimezoneTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institution = Institution::create([
            'name' => 'Colombo Campus',
            'slug' => 'colombo',
            'type' => 'university',
            'email' => 'info@colombo.edu',
            'timezone' => 'Asia/Colombo',
            'locale' => 'en',
        ]);

        app(InstitutionContext::class)->set($this->institution);
    }

    public function test_locale_switch_updates_session_and_redirects(): void
    {
        $response = $this->get(route('locale.switch', ['locale' => 'si']));

        $response->assertRedirect();
        $response->assertSessionHas('locale', 'si');
    }

    public function test_locale_switch_persists_to_authenticated_user(): void
    {
        $user = User::factory()->create([
            'institution_id' => $this->institution->id,
            'locale' => 'en',
        ]);

        $response = $this->actingAs($user)->get(route('locale.switch', ['locale' => 'si']));

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'locale' => 'si',
        ]);
    }

    public function test_translation_files_resolve_correctly_per_locale(): void
    {
        App::setLocale('en');
        expect(__('portal.nav.dashboard'))->toBe('Dashboard');
        expect(__('portal.status.active'))->toBe('Active');

        App::setLocale('si');
        expect(__('portal.nav.dashboard'))->toBe('පාලක පුවරුව');
        expect(__('portal.status.active'))->toBe('ක්‍රියාකාරී');
    }

    public function test_current_timezone_resolves_institution_timezone(): void
    {
        expect(current_timezone())->toBe('Asia/Colombo');
    }

    public function test_to_institution_tz_converts_utc_to_colombo_time(): void
    {
        // 08:00 UTC = 13:30 Asia/Colombo (+05:30)
        $utcTime = Carbon::parse('2026-11-18 08:00:00', 'UTC');

        $converted = to_institution_tz($utcTime);

        expect($converted)->not->toBeNull();
        expect($converted->timezoneName)->toBe('Asia/Colombo');
        expect($converted->format('H:i'))->toBe('13:30');
    }

    public function test_to_utc_converts_institution_local_time_to_utc(): void
    {
        // 13:30 Asia/Colombo = 08:00 UTC
        $localTime = Carbon::parse('2026-11-18 13:30:00', 'Asia/Colombo');

        $utc = to_utc($localTime);

        expect($utc)->not->toBeNull();
        expect($utc->timezoneName)->toBe('UTC');
        expect($utc->format('H:i'))->toBe('08:00');
    }

    public function test_format_portal_datetime_formats_in_institution_timezone(): void
    {
        $utcTime = Carbon::parse('2026-11-18 08:00:00', 'UTC');

        $formatted = format_portal_datetime($utcTime, 'Y-m-d H:i');

        expect($formatted)->toBe('2026-11-18 13:30');
    }
}
