<?php

namespace App\Notifications;

use App\Models\ClassSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SessionRescheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ClassSession $oldSession,
        public ClassSession $newSession,
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
        $className = $this->newSession->schoolClass->title ?? 'Class Session';
        $oldDate = $this->oldSession->date->format('l, F j, Y');
        $oldTime = date('h:i A', strtotime($this->oldSession->starts_at));
        $newDate = $this->newSession->date->format('l, F j, Y');
        $newTime = date('h:i A', strtotime($this->newSession->starts_at));
        $venue = $this->newSession->room ?: ($this->newSession->online_url ? 'Online Link' : 'Standard Venue');
        $name = property_exists($notifiable, 'name') ? (string) $notifiable->name : 'Student/Parent';

        return (new MailMessage)
            ->subject(__(':class Session Rescheduled - :institution', ['class' => $className, 'institution' => $institutionName]))
            ->greeting(__('Hello :name,', ['name' => $name]))
            ->line(__('Please note that the lecture session for :class has been rescheduled.', ['class' => $className]))
            ->line(__('Original Date: :date at :time', ['date' => $oldDate, 'time' => $oldTime]))
            ->line(__('New Date: :date at :time (:venue)', ['date' => $newDate, 'time' => $newTime, 'venue' => $venue]))
            ->line(__('Reason: :reason', ['reason' => $this->reason]))
            ->action(__('View Class Schedule'), url('/dashboard'))
            ->line(__('Please update your calendar accordingly.'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $className = $this->newSession->schoolClass->title ?? 'Class Session';
        $newDate = $this->newSession->date->format('M d, Y');
        $newTime = date('h:i A', strtotime($this->newSession->starts_at));

        return [
            'type' => 'session.rescheduled',
            'title' => __('Class Session Rescheduled'),
            'message' => __(':class session has been moved to :date at :time. Reason: :reason', [
                'class' => $className,
                'date' => $newDate,
                'time' => $newTime,
                'reason' => $this->reason,
            ]),
            'old_session_id' => $this->oldSession->id,
            'new_session_id' => $this->newSession->id,
            'class_id' => $this->newSession->class_id,
        ];
    }
}
