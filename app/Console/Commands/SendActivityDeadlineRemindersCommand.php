<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\SendActivityDeadlineRemindersJob;
use Illuminate\Console\Command;

class SendActivityDeadlineRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nursery:send-deadline-reminders {--hours=24 : Window in hours before deadline to send reminders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send deadline reminder notifications to parents whose children have not submitted upcoming nursery activities (§6.1, §6.2)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        if ($hours <= 0) {
            $hours = 24;
        }

        $this->info("Scanning upcoming activity deadlines within the next {$hours} hours...");

        $job = new SendActivityDeadlineRemindersJob($hours);
        $result = $job->handle();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Activities Scanned', $result['activities_scanned']],
                ['Reminders Dispatched', $result['reminders_sent']],
            ]
        );

        $this->info("Completed: {$result['reminders_sent']} reminder(s) dispatched.");

        return self::SUCCESS;
    }
}
