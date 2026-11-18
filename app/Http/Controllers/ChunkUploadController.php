<?php

namespace App\Http\Controllers;

use App\Exceptions\FileUploadException;
use App\Services\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

class ChunkUploadController extends Controller
{
    public function __construct(
        protected FileService $fileService
    ) {}

    /**
     * Handle chunked, resumable file upload from Resumable.js.
     */
    public function upload(Request $request): JsonResponse
    {
        $receiver = new FileReceiver(
            'file',
            $request,
            HandlerFactory::classFromRequest($request)
        );

        if (! $receiver->isUploaded()) {
            return response()->json([
                'status' => 'error',
                'message' => __('No file chunk was detected in the request.'),
            ], 400);
        }

        $save = $receiver->receive();

        if ($save->isFinished()) {
            $file = $save->getFile();

            $purpose = (string) $request->input('purpose', 'general');
            $allowedTypesInput = $request->input('allowed_types');
            $allowedTypes = is_string($allowedTypesInput)
                ? explode(',', $allowedTypesInput)
                : (is_array($allowedTypesInput) ? $allowedTypesInput : null);

            try {
                $fileAsset = $this->fileService->store(
                    $file,
                    $purpose,
                    $allowedTypes,
                    $request->user()
                );

                @unlink($file->getPathname());

                return response()->json([
                    'status' => 'success',
                    'uuid' => $fileAsset->uuid,
                    'original_name' => $fileAsset->original_name,
                    'size' => $fileAsset->size,
                    'kind' => $fileAsset->kind,
                    'download_url' => $fileAsset->getDownloadUrl(),
                ]);
            } catch (FileUploadException $e) {
                @unlink($file->getPathname());

                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            }
        }

        $handler = $save->handler();

        return response()->json([
            'status' => 'uploading',
            'progress' => $handler->getPercentageDone(),
        ]);
    }
}
