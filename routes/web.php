<?php

use App\Http\Controllers\ChunkUploadController;
use App\Http\Controllers\FileDownloadController;
use App\Http\Controllers\LocaleController;
use App\Http\Responses\LoginResponse;
use App\Livewire\Admin\AuditLogViewer;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/offline', function () {
    return view('offline');
})->name('offline');

Route::get('/locale/{locale}', [LocaleController::class, 'switch'])
    ->name('locale.switch');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    // Awaiting Approval Screen (for pending parents/users)
    Route::get('/awaiting-approval', function () {
        return view('auth.awaiting-approval');
    })->name('awaiting-approval');

    // Central Dashboard Redirect (routes user to their specific dashboard based on role)
    Route::get('/dashboard', function () {
        $user = auth()->user();
        if ($user && $user->isPending()) {
            return redirect()->route('awaiting-approval');
        }

        return redirect(app(LoginResponse::class)->getRedirectUrlForUser($user));
    })->name('dashboard');

    // Role-specific Dashboards
    Route::get('/admin/dashboard', function () {
        return view('dashboards.admin');
    })->middleware('role:super_admin|admin')->name('admin.dashboard');

    Route::get('/teacher/dashboard', function () {
        return view('dashboards.teacher');
    })->middleware('role:teacher')->name('teacher.dashboard');

    Route::get('/parent/dashboard', function () {
        return view('dashboards.parent');
    })->middleware('role:parent')->name('parent.dashboard');

    Route::get('/student/dashboard', function () {
        return view('dashboards.student');
    })->middleware('role:student')->name('student.dashboard');

    Route::get('/cashier/dashboard', function () {
        return view('dashboards.cashier');
    })->middleware('role:cashier')->name('cashier.dashboard');

    // Station Mode (Kiosk) Routes
    Route::get('/station/attendance', function () {
        return view('stations.attendance');
    })->middleware('role:super_admin|admin|attendance_marshal')->name('station.attendance');

    Route::get('/station/printing', function () {
        return view('stations.printing');
    })->middleware('role:super_admin|admin|printing_staff')->name('station.printing');

    // File Management
    Route::post('/files/upload/chunk', [ChunkUploadController::class, 'upload'])
        ->name('files.chunk-upload');

    Route::get('/files/download/{uuid}', [FileDownloadController::class, 'download'])
        ->name('files.download')
        ->middleware('signed');

    // Admin Tools
    Route::get('/admin/audit', AuditLogViewer::class)
        ->middleware('permission:audit.view')
        ->name('admin.audit');
});
