<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ActivityVideo;
use App\Notifications\BrokenVideoLinkNotification;
use App\Services\YoutubeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckActivityVideosHealthJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ?int $activityId = null
    ) {}

    /**
     * @return array{checked: int, healthy: int, broken: int}
     */
    public function handle(YoutubeService $youtubeService): array
    {
        $query = ActivityVideo::query()
            ->with(['activity.creator', 'activity.schoolClass.teacher']);

        if ($this->activityId !== null) {
            $query->where('activity_id', $this->activityId);
        } else {
            $query->whereHas('activity', fn ($q) => $q->whereIn('status', ['published', 'draft']));
        }

        $videos = $query->get();

        $checked = 0;
        $healthy = 0;
        $broken = 0;

        foreach ($videos as $video) {
            $checked++;
            $isHealthy = $youtubeService->checkHealth($video->video_id);
            $wasHealthy = $video->is_healthy;

            $video->update([
                'is_healthy' => $isHealthy,
                'last_checked_at' => now(),
            ]);

            if ($isHealthy) {
                $healthy++;
            } else {
                $broken++;
                $activity = $video->activity;
                if ($activity !== null) {
                    $teacher = $activity->creator ?? $activity->schoolClass?->teacher;
                    if ($teacher !== null && ($wasHealthy || $video->last_checked_at === null)) {
                        $teacher->notify(new BrokenVideoLinkNotification($activity, $video));
                    }

                    audit(
                        'activity_video.broken',
                        "Video '{$video->title}' ({$video->youtube_url}) in activity '{$activity->title}' was detected as broken or unreachable.",
                        $activity,
                        [
                            'video_id' => $video->id,
                            'youtube_id' => $video->video_id,
                            'youtube_url' => $video->youtube_url,
                        ]
                    );
                }
            }
        }

        return [
            'checked' => $checked,
            'healthy' => $healthy,
            'broken' => $broken,
        ];
    }
}
