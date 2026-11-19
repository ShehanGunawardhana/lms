<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $activity_id
 * @property int|null $file_asset_id
 * @property string $title
 * @property string|null $url
 * @property string $type
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Activity|null $activity
 * @property-read FileAsset|null $fileAsset
 */
class ActivityMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'file_asset_id',
        'title',
        'url',
        'type',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
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

    /**
     * @return BelongsTo<FileAsset, $this>
     */
    public function fileAsset(): BelongsTo
    {
        return $this->belongsTo(FileAsset::class, 'file_asset_id');
    }
}
