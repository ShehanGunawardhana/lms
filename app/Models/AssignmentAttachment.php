<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $assignment_id
 * @property int|null $file_asset_id
 * @property string $title
 * @property string $type
 * @property string|null $url
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Assignment|null $assignment
 * @property-read FileAsset|null $fileAsset
 */
class AssignmentAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'file_asset_id',
        'title',
        'type',
        'url',
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
     * @return BelongsTo<Assignment, $this>
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
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
        return $this->type === 'file' && $this->file_asset_id !== null;
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

        return $this->url;
    }

    public function formattedSize(): string
    {
        if ($this->fileAsset && $this->fileAsset->size > 0) {
            $bytes = $this->fileAsset->size;
            if ($bytes >= 1048576) {
                return number_format($bytes / 1048576, 1).' MB';
            }

            return number_format($bytes / 1024, 0).' KB';
        }

        return '';
    }

    public function icon(): string
    {
        if ($this->isLink()) {
            return 'link';
        }

        $fileAsset = $this->fileAsset;
        $ext = $fileAsset ? strtolower($fileAsset->extension) : '';

        return match ($ext) {
            'pdf' => 'document-text',
            'doc', 'docx' => 'document',
            'zip', 'rar', '7z' => 'archive-box',
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'photo',
            'mp4', 'mov', 'webm' => 'film',
            default => 'paper-clip',
        };
    }
}
