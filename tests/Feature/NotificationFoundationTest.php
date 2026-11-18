<?php

namespace Tests\Feature;

use App\Contracts\SmsChannelInterface;
use App\Contracts\WhatsAppChannelInterface;
use App\Models\Institution;
use App\Models\User;
use App\Notifications\GenericPortalNotification;
use App\Services\InstitutionContext;
use App\Services\NotificationService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SuperAdminSeeder::class);

        $this->institution = Institution::first();
        app(InstitutionContext::class)->set($this->institution);

        $this->user = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
            'phone' => '+1234567890',
        ]);
    }

    public function test_notification_service_dispatches_database_and_mail_to_user(): void
    {
        Notification::fake();

        $service = app(NotificationService::class);

        $dispatched = $service->send(
            user: $this->user,
            eventType: 'announcement',
            title: 'Welcome to the New Term',
            message: 'Classes commence on Monday at 08:30 AM.',
            url: 'http://localhost/classes'
        );

        expect($dispatched)->toContain('database');
        expect($dispatched)->toContain('mail');

        Notification::assertSentTo(
            $this->user,
            GenericPortalNotification::class,
            function (GenericPortalNotification $notification) {
                return $notification->title === 'Welcome to the New Term'
                    && $notification->eventType === 'announcement'
                    && in_array('database', $notification->channels, true)
                    && in_array('mail', $notification->channels, true);
            }
        );
    }

    public function test_user_notification_preference_blocks_opted_out_channel(): void
    {
        Notification::fake();

        // User opts out of mail notifications for fee_due
        $this->user->setNotificationPreference('fee_due', 'mail', false);

        $service = app(NotificationService::class);

        $dispatched = $service->send(
            user: $this->user,
            eventType: 'fee_due',
            title: 'Fee Payment Reminder',
            message: 'Your monthly tuition fee is due in 3 days.'
        );

        // Mail should be blocked by user preference; database remains
        expect($dispatched)->toContain('database');
        expect($dispatched)->not->toContain('mail');

        Notification::assertSentTo(
            $this->user,
            GenericPortalNotification::class,
            function (GenericPortalNotification $notification) {
                return in_array('database', $notification->channels, true)
                    && ! in_array('mail', $notification->channels, true);
            }
        );
    }

    public function test_sms_channel_is_called_when_enabled(): void
    {
        // Mock the SMS interface
        $smsMock = $this->mock(SmsChannelInterface::class);
        $smsMock->shouldReceive('send')
            ->once()
            ->with($this->user->phone, 'Emergency: Campus closed tomorrow due to heavy rain.')
            ->andReturn(true);

        $service = app(NotificationService::class);

        // Override channels to include sms
        $dispatched = $service->send(
            user: $this->user,
            eventType: 'announcement',
            title: 'Emergency',
            message: 'Campus closed tomorrow due to heavy rain.',
            overrideChannels: ['sms', 'database']
        );

        expect($dispatched)->toContain('sms');
    }

    public function test_whatsapp_channel_is_called_when_enabled(): void
    {
        $waMock = $this->mock(WhatsAppChannelInterface::class);
        $waMock->shouldReceive('send')
            ->once()
            ->with($this->user->phone, "*Absence Alert*\nStudent John was marked absent today.", [])
            ->andReturn(true);

        $service = app(NotificationService::class);

        $dispatched = $service->send(
            user: $this->user,
            eventType: 'absence_alert',
            title: 'Absence Alert',
            message: 'Student John was marked absent today.',
            overrideChannels: ['whatsapp']
        );

        expect($dispatched)->toContain('whatsapp');
    }

    public function test_available_event_types_returns_standard_events(): void
    {
        $service = app(NotificationService::class);
        $events = $service->getAvailableEventTypes();

        expect($events)->toHaveKeys([
            'account_approved',
            'activity_material',
            'deadline_reminder',
            'submission_reviewed',
            'payment_approved',
            'fee_due',
            'absence_alert',
            'chat_message',
            'announcement',
        ]);
    }
}
