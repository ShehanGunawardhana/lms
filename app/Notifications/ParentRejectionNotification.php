<?php

namespace App\Notifications;

use App\Models\ParentProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ParentRejectionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $reason,
        public ?ParentProfile $profile = null
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
        $child = $this->profile?->children->first();
        $childName = $child !== null ? $child->full_name : __('your child');
        $name = property_exists($notifiable, 'name') ? (string) $notifiable->name : 'Parent';

        return (new MailMessage)
            ->subject(__('Update regarding your registration at :institution', ['institution' => $institutionName]))
            ->greeting(__('Hello :name,', ['name' => $name]))
            ->line(__('Thank you for submitting your application for :child to :institution.', [
                'child' => $childName,
                'institution' => $institutionName,
            ]))
            ->line(__('After careful review by our admissions department, we are unable to approve your application at this time.'))
            ->line(__('Reason: :reason', ['reason' => $this->reason]))
            ->line(__('If you have additional documentation or questions, please contact our admissions desk.'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $inst = current_institution();
        $institutionName = $inst !== null ? $inst->name : (string) config('app.name', 'EduPortal');

        return [
            'type' => 'parent.rejected',
            'title' => __('Registration Not Approved'),
            'message' => __('Your application to :institution was not approved. Reason: :reason', [
                'institution' => $institutionName,
                'reason' => $this->reason,
            ]),
            'action_url' => route('awaiting-approval'),
        ];
    }
}
