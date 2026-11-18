<?php

namespace App\Notifications;

use App\Models\UserInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeacherInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public UserInvitation $invitation
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $institution = $this->invitation->institution;
        $institutionName = $institution !== null ? $institution->name : (string) config('app.name', 'EduPortal');
        $acceptUrl = $this->invitation->getAcceptUrl();
        $expiry = format_portal_datetime($this->invitation->expires_at);
        $name = property_exists($notifiable, 'name') ? (string) $notifiable->name : 'Teacher';

        return (new MailMessage)
            ->subject(__('Faculty Invitation: Join :institution', ['institution' => $institutionName]))
            ->greeting(__('Hello :name,', ['name' => $name]))
            ->line(__('You have been invited by the administration to join the academic faculty at :institution.', ['institution' => $institutionName]))
            ->line(__('To activate your teacher workspace and set your secure login password, please click the button below:'))
            ->action(__('Accept Invitation & Set Password'), $acceptUrl)
            ->line(__('This link will expire on :expires.', ['expires' => $expiry]))
            ->line(__('If you did not expect this invitation, you may safely ignore this message.'));
    }
}
