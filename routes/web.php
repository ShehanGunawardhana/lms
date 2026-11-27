<?php

use App\Enums\RoleName;
use App\Http\Controllers\ChunkUploadController;
use App\Http\Controllers\FileDownloadController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PhoneVerificationController;
use App\Http\Responses\LoginResponse;
use App\Livewire\Admin\AcademicRolloverManager;
use App\Livewire\Admin\AcademicStructureManager;
use App\Livewire\Admin\AuditLogViewer;
use App\Livewire\Admin\ClassEnrollmentManager;
use App\Livewire\Admin\ClassManager;
use App\Livewire\Admin\ClassScheduleManager;
use App\Livewire\Admin\NurseryStudentManager;
use App\Livewire\Admin\ParentApprovalsQueue;
use App\Livewire\Admin\PortfolioReviewQueue;
use App\Livewire\Admin\TeacherInvite;
use App\Livewire\Admin\UserManager;
use App\Livewire\Auth\AcceptInvitation;
use App\Livewire\Auth\TwoFactorSetup;
use App\Livewire\Parent\ParentActivityFeed;
use App\Livewire\Parent\ParentActivityShow;
use App\Livewire\Parent\ParentChildProgressView;
use App\Livewire\Parent\ParentChildSafetyManager;
use App\Livewire\Profile\UserProfileManager;
use App\Livewire\Public\ClassCatalog;
use App\Livewire\Public\LecturerDirectory;
use App\Livewire\Public\LecturerPortfolioPage;
use App\Livewire\Student\AssignmentList;
use App\Livewire\Student\AssignmentShow;
use App\Livewire\Teacher\ActivityManager;
use App\Livewire\Teacher\ActivityReviewManager;
use App\Livewire\Teacher\AssignmentManager;
use App\Livewire\Teacher\AssignmentReviewManager;
use App\Livewire\Teacher\PortfolioEditor;
use App\Livewire\Teacher\QuestionBank;
use App\Livewire\Teacher\TeacherDashboard;
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

    // Year-End Academic Rollover Wizard (§7.1, T-3.7)
    Route::get('/admin/academics/rollover', AcademicRolloverManager::class)
        ->middleware(['role:super_admin|admin', 'two_factor.enforce'])
        ->name('admin.academics.rollover');

    // Classes Management CRUD (§7.1, T-3.2)
    Route::get('/admin/classes', ClassManager::class)
        ->middleware(['role:super_admin|admin', 'two_factor.enforce'])
        ->name('admin.classes');

    // Class Schedules & Session Generator (§7.1, T-3.3)
    Route::get('/admin/classes/{classId}/schedule', ClassScheduleManager::class)
        ->middleware(['role:super_admin|admin', 'two_factor.enforce'])
        ->name('admin.classes.schedule');

    // Class Student Enrollments & Roster (§7.1, T-3.5)
    Route::get('/admin/classes/{classId}/enrollments', ClassEnrollmentManager::class)
        ->middleware(['role:super_admin|admin', 'two_factor.enforce'])
        ->name('admin.classes.enrollments');

    // Teacher & Faculty Workspace Dashboard (§4, §7.1, T-3.6)
    Route::get('/teacher/dashboard', TeacherDashboard::class)
        ->middleware('role:teacher')
        ->name('teacher.dashboard');

    // Nursery Module Activities Manager (§6.1, T-4.1)
    Route::get('/teacher/activities', ActivityManager::class)
        ->middleware(['role:super_admin|admin|teacher', 'two_factor.enforce'])
        ->name('teacher.activities');

    // Nursery Module Teacher Submissions Review Screen (§6.4, T-4.7)
    Route::get('/teacher/activities/{activity}/reviews', ActivityReviewManager::class)
        ->middleware(['role:super_admin|admin|teacher', 'two_factor.enforce'])
        ->name('teacher.activities.reviews');

    // Assignments Manager (§8, T-5.1)
    Route::get('/teacher/assignments', AssignmentManager::class)
        ->middleware(['role:super_admin|admin|teacher', 'two_factor.enforce'])
        ->name('teacher.assignments');

    // Assignment Submissions Review Screen (§8, T-5.4)
    Route::get('/teacher/assignments/{assignment}/reviews', AssignmentReviewManager::class)
        ->middleware(['role:super_admin|admin|teacher', 'two_factor.enforce'])
        ->name('teacher.assignments.reviews');

    // Question Bank with Tags & All Question Types (§9, T-6.1)
    Route::get('/teacher/question-bank', QuestionBank::class)
        ->middleware(['role:super_admin|admin|teacher', 'two_factor.enforce'])
        ->name('teacher.questions.index');

    // Student & Parent Coursework Assignments Hub (§8, T-5.5)
    Route::get('/student/assignments', AssignmentList::class)
        ->middleware(['auth'])
        ->name('student.assignments.index');

    Route::get('/assignments', function () {
        /** @var User|null $user */
        $user = auth()->user();
        if ($user !== null && ($user->hasRole(RoleName::Student->value) || $user->hasRole(RoleName::Parent->value))) {
            return redirect()->route('student.assignments.index');
        }

        return redirect()->route('teacher.assignments');
    })->middleware(['auth'])->name('assignments.index');

    // Assignment Show & Submissions (§8, T-5.2)
    Route::get('/assignments/{assignment}', AssignmentShow::class)
        ->middleware(['auth'])
        ->name('assignments.show');

    Route::get('/student/assignments/{assignment}', AssignmentShow::class)
        ->middleware(['auth'])
        ->name('student.assignments.show');

    Route::get('/activities', function () {
        /** @var User|null $user */
        $user = auth()->user();
        if ($user !== null && $user->hasRole(RoleName::Parent->value)) {
            return redirect()->route('parent.activities');
        }

        return redirect()->route('teacher.activities');
    })->middleware(['auth'])->name('activities.index');

    // Nursery Module Parent Activity Feed & Show (§6.6, T-4.4)
    Route::get('/parent/activities', ParentActivityFeed::class)
        ->middleware('role:parent')
        ->name('parent.activities');

    Route::get('/parent/activities/{activity}', ParentActivityShow::class)
        ->middleware('role:parent')
        ->name('parent.activities.show');

    // Nursery Module Parent Child Health & Safety Manager (§6.5, T-4.8)
    Route::get('/parent/safety/{student?}', ParentChildSafetyManager::class)
        ->middleware('role:parent')
        ->name('parent.safety');

    Route::get('/parent/children/{student}/safety', ParentChildSafetyManager::class)
        ->middleware('role:parent')
        ->name('parent.children.safety');

    // Nursery Module Parent Child Learning Progress & Milestones (§6.6, T-4.9)
    Route::get('/parent/progress/{student?}', ParentChildProgressView::class)
        ->middleware('role:parent')
        ->name('parent.progress');

    Route::get('/parent/children/{student}/progress', ParentChildProgressView::class)
        ->middleware('role:parent')
        ->name('parent.children.progress');

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
