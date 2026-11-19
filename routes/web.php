<?php

use App\Http\Controllers\ChunkUploadController;
use App\Http\Controllers\FileDownloadController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PhoneVerificationController;
use App\Http\Responses\LoginResponse;
use App\Livewire\Admin\AcademicStructureManager;
use App\Livewire\Admin\AuditLogViewer;
use App\Livewire\Admin\ClassManager;
use App\Livewire\Admin\NurseryStudentManager;
use App\Livewire\Admin\ParentApprovalsQueue;
use App\Livewire\Admin\PortfolioReviewQueue;
use App\Livewire\Admin\TeacherInvite;
use App\Livewire\Admin\UserManager;
use App\Livewire\Auth\AcceptInvitation;
use App\Livewire\Auth\TwoFactorSetup;
use App\Livewire\Profile\UserProfileManager;
use App\Livewire\Public\ClassCatalog;
use App\Livewire\Public\LecturerDirectory;
use App\Livewire\Public\LecturerPortfolioPage;
use App\Livewire\Teacher\PortfolioEditor;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $institution = current_institution() ?? Institution::first();

    return view('welcome', compact('institution'));
})->name('home');

Route::get('/offline', function () {
    return view('offline');
})->name('offline');

Route::get('/locale/{locale}', [LocaleController::class, 'switch'])
    ->name('locale.switch');

// Teacher / User Invitation Acceptance
Route::get('/invitation/{token}', AcceptInvitation::class)
    ->name('invitation.accept');

// Public Faculty Directory & Class Listings (§5.5, T-2.4)
Route::get('/lecturers', LecturerDirectory::class)->name('public.lecturers');
Route::get('/lecturers/{id}', LecturerPortfolioPage::class)->name('public.lecturers.show');
Route::get('/classes', ClassCatalog::class)->name('public.classes');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    // Awaiting Approval Screen (for pending parents/users)
    Route::get('/awaiting-approval', function () {
        /** @var User|null $user */
        $user = auth()->user();
        $profile = $user !== null ? $user->parentProfile()->with('children')->first() : null;

        return view('auth.awaiting-approval', [
            'user' => $user,
            'profile' => $profile,
        ]);
    })->name('awaiting-approval');

    // Phone verification via SMS OTP (§5.2)
    Route::post('/verification/phone/send', [PhoneVerificationController::class, 'sendOtp'])
        ->name('verification.phone.send');
    Route::post('/verification/phone/verify', [PhoneVerificationController::class, 'verifyOtp'])
        ->name('verification.phone.verify');

    // Two-Factor Authentication Management
    Route::get('/user/two-factor-setup', TwoFactorSetup::class)
        ->name('two-factor.setup');

    // User Profile, Security, Language & Notification Preferences (§1.7)
    Route::get('/user/profile', UserProfileManager::class)
        ->name('profile.show');

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
    })->middleware(['role:super_admin|admin', 'two_factor.enforce'])->name('admin.dashboard');

    // Faculty & User Management
    Route::get('/admin/teachers/invite', TeacherInvite::class)
        ->middleware(['role:super_admin|admin', 'two_factor.enforce'])
        ->name('admin.teachers.invite');

    // Parent Approvals Queue (§5.2)
    Route::get('/admin/approvals/parents', ParentApprovalsQueue::class)
        ->middleware(['role:super_admin|admin', 'two_factor.enforce'])
        ->name('admin.approvals.parents');

    // Nursery Students & Guardians Roster (§6.5, T-1.6)
    Route::get('/admin/nursery/students', NurseryStudentManager::class)
        ->middleware(['role:super_admin|admin', 'two_factor.enforce'])
        ->name('admin.nursery.students');

    // Admin User Directory (§4, §13, T-1.9)
    Route::get('/admin/users', UserManager::class)
        ->middleware(['role:super_admin|admin', 'two_factor.enforce'])
        ->name('admin.users');

    // Lecturer Portfolio Approval Queue (§5.5, T-2.3)
    Route::get('/admin/portfolios', PortfolioReviewQueue::class)
        ->middleware(['role:super_admin|admin', 'two_factor.enforce'])
        ->name('admin.portfolios');

    // Academic Structure: Years, Terms & Batches (§7.1, T-3.1)
    Route::get('/admin/academics', AcademicStructureManager::class)
        ->middleware(['role:super_admin|admin', 'two_factor.enforce'])
        ->name('admin.academics');

    // Classes Management CRUD (§7.1, T-3.2)
    Route::get('/admin/classes', ClassManager::class)
        ->middleware(['role:super_admin|admin', 'two_factor.enforce'])
        ->name('admin.classes');

    Route::get('/teacher/dashboard', function () {
        return view('dashboards.teacher');
    })->middleware('role:teacher')->name('teacher.dashboard');

    // Lecturer Portfolio Editor (§5.5, T-2.2)
    Route::get('/teacher/portfolio', PortfolioEditor::class)
        ->middleware('role:teacher')
        ->name('teacher.portfolio');

    Route::get('/parent/dashboard', function () {
        return view('dashboards.parent');
    })->middleware('role:parent')->name('parent.dashboard');

    Route::get('/student/dashboard', function () {
        return view('dashboards.student');
    })->middleware('role:student')->name('student.dashboard');

    Route::get('/cashier/dashboard', function () {
        return view('dashboards.cashier');
    })->middleware('role:cashier')->name('cashier.dashboard');

    Route::get('/printing/dashboard', function () {
        return view('dashboards.printing');
    })->middleware('role:super_admin|admin|printing_staff')->name('printing.dashboard');

    Route::get('/attendance/dashboard', function () {
        return view('dashboards.attendance');
    })->middleware('role:super_admin|admin|attendance_marshal')->name('attendance.dashboard');

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
        ->middleware(['permission:audit.view', 'two_factor.enforce'])
        ->name('admin.audit');
});
