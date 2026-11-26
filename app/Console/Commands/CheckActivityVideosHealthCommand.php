<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\CheckActivityVideosHealthJob;
use App\Services\YoutubeService;
use Illuminate\Console\Command;

class CheckActivityVideosHealthCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nursery:check-video-health {--activity= : Specific activity ID to check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check YouTube video link health for nursery activities and notify teachers of broken videos (§6.1)';

    /**
     * Execute the console command.
     */
    public function handle(YoutubeService $youtubeService): int
    {
        $activityId = $this->option('activity') ? (int) $this->option('activity') : null;

        $this->info('Starting YouTube video link health check...');

        $job = new CheckActivityVideosHealthJob($activityId);
        $result = $job->handle($youtubeService);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Videos Checked', $result['checked']],
                ['Healthy / Reachable', $result['healthy']],
                ['Broken / Unreachable', $result['broken']],
            ]
        );

        if ($result['broken'] > 0) {
            $this->warn("{$result['broken']} broken or unreachable video link(s) found. Notifications dispatched to teachers.");
        } else {
            $this->info('All checked video links are healthy and reachable!');
        }

        return self::SUCCESS;
    }
}
