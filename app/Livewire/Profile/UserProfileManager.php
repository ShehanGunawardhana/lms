<?php

namespace App\Livewire\Profile;

use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class UserProfileManager extends Component
{
    // Profile Fields
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    // Password Fields
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    // Language / Locale Field
    public string $locale = 'en';

    public function mount(): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        if ($user) {
            $this->name = $user->name;
            $this->email = $user->email;
            $this->phone = $user->phone ?? '';
            $this->locale = $user->locale ?: 'en';
        }
    }

    public function updateProfileInformation(UpdateUserProfileInformation $updater): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $this->resetErrorBag('updateProfileInformation');

        $updater->update($user, [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);

        session()->flash('success', __('Profile details updated successfully.'));
    }

    public function updatePassword(UpdateUserPassword $updater): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $this->resetErrorBag('updatePassword');

        $updater->update($user, [
            'current_password' => $this->current_password,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);

        session()->flash('success', __('Password updated successfully.'));
    }

    public function updateLocale(): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $this->validate([
            'locale' => ['required', 'string', 'in:en,si,ta'],
        ]);

        $user->update([
            'locale' => $this->locale,
        ]);

        session(['locale' => $this->locale]);
        app()->setLocale($this->locale);

        audit('user.locale_updated', "User {$user->name} changed interface language to {$this->locale}", $user);

        session()->flash('success', __('Language preference updated successfully.'));
    }

    public function toggleNotification(string $eventType, string $channel): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            return;
        }

        if (! in_array($channel, ['mail', 'database', 'sms', 'whatsapp'], true)) {
            return;
        }

        $current = $user->wantsNotification($eventType, $channel);
        $newState = ! $current;

        $user->setNotificationPreference($eventType, $channel, $newState);

        audit(
            'user.notification_preference_updated',
            "User {$user->name} set notification preference for {$eventType} ({$channel}) to ".($newState ? 'enabled' : 'disabled'),
            $user,
            [
                'event_type' => $eventType,
                'channel' => $channel,
                'is_enabled' => $newState,
            ]
        );

        session()->flash('success', __('Notification preferences saved.'));
    }

    public function render(NotificationService $notificationService): View
    {
        /** @var User|null $user */
        $user = auth()->user();

        $eventTypes = $notificationService->getAvailableEventTypes();

        $preferences = [];
        if ($user) {
            foreach (array_keys($eventTypes) as $event) {
                foreach (['mail', 'database', 'sms', 'whatsapp'] as $channel) {
                    $preferences[$event][$channel] = $user->wantsNotification($event, $channel);
                }
            }
        }

        return view('livewire.profile.user-profile-manager', [
            'user' => $user,
            'eventTypes' => $eventTypes,
            'preferences' => $preferences,
        ]);
    }
}
