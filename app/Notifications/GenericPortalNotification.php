<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GenericPortalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  list<string>  $channels
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public string $title,
        public string $message,
        public string $eventType = 'general',
        public array $data = [],
        public ?string $url = null,
        public array $channels = ['database', 'mail']
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        $activeChannels = [];

        if (in_array('database', $this->channels, true)) {
            $activeChannels[] = 'database';
        }

        if (in_array('mail', $this->channels, true)) {
            $activeChannels[] = 'mail';
        }

        return $activeChannels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->greeting(__('Hello :name,', ['name' => $notifiable->name ?? 'User']))
            ->line($this->message);

        if ($this->url) {
            $mail->action(__('View Details'), $this->url);
        }

        $mail->line(__('Thank you for using our educational portal.'));

        return $mail;
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'event_type' => $this->eventType,
            'data' => $this->data,
            'url' => $this->url,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
