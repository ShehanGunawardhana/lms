<?php

namespace App\Models;

use App\Support\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\URL;

class FileAsset extends Model
{
    use BelongsToInstitution, HasFactory, SoftDeletes;

    protected $fillable = [
        'institution_id',
        'uuid',
        'disk',
        'path',
        'original_name',
        'extension',
        'mime_verified',
        'size',
        'hash_sha256',
        'kind',
        'scan_status',
        'uploaded_by',
        'purpose',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isScanClean(): bool
    {
        return $this->scan_status === 'clean';
    }

    public function isPending(): bool
    {
        return $this->scan_status === 'pending';
    }

    public function isInfected(): bool
    {
        return $this->scan_status === 'infected';
    }

    public function isImage(): bool
    {
        return $this->kind === 'image';
    }

    public function isVideo(): bool
    {
        return $this->kind === 'video';
    }

    public function isPdf(): bool
    {
        return $this->extension === 'pdf';
    }

    public function isZip(): bool
    {
        return $this->kind === 'archive' || $this->extension === 'zip';
    }

    public function isAudio(): bool
    {
        return $this->kind === 'audio';
    }

    /**
     * Generate a signed temporary download URL.
     */
    public function getDownloadUrl(int $expirationMinutes = 60): string
    {
        return URL::temporarySignedRoute(
            'files.download',
            now()->addMinutes($expirationMinutes),
            ['uuid' => $this->uuid]
        );
    }

    /**
     * Generate a signed temporary inline streaming URL (for images and audio).
     */
    public function getInlineUrl(int $expirationMinutes = 60): string
    {
        return URL::temporarySignedRoute(
            'files.download',
            now()->addMinutes($expirationMinutes),
            ['uuid' => $this->uuid, 'inline' => 1]
        );
    }

    public function temporarySignedUrl(?\DateTimeInterface $expiration = null): string
    {
        $minutes = $expiration !== null ? max(1, (int) now()->diffInMinutes($expiration)) : 60;

        return $this->getDownloadUrl($minutes);
    }
}
