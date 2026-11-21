<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $activity_id
 * @property string $youtube_url
 * @property string $video_id
 * @property string|null $title
 * @property string|null $thumbnail_url
 * @property int $sort_order
 * @property bool $is_healthy
 * @property Carbon|null $last_checked_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Activity|null $activity
 */
class ActivityVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'youtube_url',
        'video_id',
        'title',
        'thumbnail_url',
        'sort_order',
        'is_healthy',
        'last_checked_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_healthy' => 'boolean',
            'last_checked_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Activity, $this>
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    public function embedUrl(): string
    {
        return "https://www.youtube-nocookie.com/embed/{$this->video_id}?rel=0&modestbranding=1";
    }

    public function thumbnailUrl(): string
    {
        return $this->thumbnail_url ?: "https://img.youtube.com/vi/{$this->video_id}/hqdefault.jpg";
    }
}
