<?php

namespace App\Livewire\Admin;

use App\Contracts\SmsChannelInterface;
use App\Enums\RoleName;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Models\UserInvitation;
use App\Notifications\TeacherInvitationNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TeacherInvite extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $channel = 'email';

    public string $subjects = '';

    public ?string $lastInviteUrl = null;

    /**
     * @return array<string, list<string>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => [
                $this->channel !== 'email' ? 'required' : 'nullable',
                'string',
                'max:30',
            ],
            'channel' => ['required', 'in:email,sms,both'],
            'subjects' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function invite(): void
    {
        $this->validate();

        $institutionId = current_institution_id() ?? 1;

        // 1. Create the teacher user record in 'invited' state
        $user = User::create([
            'institution_id' => $institutionId,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ? $this->phone : null,
            'status' => 'invited',
            'password' => Hash::make(Str::random(32)),
        ]);

        $user->assignRole(RoleName::Teacher->value);

        // 2. Create the TeacherProfile stub
        $subjectsArray = ! empty($this->subjects)
            ? array_map('trim', explode(',', $this->subjects))
            : [];

        TeacherProfile::create([
            'institution_id' => $institutionId,
            'user_id' => $user->id,
            'subjects' => $subjectsArray,
            'portfolio_status' => 'draft',
        ]);

        // 3. Create the invitation record
        $token = Str::random(48);
        $invitation = UserInvitation::create([
            'institution_id' => $institutionId,
            'user_id' => $user->id,
            'token' => $token,
            'channel' => $this->channel,
            'sent_to' => in_array($this->channel, ['sms']) ? (string) $this->phone : $this->email,
            'expires_at' => now()->addDays(7),
            'created_by' => auth()->id(),
        ]);

        // 4. Dispatch Email Notification if requested
        if (in_array($this->channel, ['email', 'both'], true)) {
            $user->notify(new TeacherInvitationNotification($invitation));
        }

        // 5. Dispatch SMS if requested and phone is present
        if (in_array($this->channel, ['sms', 'both'], true) && ! empty($user->phone)) {
            $smsChannel = app(SmsChannelInterface::class);
            $inst = current_institution();
            $instName = $inst !== null ? $inst->name : 'EduPortal';
            $smsMessage = __(':inst: You are invited to join our teaching faculty. Set your password here: :url', [
                'inst' => $instName,
                'url' => $invitation->getAcceptUrl(),
            ]);
            $smsChannel->send($user->phone, $smsMessage);
        }

        // 6. Audit Log
        audit('user.invited', "Invited teacher {$user->name} ({$user->email}) via {$this->channel}", $user, [
            'role' => RoleName::Teacher->value,
            'channel' => $this->channel,
            'invitation_id' => $invitation->id,
        ]);

        $this->lastInviteUrl = $invitation->getAcceptUrl();

        session()->flash('success', __('Teacher invitation sent successfully! Link generated below.'));

        $this->reset(['name', 'email', 'phone', 'subjects']);
        $this->channel = 'email';
    }

    public function resend(int $invitationId): void
    {
        $invitation = UserInvitation::where('institution_id', current_institution_id() ?? 1)
            ->where('id', $invitationId)
            ->firstOrFail();

        if ($invitation->isAccepted()) {
            session()->flash('error', __('This invitation has already been accepted.'));

            return;
        }

        $invitation->update([
            'token' => Str::random(48),
            'expires_at' => now()->addDays(7),
        ]);

        $user = $invitation->user;

        if (in_array($invitation->channel, ['email', 'both'], true) && $user) {
            $user->notify(new TeacherInvitationNotification($invitation));
        }

        if (in_array($invitation->channel, ['sms', 'both'], true) && $user && ! empty($user->phone)) {
            $smsChannel = app(SmsChannelInterface::class);
            $inst = current_institution();
            $instName = $inst !== null ? $inst->name : 'EduPortal';
            $smsMessage = __(':inst: Teacher invitation resent. Set your password here: :url', [
                'inst' => $instName,
                'url' => $invitation->getAcceptUrl(),
            ]);
            $smsChannel->send($user->phone, $smsMessage);
        }

        audit('user.invite_resent', "Resent invitation for teacher {$user?->name}", $user);

        $this->lastInviteUrl = $invitation->getAcceptUrl();

        session()->flash('success', __('Invitation resent successfully with a renewed 7-day expiration.'));
    }

    public function revoke(int $invitationId): void
    {
        $invitation = UserInvitation::where('institution_id', current_institution_id() ?? 1)
            ->where('id', $invitationId)
            ->firstOrFail();

        $user = $invitation->user;

        audit('user.invite_revoked', "Revoked invitation for teacher {$user?->name}", $user);

        $invitation->delete();

        session()->flash('success', __('Invitation link revoked.'));
    }

    public function render(): View
    {
        $invitations = UserInvitation::with('user')
            ->where('institution_id', current_institution_id() ?? 1)
            ->latest()
            ->paginate(10);

        return view('livewire.admin.teacher-invite', [
            'invitations' => $invitations,
        ]);
    }
}
