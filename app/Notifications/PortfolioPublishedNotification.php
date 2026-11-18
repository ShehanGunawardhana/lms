<?php

namespace App\Notifications;

use App\Models\TeacherProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PortfolioPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public TeacherProfile $profile
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
            ->subject(__('Your Lecturer Portfolio is Now Live - :institution', ['institution' => $institutionName]))
            ->greeting(__('Hello :name,', ['name' => $name]))
            ->line(__('Congratulations! Your faculty portfolio has been reviewed and approved by institution administrators.'))
            ->line(__('It is now published on the public lecturer directory and class catalog where prospective students and families can view your credentials, subjects, and sample materials.'))
            ->action(__('View Portfolio Editor'), route('teacher.portfolio'))
            ->line(__('Thank you for your academic leadership.'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $inst = current_institution();
        $institutionName = $inst !== null ? $inst->name : (string) config('app.name', 'EduPortal');

        return [
            'type' => 'portfolio.published',
            'title' => __('Lecturer Portfolio Published'),
            'message' => __('Your faculty profile has been approved and published to the public directory of :institution.', [
                'institution' => $institutionName,
            ]),
            'profile_id' => $this->profile->id,
            'action_url' => route('teacher.portfolio'),
        ];
    }
}
