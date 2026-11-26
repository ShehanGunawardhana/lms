<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Activity;
use App\Models\ActivityVideo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BrokenVideoLinkNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Activity $activity,
        public ActivityVideo $video,
        public string $reason = 'Video is unreachable, private, or has been removed from YouTube.'
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
        $name = property_exists($notifiable, 'name') ? (string) $notifiable->name : 'Teacher';
        $videoTitle = $this->video->title ?: "YouTube Video ({$this->video->video_id})";

        return (new MailMessage)
            ->subject(__('[Action Required] Broken Video Link in Activity: :title - :institution', [
                'title' => $this->activity->title,
                'institution' => $institutionName,
            ]))
            ->greeting(__('Hello :name,', ['name' => $name]))
            ->line(__('Our automated daily health check detected that a video link in your nursery activity is broken or unavailable.', [
                'title' => $this->activity->title,
            ]))
            ->line(__('Activity: :title', ['title' => $this->activity->title]))
            ->line(__('Video: :video', ['video' => $videoTitle]))
            ->line(__('URL: :url', ['url' => $this->video->youtube_url]))
            ->line(__('Reason: :reason', ['reason' => $this->reason]))
            ->action(__('Manage Nursery Activities'), route('teacher.activities'))
            ->line(__('Please replace or update this video link so children and parents can continue their learning without interruption.'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $videoTitle = $this->video->title ?: "YouTube Video ({$this->video->video_id})";

        return [
            'type' => 'activity_video.broken',
            'title' => __('Broken Video Link Detected'),
            'message' => __('Video ":video" in activity ":title" is broken or unreachable. Please replace it.', [
                'video' => $videoTitle,
                'title' => $this->activity->title,
            ]),
            'activity_id' => $this->activity->id,
            'video_id' => $this->video->id,
            'video_url' => $this->video->youtube_url,
            'url' => route('teacher.activities'),
        ];
    }
}
