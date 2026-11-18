<?php

namespace Tests\Feature;

use App\Contracts\SmsChannelInterface;
use App\Enums\RoleName;
use App\Models\ChildRegistration;
use App\Models\Institution;
use App\Models\ParentProfile;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ParentRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institution = Institution::create([
            'name' => 'Royal Palms Nursery & Institute',
            'slug' => 'royal-palms',
            'code' => 'RPNI',
            'type' => 'nursery',
            'is_active' => true,
        ]);

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_parent_registration_screen_renders_with_child_and_consent_fields(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee(__('Parent / Guardian Details'));
        $response->assertSee(__('Child Details (Nursery)'));
        $response->assertSee(__('Target Nursery Age Group'));
        $response->assertSee(__('Parental Consents & Authorizations'));
        $response->assertSee(__('Educational Activity Photo & Media Release Consent'));
        $response->assertSee(__('Register Parent Account & Submit for Review'));
    }

    public function test_parent_can_register_with_parent_and_child_details_and_photo_consent(): void
    {
        $response = $this->post('/register', [
            'name' => 'Samantha Smith',
            'email' => 'samantha.smith@example.com',
            'phone' => '+94771234567',
            'relationship' => 'mother',
            'child_first_name' => 'Oliver',
            'child_last_name' => 'Smith',
            'child_dob' => '2023-05-14',
            'child_gender' => 'male',
            'nursery_group' => 'playgroup',
            'medical_notes' => 'Mild peanut allergy. Carries antihistamine.',
            'dietary_needs' => 'No peanut products',
            'consent_terms' => '1',
            'consent_child_safety' => '1',
            'consent_photo' => '1',
            'password' => 'SecurePass123!#',
            'password_confirmation' => 'SecurePass123!#',
        ]);

        $response->assertRedirect(route('awaiting-approval'));

        $user = User::where('email', 'samantha.smith@example.com')->first();
        expect($user)->not->toBeNull();
        expect($user->name)->toBe('Samantha Smith');
        expect($user->phone)->toBe('+94771234567');
        expect($user->status)->toBe('pending');
        expect($user->hasRole(RoleName::Parent->value))->toBeTrue();

        $profile = ParentProfile::where('user_id', $user->id)->first();
        expect($profile)->not->toBeNull();
        expect($profile->relationship)->toBe('mother');
        expect($profile->consent_terms)->toBeTrue();
        expect($profile->consent_child_safety)->toBeTrue();
        expect($profile->consent_photo)->toBeTrue();
        expect($profile->consented_at)->not->toBeNull();

        $child = ChildRegistration::where('parent_profile_id', $profile->id)->first();
        expect($child)->not->toBeNull();
        expect($child->first_name)->toBe('Oliver');
        expect($child->last_name)->toBe('Smith');
        expect($child->full_name)->toBe('Oliver Smith');
        expect($child->nursery_group)->toBe('playgroup');
        expect($child->medical_notes)->toBe('Mild peanut allergy. Carries antihistamine.');
        expect($child->dietary_needs)->toBe('No peanut products');

        $activity = Activity::where('event', 'parent.registered')->first();
        expect($activity)->not->toBeNull();
        expect($activity->causer_id)->toBe($user->id);
    }

    public function test_mandatory_consents_are_validated(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Carter',
            'email' => 'john.carter@example.com',
            'phone' => '+94779998877',
            'child_first_name' => 'Lily',
            'child_last_name' => 'Carter',
            'child_dob' => '2024-01-10',
            'nursery_group' => 'toddler',
            // Missing consent_terms and consent_child_safety
            'password' => 'SecurePass123!#',
            'password_confirmation' => 'SecurePass123!#',
        ]);

        $response->assertSessionHasErrors(['consent_terms', 'consent_child_safety']);
    }

    public function test_child_dob_must_be_before_today(): void
    {
        $response = $this->post('/register', [
            'name' => 'Emily Watson',
            'email' => 'emily.watson@example.com',
            'phone' => '+94778887766',
            'child_first_name' => 'Noah',
            'child_last_name' => 'Watson',
            'child_dob' => now()->addDay()->format('Y-m-d'), // Future date
            'nursery_group' => 'nursery',
            'consent_terms' => '1',
            'consent_child_safety' => '1',
            'password' => 'SecurePass123!#',
            'password_confirmation' => 'SecurePass123!#',
        ]);

        $response->assertSessionHasErrors(['child_dob']);
    }

    public function test_pending_parent_can_view_awaiting_approval_screen_with_child_summary(): void
    {
        $parent = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'pending',
            'phone' => '+94771122334',
        ]);
        $parent->assignRole(RoleName::Parent->value);

        $profile = ParentProfile::create([
            'institution_id' => $this->institution->id,
            'user_id' => $parent->id,
            'relationship' => 'mother',
            'consent_terms' => true,
            'consent_child_safety' => true,
            'consent_photo' => true,
            'consented_at' => now(),
        ]);

        ChildRegistration::create([
            'institution_id' => $this->institution->id,
            'parent_profile_id' => $profile->id,
            'first_name' => 'Sophia',
            'last_name' => 'Parent',
            'dob' => '2022-09-01',
            'nursery_group' => 'nursery',
        ]);

        $response = $this->actingAs($parent)->get('/awaiting-approval');

        $response->assertStatus(200);
        $response->assertSee('Sophia Parent');
        $response->assertSee('Status: Pending Review');
        $response->assertSee(__('Photo Release Granted'));
    }

    public function test_rejected_parent_sees_rejection_banner_and_reason(): void
    {
        $parent = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'rejected',
        ]);
        $parent->assignRole(RoleName::Parent->value);

        ParentProfile::create([
            'institution_id' => $this->institution->id,
            'user_id' => $parent->id,
            'rejection_reason' => 'Child immunization records are incomplete. Please resubmit.',
            'reviewed_at' => now(),
        ]);

        $response = $this->actingAs($parent)->get('/awaiting-approval');

        $response->assertStatus(200);
        $response->assertSee(__('Registration Not Approved'));
        $response->assertSee('Child immunization records are incomplete. Please resubmit.');
    }

    public function test_parent_can_request_and_verify_sms_otp(): void
    {
        $parent = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'pending',
            'phone' => '+94775554433',
        ]);
        $parent->assignRole(RoleName::Parent->value);

        $smsMock = $this->mock(SmsChannelInterface::class);
        $smsMock->shouldReceive('send')
            ->once()
            ->with('+94775554433', \Mockery::on(fn ($msg) => str_contains((string) $msg, 'verification code')))
            ->andReturn(true);

        // 1. Send OTP
        $response = $this->actingAs($parent)->post('/verification/phone/send');
        $response->assertSessionHas('success');

        $profile = ParentProfile::where('user_id', $parent->id)->first();
        expect($profile)->not->toBeNull();
        expect($profile->phone_verification_code)->not->toBeNull();
        expect(strlen((string) $profile->phone_verification_code))->toBe(6);

        // 2. Fail with wrong code
        $wrongResponse = $this->actingAs($parent)->post('/verification/phone/verify', [
            'code' => '000000',
        ]);
        $wrongResponse->assertSessionHas('error');
        $parent->refresh();
        expect($parent->phone_verified_at)->toBeNull();

        // 3. Succeed with correct code
        $correctResponse = $this->actingAs($parent)->post('/verification/phone/verify', [
            'code' => $profile->phone_verification_code,
        ]);
        $correctResponse->assertSessionHas('success');

        $parent->refresh();
        expect($parent->phone_verified_at)->not->toBeNull();
    }
}
