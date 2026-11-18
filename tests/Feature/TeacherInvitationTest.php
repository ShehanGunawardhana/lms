<?php

namespace Tests\Feature;

use App\Contracts\SmsChannelInterface;
use App\Enums\RoleName;
use App\Livewire\Admin\TeacherInvite;
use App\Livewire\Auth\AcceptInvitation;
use App\Models\Institution;
use App\Models\User;
use App\Models\UserInvitation;
use App\Notifications\TeacherInvitationNotification;
use App\Services\InstitutionContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class TeacherInvitationTest extends TestCase
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
            'status' => 'active',
            'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
            'two_factor_confirmed_at' => now(),
        ]);
        $this->admin->assignRole(RoleName::Admin->value);
    }

    public function test_guest_or_non_admin_cannot_access_teacher_invite_screen(): void
    {
        $this->get(route('admin.teachers.invite'))->assertRedirect(route('login'));

        $teacher = User::factory()->create(['status' => 'active', 'institution_id' => $this->institution->id]);
        $teacher->assignRole(RoleName::Teacher->value);

        $this->actingAs($teacher)->get(route('admin.teachers.invite'))->assertForbidden();
    }

    public function test_admin_with_two_factor_can_render_teacher_invite_screen(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.teachers.invite'))
            ->assertOk()
            ->assertSee(__('Teacher & Lecturer Invitations'));
    }

    public function test_admin_can_invite_teacher_via_email(): void
    {
        Notification::fake();
        $this->actingAs($this->admin);

        Livewire::test(TeacherInvite::class)
            ->set('name', 'Dr. Alan Turing')
            ->set('email', 'alan.turing@campus.edu')
            ->set('channel', 'email')
            ->set('subjects', 'Computer Science, Mathematics')
            ->call('invite')
            ->assertHasNoErrors()
            ->assertSee(__('Teacher invitation sent successfully! Link generated below.'));

        $teacher = User::where('email', 'alan.turing@campus.edu')->first();
        expect($teacher)->not->toBeNull();
        expect($teacher->name)->toBe('Dr. Alan Turing');
        expect($teacher->status)->toBe('invited');
        expect($teacher->hasRole(RoleName::Teacher->value))->toBeTrue();

        $this->assertDatabaseHas('teacher_profiles', [
            'user_id' => $teacher->id,
            'portfolio_status' => 'draft',
        ]);

        $invitation = UserInvitation::where('user_id', $teacher->id)->first();
        expect($invitation)->not->toBeNull();
        expect($invitation->channel)->toBe('email');
        expect($invitation->isExpired())->toBeFalse();

        Notification::assertSentTo($teacher, TeacherInvitationNotification::class);

        $activity = Activity::where('event', 'user.invited')->first();
        expect($activity)->not->toBeNull();
        expect($activity->causer_id)->toBe($this->admin->id);
    }

    public function test_admin_can_invite_teacher_via_sms(): void
    {
        $this->actingAs($this->admin);

        $smsMock = $this->mock(SmsChannelInterface::class);
        $smsMock->shouldReceive('send')
            ->once()
            ->with('+94770001122', \Mockery::any())
            ->andReturn(true);

        Livewire::test(TeacherInvite::class)
            ->set('name', 'Prof. Ada Lovelace')
            ->set('email', 'ada.lovelace@campus.edu')
            ->set('phone', '+94770001122')
            ->set('channel', 'sms')
            ->call('invite')
            ->assertHasNoErrors();

        $teacher = User::where('email', 'ada.lovelace@campus.edu')->first();
        expect($teacher)->not->toBeNull();
        expect($teacher->phone)->toBe('+94770001122');
    }

    public function test_admin_can_resend_and_revoke_invitation(): void
    {
        Notification::fake();
        $this->actingAs($this->admin);

        $teacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'invited',
        ]);
        $teacher->assignRole(RoleName::Teacher->value);

        $invitation = UserInvitation::create([
            'institution_id' => $this->institution->id,
            'user_id' => $teacher->id,
            'token' => Str::random(48),
            'channel' => 'email',
            'sent_to' => $teacher->email,
            'expires_at' => now()->subDay(), // already expired
            'created_by' => $this->admin->id,
        ]);

        // 1. Resend
        Livewire::test(TeacherInvite::class)
            ->call('resend', $invitation->id)
            ->assertSee(__('Invitation resent successfully with a renewed 7-day expiration.'));

        $invitation->refresh();
        expect($invitation->isExpired())->toBeFalse();

        // 2. Revoke
        Livewire::test(TeacherInvite::class)
            ->call('revoke', $invitation->id)
            ->assertSee(__('Invitation link revoked.'));

        $this->assertDatabaseMissing('user_invitations', ['id' => $invitation->id]);
    }

    public function test_teacher_can_render_invitation_screen(): void
    {
        $teacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Grace Hopper',
            'email' => 'grace@navy.mil',
            'status' => 'invited',
        ]);
        $teacher->assignRole(RoleName::Teacher->value);

        $invitation = UserInvitation::create([
            'institution_id' => $this->institution->id,
            'user_id' => $teacher->id,
            'token' => 'valid-test-token-12345',
            'channel' => 'email',
            'sent_to' => $teacher->email,
            'expires_at' => now()->addDays(5),
            'created_by' => $this->admin->id,
        ]);

        $response = $this->get(route('invitation.accept', ['token' => 'valid-test-token-12345']));

        $response->assertOk()
            ->assertSee('Grace Hopper')
            ->assertSee(__('Choose Your Password'));
    }

    public function test_expired_or_already_accepted_invitation_is_flagged(): void
    {
        $teacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'invited',
        ]);

        $expired = UserInvitation::create([
            'institution_id' => $this->institution->id,
            'user_id' => $teacher->id,
            'token' => 'expired-token',
            'channel' => 'email',
            'sent_to' => $teacher->email,
            'expires_at' => now()->subDay(),
        ]);

        $this->get(route('invitation.accept', ['token' => 'expired-token']))
            ->assertOk()
            ->assertSee(__('Invitation Expired'));

        $accepted = UserInvitation::create([
            'institution_id' => $this->institution->id,
            'user_id' => $teacher->id,
            'token' => 'accepted-token',
            'channel' => 'email',
            'sent_to' => $teacher->email,
            'expires_at' => now()->addDays(5),
            'accepted_at' => now(),
        ]);

        $this->get(route('invitation.accept', ['token' => 'accepted-token']))
            ->assertOk()
            ->assertSee(__('Invitation Already Accepted'));
    }

    public function test_teacher_can_accept_invitation_set_password_and_is_redirected_to_teacher_dashboard(): void
    {
        $teacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Richard Feynman',
            'email' => 'feynman@caltech.edu',
            'status' => 'invited',
        ]);
        $teacher->assignRole(RoleName::Teacher->value);

        $invitation = UserInvitation::create([
            'institution_id' => $this->institution->id,
            'user_id' => $teacher->id,
            'token' => 'feynman-activation-token',
            'channel' => 'email',
            'sent_to' => $teacher->email,
            'expires_at' => now()->addDays(5),
        ]);

        Livewire::test(AcceptInvitation::class, ['token' => 'feynman-activation-token'])
            ->set('password', 'Quantum123!')
            ->set('password_confirmation', 'Quantum123!')
            ->call('accept')
            ->assertHasNoErrors()
            ->assertRedirect(route('teacher.dashboard'));

        $teacher->refresh();
        expect($teacher->status)->toBe('active');
        expect(Hash::check('Quantum123!', $teacher->password))->toBeTrue();
        expect($teacher->email_verified_at)->not->toBeNull();

        $invitation->refresh();
        expect($invitation->isAccepted())->toBeTrue();

        $this->assertAuthenticatedAs($teacher);
    }
}
