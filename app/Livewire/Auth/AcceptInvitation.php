<?php

namespace App\Livewire\Auth;

use App\Actions\Fortify\PasswordValidationRules;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class AcceptInvitation extends Component
{
    use PasswordValidationRules;

    public string $token = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?string $teacherName = null;

    public ?string $teacherEmail = null;

    public ?string $institutionName = null;

    public bool $isExpired = false;

    public bool $isAccepted = false;

    public function mount(string $token): void
    {
        $this->token = $token;

        $invitation = UserInvitation::with(['user', 'institution'])
            ->where('token', $token)
            ->first();

        if (! $invitation) {
            abort(404, __('The specified invitation link does not exist.'));
        }

        if ($invitation->isAccepted()) {
            $this->isAccepted = true;

            return;
        }

        if ($invitation->isExpired()) {
            $this->isExpired = true;

            return;
        }

        $this->teacherName = $invitation->user?->name;
        $this->teacherEmail = $invitation->user?->email;
        $inst = $invitation->institution;
        $this->institutionName = $inst !== null ? $inst->name : (string) config('app.name', 'EduPortal');
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'password' => $this->passwordRules(),
        ];
    }

    public function accept(): void
    {
        $invitation = UserInvitation::with('user')
            ->where('token', $this->token)
            ->firstOrFail();

        if (! $invitation->isValid()) {
            session()->flash('error', __('This invitation is no longer valid.'));

            return;
        }

        $this->validate();

        /** @var User $user */
        $user = $invitation->user;

        // 1. Activate teacher account and set password
        $user->forceFill([
            'password' => Hash::make($this->password),
            'status' => 'active',
            'email_verified_at' => now(),
            'phone_verified_at' => $user->phone ? now() : null,
        ])->save();

        // 2. Mark invitation accepted
        $invitation->update([
            'accepted_at' => now(),
        ]);

        // 3. Audit log
        audit('user.invite_accepted', "Teacher {$user->name} accepted invitation and activated account", $user);

        // 4. Authenticate user
        auth()->login($user);
        session()->regenerate();

        session()->flash('success', __('Welcome! Your faculty account has been activated successfully.'));

        $this->redirectRoute('teacher.dashboard');
    }

    public function render(): View
    {
        return view('livewire.auth.accept-invitation');
    }
}
