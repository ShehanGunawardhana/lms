<?php

namespace Tests\Feature;

use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Enums\RoleName;
use App\Livewire\Profile\UserProfileManager;
use App\Models\Institution;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class UserProfileManagerTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected User $user;

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

        $this->user = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Arthur Dent',
            'email' => 'arthur.dent@example.com',
            'phone' => '+94770001234',
            'password' => Hash::make('CurrentSecret123!'),
            'status' => 'active',
            'locale' => 'en',
        ]);
        $this->user->assignRole(RoleName::Teacher->value);
    }

    public function test_guest_cannot_access_profile_page(): void
    {
        $this->get('/user/profile')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_render_profile_page(): void
    {
        $this->actingAs($this->user)
            ->get('/user/profile')
            ->assertOk()
            ->assertSee(__('Profile & Preferences'))
            ->assertSee('Arthur Dent')
            ->assertSee('arthur.dent@example.com')
            ->assertSee(__('Language & Localization'))
            ->assertSee(__('Notification Preferences'));
    }

    public function test_user_can_update_profile_information(): void
    {
        $this->actingAs($this->user);

        Livewire::test(UserProfileManager::class)
            ->set('name', 'Arthur Philip Dent')
            ->set('email', 'arthur.new@example.com')
            ->set('phone', '+94779990000')
            ->call('updateProfileInformation', app(UpdateUserProfileInformation::class))
            ->assertHasNoErrors()
            ->assertSee(__('Profile details updated successfully.'));

        $this->user->refresh();
        expect($this->user->name)->toBe('Arthur Philip Dent');
        expect($this->user->email)->toBe('arthur.new@example.com');
        expect($this->user->phone)->toBe('+94779990000');

        $activity = Activity::where('event', 'user.profile_updated')->first();
        expect($activity)->not->toBeNull();
        expect($activity->causer_id)->toBe($this->user->id);
    }

    public function test_user_can_update_password(): void
    {
        $this->actingAs($this->user);

        // 1. Wrong current password fails
        Livewire::test(UserProfileManager::class)
            ->set('current_password', 'WrongPassword!')
            ->set('password', 'NewSecret456!#')
            ->set('password_confirmation', 'NewSecret456!#')
            ->call('updatePassword', app(UpdateUserPassword::class))
            ->assertHasErrors(['current_password']);

        // 2. Correct password updates
        Livewire::test(UserProfileManager::class)
            ->set('current_password', 'CurrentSecret123!')
            ->set('password', 'NewSecret456!#')
            ->set('password_confirmation', 'NewSecret456!#')
            ->call('updatePassword', app(UpdateUserPassword::class))
            ->assertHasNoErrors()
            ->assertSee(__('Password updated successfully.'));

        $this->user->refresh();
        expect(Hash::check('NewSecret456!#', $this->user->password))->toBeTrue();

        $activity = Activity::where('event', 'user.password_changed')->first();
        expect($activity)->not->toBeNull();
        expect($activity->causer_id)->toBe($this->user->id);
    }

    public function test_user_can_change_language_preference(): void
    {
        $this->actingAs($this->user);

        Livewire::test(UserProfileManager::class)
            ->set('locale', 'si')
            ->call('updateLocale')
            ->assertHasNoErrors()
            ->assertSee(__('Language preference updated successfully.'));

        $this->user->refresh();
        expect($this->user->locale)->toBe('si');
        expect(session('locale'))->toBe('si');
        expect(app()->getLocale())->toBe('si');

        $activity = Activity::where('event', 'user.locale_updated')->first();
        expect($activity)->not->toBeNull();
        expect($activity->causer_id)->toBe($this->user->id);
    }

    public function test_user_can_toggle_notification_preferences(): void
    {
        $this->actingAs($this->user);

        // Standard default is true (opt-in)
        expect($this->user->wantsNotification('announcement', 'sms'))->toBeTrue();

        // Toggle to false
        Livewire::test(UserProfileManager::class)
            ->call('toggleNotification', 'announcement', 'sms')
            ->assertSee(__('Notification preferences saved.'));

        $this->user->refresh();
        expect($this->user->wantsNotification('announcement', 'sms'))->toBeFalse();

        // Toggle back to true
        Livewire::test(UserProfileManager::class)
            ->call('toggleNotification', 'announcement', 'sms')
            ->assertSee(__('Notification preferences saved.'));

        $this->user->refresh();
        expect($this->user->wantsNotification('announcement', 'sms'))->toBeTrue();

        $activity = Activity::where('event', 'user.notification_preference_updated')->first();
        expect($activity)->not->toBeNull();
        expect($activity->causer_id)->toBe($this->user->id);
    }
}
