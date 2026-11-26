<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Activity;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class ActivityDeadlineReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Activity $activity,
        public Student $student,
        public Carbon $deadline
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
        $name = property_exists($notifiable, 'name') ? (string) $notifiable->name : 'Parent';
        $formattedDeadline = format_portal_datetime($this->deadline, 'l, F j, Y h:i A');
        $hoursLeft = (int) max(1, round(now()->diffInHours($this->deadline, false)));

        return (new MailMessage)
            ->subject(__('Reminder: Activity ":title" is due soon for :child - :institution', [
                'title' => $this->activity->title,
                'child' => $this->student->first_name,
                'institution' => $institutionName,
            ]))
            ->greeting(__('Hello :name,', ['name' => $name]))
            ->line(__('This is a friendly reminder that the nursery activity ":title" for :child is due soon.', [
                'title' => $this->activity->title,
                'child' => $this->student->full_name,
            ]))
            ->line(__('Deadline: :deadline (:hours hours remaining)', [
                'deadline' => $formattedDeadline,
                'hours' => $hoursLeft,
            ]))
            ->action(__('View Activity & Submit Work'), route('parent.activities.show', [
                'activity' => $this->activity->id,
                'child' => $this->student->id,
            ]))
            ->line(__('Upload your child\'s completed artwork or photos before the deadline to earn cheerful teacher feedback stickers!'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'activity.deadline_reminder',
            'title' => __('Activity Deadline Reminder'),
            'message' => __('Activity ":title" for :child is due on :deadline.', [
                'title' => $this->activity->title,
                'child' => $this->student->first_name,
                'deadline' => format_portal_datetime($this->deadline, 'M d, Y h:i A'),
            ]),
            'activity_id' => $this->activity->id,
            'student_id' => $this->student->id,
            'deadline' => $this->deadline->toIso8601String(),
            'url' => route('parent.activities.show', [
                'activity' => $this->activity->id,
                'child' => $this->student->id,
            ]),
        ];
    }
}
