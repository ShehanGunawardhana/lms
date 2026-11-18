<?php

namespace App\Services;

use App\Exceptions\FileUploadException;
use App\Jobs\ScanFileAsset;
use App\Models\FileAsset;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use ZipArchive;

class FileService
{
    /**
     * Store and validate an uploaded file according to portal security rules.
     *
     * @param  UploadedFile|string  $file  An UploadedFile or local path to assembled chunks
     * @param  string  $purpose  (e.g., 'submission', 'material', 'payment_slip', 'profile_photo')
     * @param  array<string>|null  $allowedTypes  Custom whitelist of extensions or kinds (e.g. ['pdf', 'jpg'])
     * @param  User|null  $uploader  User performing the upload
     *
     * @throws FileUploadException
     */
    public function store(
        UploadedFile|string $file,
        string $purpose = 'general',
        ?array $allowedTypes = null,
        ?User $uploader = null
    ): FileAsset {
        $realPath = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        $originalName = $file instanceof UploadedFile ? $file->getClientOriginalName() : basename($file);
        $declaredExt = strtolower($file instanceof UploadedFile ? $file->getClientOriginalExtension() : pathinfo($file, PATHINFO_EXTENSION));

        if (! file_exists($realPath) || ! is_readable($realPath)) {
            throw new FileUploadException(__('The file could not be read or does not exist.'));
        }

        $size = filesize($realPath);
        if ($size === false || $size === 0) {
            throw new FileUploadException(__('Empty files cannot be uploaded.'));
        }

        // 1. Size Validation (50 MB default)
        $maxSizeMb = (int) portal_setting('uploads.max_size_mb', 50);
        $maxSizeBytes = $maxSizeMb * 1024 * 1024;

        if ($size > $maxSizeBytes) {
            throw new FileUploadException(
                __('The file exceeds the maximum allowed size of :max MB.', ['max' => $maxSizeMb])
            );
        }

        // 2. Magic-byte verification and MIME detection
        $verifiedType = $this->verifyMagicBytes($realPath, $declaredExt);
        if ($verifiedType === null) {
            throw new FileUploadException(
                __('The file content does not match its declared type or is corrupt.')
            );
        }

        $verifiedExt = $verifiedType['extension'];
        $verifiedMime = $verifiedType['mime'];
        $kind = $verifiedType['kind'];

        // 3. Global & Contextual Type Whitelist Verification
        $globalWhitelist = portal_setting('uploads.allowed_types', ['mp4', 'mov', 'webm', 'pdf', 'jpg', 'png', 'zip']);
        if (! in_array($verifiedExt, $globalWhitelist, true) && ! in_array($declaredExt, $globalWhitelist, true)) {
            throw new FileUploadException(
                __('File type :ext is not allowed by institution policy.', ['ext' => $verifiedExt])
            );
        }

        if ($allowedTypes !== null && count($allowedTypes) > 0) {
            $normalizedAllowed = array_map('strtolower', $allowedTypes);
            if (! in_array($verifiedExt, $normalizedAllowed, true) && ! in_array($kind, $normalizedAllowed, true)) {
                throw new FileUploadException(
                    __('This upload only accepts: :types.', ['types' => implode(', ', $allowedTypes)])
                );
            }
        }

        // 4. Sanitize Images (strip metadata and re-encode)
        if ($kind === 'image') {
            $this->sanitizeImage($realPath, $verifiedExt);
            $size = (int) filesize($realPath);
        }

        // 5. Inspect ZIP Archives
        if ($kind === 'archive') {
            $this->inspectZip($realPath);
        }

        // 6. Calculate SHA-256 Hash
        $hash = hash_file('sha256', $realPath);
        if ($hash === false) {
            throw new FileUploadException(__('Failed to compute file integrity hash.'));
        }

        // 7. Store on private disk outside public root
        $uuid = (string) Str::uuid();
        $institutionId = current_institution_id() ?? 1;
        $year = date('Y');
        $month = date('m');
        $disk = 'private';
        $relativeDir = "institutions/{$institutionId}/{$year}/{$month}";
        $filename = "{$uuid}.{$verifiedExt}";
        $storagePath = "{$relativeDir}/{$filename}";

        $fileStream = fopen($realPath, 'rb');
        if ($fileStream === false) {
            throw new FileUploadException(__('Failed to open file stream for storage.'));
        }

        $saved = Storage::disk($disk)->put($storagePath, $fileStream);
        if (is_resource($fileStream)) {
            fclose($fileStream);
        }

        if (! $saved) {
            throw new FileUploadException(__('Failed to write file to private storage.'));
        }

        $fileAsset = FileAsset::create([
            'institution_id' => $institutionId,
            'uuid' => $uuid,
            'disk' => $disk,
            'path' => $storagePath,
            'original_name' => $originalName,
            'extension' => $verifiedExt,
            'mime_verified' => $verifiedMime,
            'size' => $size,
            'hash_sha256' => $hash,
            'kind' => $kind,
            'scan_status' => 'clean',
            'uploaded_by' => $uploader ? $uploader->id : auth()->id(),
            'purpose' => $purpose,
        ]);

        // 8. Queue malware and virus scan
        ScanFileAsset::dispatch($fileAsset);

        return $fileAsset;
    }

    /**
     * Re-encode image and strip EXIF metadata using Intervention Image.
     */
    public function sanitizeImage(string $path, string $ext): void
    {
        try {
            $manager = new ImageManager(new Driver);
            $image = $manager->read($path);

            if (in_array(strtolower($ext), ['jpg', 'jpeg'], true)) {
                $image->toJpeg(85)->save($path);
            } elseif (strtolower($ext) === 'png') {
                $image->toPng()->save($path);
            }
        } catch (\Throwable) {
            throw new FileUploadException(__('The image could not be processed or contains corrupt data.'));
        }
    }

    /**
     * Inspect ZIP archive entries, uncompressed size, and detect forbidden executables.
     */
    public function inspectZip(string $path): void
    {
        $zip = new ZipArchive;
        $status = $zip->open($path);

        if ($status !== true) {
            throw new FileUploadException(__('Unable to open and inspect the ZIP archive.'));
        }

        $maxEntries = (int) portal_setting('uploads.zip_max_entries', 200);
        $maxUncompressedMb = (int) portal_setting('uploads.zip_max_uncompressed_mb', 200);
        $maxUncompressedBytes = $maxUncompressedMb * 1024 * 1024;

        $numFiles = $zip->numFiles;
        if ($numFiles > $maxEntries) {
            $zip->close();
            throw new FileUploadException(
                __('The ZIP archive contains :count files, which exceeds the limit of :max.', [
                    'count' => $numFiles,
                    'max' => $maxEntries,
                ])
            );
        }

        $totalSize = 0;
        $forbiddenExtensions = [
            'exe', 'bat', 'cmd', 'sh', 'php', 'phtml', 'pl', 'py', 'js', 'vbs',
            'ps1', 'jar', 'msi', 'dll', 'com', 'scr', 'cpl', 'htc', 'asp', 'aspx',
        ];

        for ($i = 0; $i < $numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if (! $stat) {
                continue;
            }

            $name = $stat['name'];
            $entrySize = $stat['size'];

            // Prevent zip slip / directory traversal
            if (str_contains($name, '../') || str_contains($name, '..\\')) {
                $zip->close();
                throw new FileUploadException(__('The ZIP archive contains insecure relative paths.'));
            }

            // Check forbidden extensions
            $entryExt = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (in_array($entryExt, $forbiddenExtensions, true)) {
                $zip->close();
                throw new FileUploadException(
                    __('The ZIP archive contains an executable or script (:file) which is forbidden.', ['file' => $name])
                );
            }

            $totalSize += $entrySize;
            if ($totalSize > $maxUncompressedBytes) {
                $zip->close();
                throw new FileUploadException(
                    __('The uncompressed size of the ZIP exceeds the limit of :max MB.', ['max' => $maxUncompressedMb])
                );
            }
        }

        $zip->close();
    }

    /**
     * Inspect file header signature bytes against expected declared format.
     *
     * @return array{extension: string, mime: string, kind: string}|null
     */
    public function verifyMagicBytes(string $path, string $declaredExt): ?array
    {
        $handle = fopen($path, 'rb');
        if (! $handle) {
            return null;
        }

        $header = fread($handle, 64);
        fclose($handle);

        if ($header === false || strlen($header) < 4) {
            return null;
        }

        // Check JPEG: \xFF\xD8\xFF
        if (str_starts_with($header, "\xFF\xD8\xFF")) {
            return ['extension' => 'jpg', 'mime' => 'image/jpeg', 'kind' => 'image'];
        }

        // Check PNG: \x89PNG\r\n\x1a\n
        if (str_starts_with($header, "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A")) {
            return ['extension' => 'png', 'mime' => 'image/png', 'kind' => 'image'];
        }

        // Check PDF: %PDF-
        if (str_starts_with($header, '%PDF-')) {
            return ['extension' => 'pdf', 'mime' => 'application/pdf', 'kind' => 'document'];
        }

        // Check ZIP: PK\x03\x04 or PK\x05\x06 or PK\x07\x08
        if (str_starts_with($header, "PK\x03\x04") || str_starts_with($header, "PK\x05\x06") || str_starts_with($header, "PK\x07\x08")) {
            return ['extension' => 'zip', 'mime' => 'application/zip', 'kind' => 'archive'];
        }

        // Check WebM: \x1A\x45\xDF\xA3 (EBML ID)
        if (str_starts_with($header, "\x1A\x45\xDF\xA3")) {
            return ['extension' => 'webm', 'mime' => 'video/webm', 'kind' => 'video'];
        }

        // Check MP4 / MOV: Box header at offset 4 contains 'ftyp'
        if (strlen($header) >= 12 && substr($header, 4, 4) === 'ftyp') {
            $majorBrand = substr($header, 8, 4);
            $isQuickTime = ($majorBrand === 'qt  ' || $declaredExt === 'mov');

            return [
                'extension' => $isQuickTime ? 'mov' : 'mp4',
                'mime' => $isQuickTime ? 'video/quicktime' : 'video/mp4',
                'kind' => 'video',
            ];
        }

        return null;
    }
}
