<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TwoFactorSetup extends Component
{
    public string $code = '';

    public bool $showingQr = false;

    public bool $showingRecoveryCodes = false;

    public function mount(): void
    {
        /** @var User $user */
        $user = auth()->user();

        if (! empty($user->two_factor_secret) && empty($user->two_factor_confirmed_at)) {
            $this->showingQr = true;
        }

        if (! empty($user->two_factor_confirmed_at)) {
            $this->showingRecoveryCodes = true;
        }
    }

    public function enable(EnableTwoFactorAuthentication $enable): void
    {
        /** @var User $user */
        $user = auth()->user();

        $enable($user);
        $this->showingQr = true;
        $this->showingRecoveryCodes = false;
        $this->code = '';
    }

    public function confirm(ConfirmTwoFactorAuthentication $confirm): void
    {
        $this->validate([
            'code' => ['required', 'string', 'min:6'],
        ]);

        /** @var User $user */
        $user = auth()->user();

        try {
            $confirm($user, $this->code);
            $this->showingQr = false;
            $this->showingRecoveryCodes = true;
            $this->code = '';

            session()->flash('success', __('Two-factor authentication has been verified and enabled on your account.'));
        } catch (ValidationException $e) {
            $this->addError('code', __('The provided authentication code was invalid.'));
        }
    }

    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generate): void
    {
        /** @var User $user */
        $user = auth()->user();

        $generate($user);
        $this->showingRecoveryCodes = true;

        session()->flash('success', __('New recovery codes have been generated. Store them safely.'));
    }

    public function disable(DisableTwoFactorAuthentication $disable): void
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user->hasRole('admin') || $user->hasRole('super_admin')) {
            session()->flash('error', __('Two-factor authentication is mandatory for administrators and cannot be disabled.'));

            return;
        }

        $disable($user);
        $this->showingQr = false;
        $this->showingRecoveryCodes = false;
        $this->code = '';

        session()->flash('success', __('Two-factor authentication disabled.'));
    }

    /**
     * @return list<string>
     */
    public function getRecoveryCodesProperty(): array
    {
        /** @var User $user */
        $user = auth()->user();

        if (empty($user->two_factor_recovery_codes)) {
            return [];
        }

        try {
            return array_values((array) $user->recoveryCodes());
        } catch (\Throwable) {
            return [];
        }
    }

    public function getQrCodeSvgProperty(): string
    {
        /** @var User $user */
        $user = auth()->user();

        if (empty($user->two_factor_secret)) {
            return '';
        }

        try {
            return $user->twoFactorQrCodeSvg();
        } catch (\Throwable) {
            return '';
        }
    }

    public function getSetupKeyProperty(): string
    {
        /** @var User $user */
        $user = auth()->user();

        if (empty($user->two_factor_secret)) {
            return '';
        }

        try {
            return (string) decrypt($user->two_factor_secret);
        } catch (\Throwable) {
            return '';
        }
    }

    public function render(): View
    {
        return view('livewire.auth.two-factor-setup');
    }
}
