<?php

namespace App\Notifications;

use App\Models\ParentProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ParentApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
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
            ->subject(__('Welcome to :institution - Parent Account Approved', ['institution' => $institutionName]))
            ->greeting(__('Hello :name,', ['name' => $name]))
            ->line(__('We are pleased to inform you that your registration and nursery intake application for :child have been approved!', ['child' => $childName]))
            ->line(__('You can now log in to your Parent Portal to view learning feeds, milestone updates, attendance records, and upcoming schedules.'))
            ->action(__('Access Parent Dashboard'), route('login'))
            ->line(__('Thank you for being part of our educational family.'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $inst = current_institution();
        $institutionName = $inst !== null ? $inst->name : (string) config('app.name', 'EduPortal');
        $child = $this->profile?->children->first();
        $childName = $child !== null ? $child->full_name : __('your child');

        return [
            'type' => 'parent.approved',
            'title' => __('Parent Account Approved'),
            'message' => __('Your account and nursery intake for :child have been approved by :institution.', [
                'child' => $childName,
                'institution' => $institutionName,
            ]),
            'action_url' => route('parent.dashboard'),
        ];
    }
}
