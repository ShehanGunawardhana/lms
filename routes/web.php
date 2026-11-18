<?php

use App\Http\Controllers\ChunkUploadController;
use App\Http\Controllers\FileDownloadController;
use App\Livewire\Admin\AuditLogViewer;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/files/upload/chunk', [ChunkUploadController::class, 'upload'])
        ->name('files.chunk-upload');

    Route::get('/files/download/{uuid}', [FileDownloadController::class, 'download'])
        ->name('files.download')
        ->middleware('signed');

    Route::get('/admin/audit', AuditLogViewer::class)
        ->middleware('permission:audit.view')
        ->name('admin.audit');
});
