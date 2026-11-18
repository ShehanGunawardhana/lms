<?php

namespace App\Services;

use App\Contracts\SmsChannelInterface;
use App\Contracts\WhatsAppChannelInterface;
use App\Models\User;
use App\Notifications\GenericPortalNotification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Standard portal event types and their default allowed channels.
     *
     * @var array<string, list<string>>
     */
    protected array $eventChannelMap = [
        'account_approved' => ['mail', 'database', 'sms'],
        'account_rejected' => ['mail', 'database'],
        'activity_material' => ['database', 'mail'],
        'deadline_reminder' => ['database', 'mail', 'sms'],
        'submission_reviewed' => ['database', 'mail'],
        'payment_approved' => ['mail', 'database', 'sms'],
        'payment_rejected' => ['mail', 'database', 'sms'],
        'fee_due' => ['mail', 'database', 'sms'],
        'class_rescheduled' => ['database', 'mail', 'sms'],
        'absence_alert' => ['sms', 'database', 'mail', 'whatsapp'],
        'chat_message' => ['database'],
        'announcement' => ['database', 'mail'],
    ];

    public function __construct(
        protected SmsChannelInterface $smsChannel,
        protected WhatsAppChannelInterface $whatsAppChannel,
    ) {}

    /**
     * Dispatch a notification to a specific user across eligible channels.
     *
     * @param  array<string, mixed>  $data
     * @param  list<string>|null  $overrideChannels
     * @return list<string> The channels through which the notification was dispatched
     */
    public function send(
        User $user,
        string $eventType,
        string $title,
        string $message,
        array $data = [],
        ?string $url = null,
        ?array $overrideChannels = null
    ): array {
        // 1. Determine candidate channels based on explicit override or institution policy
        if ($overrideChannels !== null) {
            $candidateChannels = $overrideChannels;
        } else {
            $defaultChannels = $this->eventChannelMap[$eventType] ?? ['database', 'mail'];
            $institutionChannels = (array) portal_setting('notifications.channels', ['mail', 'database']);
            $candidateChannels = array_values(array_intersect($defaultChannels, $institutionChannels));
        }

        // 2. Filter by user preference
        $activeChannels = [];
        foreach ($candidateChannels as $channel) {
            if ($user->wantsNotification($eventType, $channel)) {
                $activeChannels[] = $channel;
            }
        }

        if (empty($activeChannels)) {
            return [];
        }

        $dispatched = [];

        // 4. Handle Laravel native channels (database & mail)
        $laravelChannels = array_values(array_intersect($activeChannels, ['database', 'mail']));
        if (! empty($laravelChannels)) {
            $user->notify(new GenericPortalNotification(
                title: $title,
                message: $message,
                eventType: $eventType,
                data: $data,
                url: $url,
                channels: $laravelChannels,
            ));
            foreach ($laravelChannels as $ch) {
                $dispatched[] = $ch;
            }
        }

        // 5. Handle SMS channel
        if (in_array('sms', $activeChannels, true) && ! empty($user->phone)) {
            $smsContent = "{$title}: {$message}";
            if ($this->smsChannel->send($user->phone, $smsContent)) {
                $dispatched[] = 'sms';
            }
        }

        // 6. Handle WhatsApp channel
        if (in_array('whatsapp', $activeChannels, true) && ! empty($user->phone)) {
            $waContent = "*{$title}*\n{$message}";
            if ($this->whatsAppChannel->send($user->phone, $waContent, $data)) {
                $dispatched[] = 'whatsapp';
            }
        }

        Log::info('[NotificationService] Dispatched notification', [
            'user_id' => $user->id,
            'event_type' => $eventType,
            'dispatched_channels' => $dispatched,
        ]);

        return $dispatched;
    }

    /**
     * Dispatch notification to a collection of users.
     *
     * @param  iterable<User>  $users
     * @param  array<string, mixed>  $data
     * @param  list<string>|null  $overrideChannels
     */
    public function sendToUsers(
        iterable $users,
        string $eventType,
        string $title,
        string $message,
        array $data = [],
        ?string $url = null,
        ?array $overrideChannels = null
    ): void {
        foreach ($users as $user) {
            $this->send($user, $eventType, $title, $message, $data, $url, $overrideChannels);
        }
    }

    /**
     * Get list of standard event types supported by the portal.
     *
     * @return array<string, string>
     */
    public function getAvailableEventTypes(): array
    {
        return [
            'account_approved' => __('Account approval status'),
            'account_rejected' => __('Account rejection notice'),
            'activity_material' => __('New learning activity or material'),
            'deadline_reminder' => __('Approaching or extended deadlines'),
            'submission_reviewed' => __('Submission feedback & grades'),
            'payment_approved' => __('Fee payment approval & receipt'),
            'payment_rejected' => __('Fee payment rejection notice'),
            'fee_due' => __('Monthly fee due reminders'),
            'class_rescheduled' => __('Class reschedule or cancellation'),
            'absence_alert' => __('Immediate student absence alerts'),
            'chat_message' => __('Direct chat messages'),
            'announcement' => __('Institution & class announcements'),
        ];
    }
}
