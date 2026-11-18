<?php

use App\Livewire\Admin\AuditLogViewer;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Authenticated Admin Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/audit', AuditLogViewer::class)
        ->middleware('permission:audit.view')
        ->name('admin.audit');
});
