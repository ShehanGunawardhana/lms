<?php

use App\Http\Controllers\FileDownloadController;
use App\Livewire\Admin\AuditLogViewer;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/files/download/{uuid}', [FileDownloadController::class, 'download'])
        ->name('files.download')
        ->middleware('signed');

    Route::get('/admin/audit', AuditLogViewer::class)
        ->middleware('permission:audit.view')
        ->name('admin.audit');
});
