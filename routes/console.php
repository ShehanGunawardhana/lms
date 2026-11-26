<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Nursery Module Automated Scheduled Jobs (§6.1, T-4.10)
Schedule::command('nursery:check-video-health')->dailyAt('02:00');
Schedule::command('nursery:send-deadline-reminders --hours=24')->hourly();
