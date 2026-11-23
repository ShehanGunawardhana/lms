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

    public function isFile(): bool
    {
        return $this->type === 'file';
    }

    public function isLink(): bool
    {
        return $this->type === 'link';
    }

    public function downloadUrl(): ?string
    {
        if ($this->isFile() && $this->fileAsset) {
            return $this->fileAsset->getDownloadUrl();
        }

        if ($this->isLink()) {
            return $this->url;
        }

        return null;
    }

    public function formattedSize(): ?string
    {
        if (! $this->fileAsset || ! $this->fileAsset->size) {
            return null;
        }

        $bytes = (int) $this->fileAsset->size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }

        return number_format($bytes / 1024, 0).' KB';
    }

    public function icon(): string
    {
        if ($this->isLink()) {
            return '🔗';
        }

        if (! $this->fileAsset) {
            return '📎';
        }

        $ext = strtolower($this->fileAsset->extension);

        return match ($ext) {
            'pdf' => '📄',
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg' => '🖼️',
            'zip', 'rar', 'tar', 'gz' => '📦',
            'mp3', 'wav', 'ogg' => '🎵',
            'mp4', 'mov', 'webm' => '🎥',
            'doc', 'docx' => '📝',
            default => '📎',
        };
    }

    public function getIconAttribute(): string
    {
        return $this->icon();
    }

    public function getFormattedSizeAttribute(): ?string
    {
        return $this->formattedSize();
    }
}
