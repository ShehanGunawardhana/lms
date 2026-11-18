<?php

namespace App\Http\Controllers;

use App\Models\FileAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileDownloadController extends Controller
{
    /**
     * Securely download or view a file asset via signed URL and policy gate.
     */
    public function download(Request $request, string $uuid): StreamedResponse|Response
    {
        $fileAsset = FileAsset::query()->where('uuid', $uuid)->firstOrFail();

        Gate::authorize('view', $fileAsset);

        if ($fileAsset->isInfected()) {
            abort(403, __('This file has been quarantined due to a security violation.'));
        }

        $disk = Storage::disk($fileAsset->disk);
        if (! $disk->exists($fileAsset->path)) {
            abort(404, __('The requested file could not be found in storage.'));
        }

        return $disk->download(
            $fileAsset->path,
            $fileAsset->original_name,
            ['Content-Type' => $fileAsset->mime_verified]
        );
    }
}
