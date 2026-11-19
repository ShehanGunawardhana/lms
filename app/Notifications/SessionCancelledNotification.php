<?php

namespace App\Notifications;

use App\Models\ClassSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SessionCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ClassSession $session,
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
        $className = $this->session->schoolClass->title ?? 'Class Session';
        $formattedDate = $this->session->date->format('l, F j, Y');
        $formattedTime = date('h:i A', strtotime($this->session->starts_at));
        $name = property_exists($notifiable, 'name') ? (string) $notifiable->name : 'Student/Parent';

        return (new MailMessage)
            ->subject(__(':class Session Cancelled - :institution', ['class' => $className, 'institution' => $institutionName]))
            ->greeting(__('Hello :name,', ['name' => $name]))
            ->line(__('Please be informed that the scheduled session for :class has been cancelled.', ['class' => $className]))
            ->line(__('Date: :date at :time', ['date' => $formattedDate, 'time' => $formattedTime]))
            ->line(__('Reason: :reason', ['reason' => $this->reason]))
            ->action(__('View Portal Dashboard'), url('/dashboard'))
            ->line(__('We apologize for any inconvenience caused.'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $className = $this->session->schoolClass->title ?? 'Class Session';
        $formattedDate = $this->session->date->format('M d, Y');

        return [
            'type' => 'session.cancelled',
            'title' => __('Class Session Cancelled'),
            'message' => __(':class session on :date was cancelled. Reason: :reason', [
                'class' => $className,
                'date' => $formattedDate,
                'reason' => $this->reason,
            ]),
            'session_id' => $this->session->id,
            'class_id' => $this->session->class_id,
        ];
    }
}
