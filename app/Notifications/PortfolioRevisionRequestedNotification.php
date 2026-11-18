<?php

namespace App\Notifications;

use App\Models\TeacherProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PortfolioRevisionRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public TeacherProfile $profile,
        public string $reason
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $inst = current_institution();
        $institutionName = $inst !== null ? $inst->name : (string) config('app.name', 'EduPortal');
        $name = property_exists($notifiable, 'name') ? (string) $notifiable->name : 'Lecturer';

        return (new MailMessage)
            ->subject(__('Portfolio Revision Requested - :institution', ['institution' => $institutionName]))
            ->greeting(__('Hello :name,', ['name' => $name]))
            ->line(__('Your faculty portfolio submission was reviewed by administration, and revisions have been requested before publication.'))
            ->line(__('Feedback / Requested Changes:'))
            ->line('"'.$this->reason.'"')
            ->line(__('Please update your profile details in the portfolio editor and resubmit for approval.'))
            ->action(__('Open Portfolio Editor'), route('teacher.portfolio'))
            ->line(__('Thank you for your cooperation.'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $inst = current_institution();
        $institutionName = $inst !== null ? $inst->name : (string) config('app.name', 'EduPortal');

        return [
            'type' => 'portfolio.revision_requested',
            'title' => __('Portfolio Revision Requested'),
            'message' => __('Administration requested changes to your portfolio: :reason', [
                'reason' => $this->reason,
            ]),
            'feedback' => $this->reason,
            'profile_id' => $this->profile->id,
            'action_url' => route('teacher.portfolio'),
        ];
    }
}
