<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminInboxController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\MajorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StudentAuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentInboxController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public Routes
Route::get('/', [ScheduleController::class, 'index'])->name('home');

// Student Authentication (Public)
Route::middleware('guest.student')->group(function () {
    Route::get('/siswa/login', [StudentAuthController::class, 'showLogin'])->name('student.login');
    Route::post('/siswa/login', [StudentAuthController::class, 'login']);
    Route::get('/siswa/register', [StudentAuthController::class, 'showRegister'])->name('student.register');
    Route::post('/siswa/register', [StudentAuthController::class, 'register']);
    Route::get('/siswa/lupa-password', [StudentAuthController::class, 'showForgotPassword'])->name('student.forgot-password');
    Route::post('/siswa/lupa-password', [StudentAuthController::class, 'sendResetLink'])->name('student.forgot-password.send');
    Route::get('/siswa/reset-password/{token}', [StudentAuthController::class, 'showResetPassword'])->name('student.reset-password');
    Route::post('/siswa/reset-password', [StudentAuthController::class, 'resetPassword'])->name('student.reset-password.update');
});

// Student Registration & Management (students/pendaftaran prefix)
Route::prefix('students/pendaftaran')->group(function () {
    // Public registration form
    Route::get('/daftar', [StudentController::class, 'create'])->name('student.register.form');
    Route::post('/daftar', [StudentController::class, 'store'])->name('student.register.store');

    // Certificate & Preview (public access with registration number)
    Route::get('/{registrationNumber}', [StudentController::class, 'certificate'])->name('student.certificate');
    Route::get('/{registrationNumber}/cetak', [StudentController::class, 'printCertificate'])->name('student.certificate.print');
    Route::get('/{registrationNumber}/preview', [StudentController::class, 'preview'])->name('student.preview');

    // Edit & Update — requires student auth + ownership
    Route::middleware('auth:student')->group(function () {
        Route::get('/{registrationNumber}/edit', [StudentController::class, 'edit'])->name('student.edit');
        Route::put('/{registrationNumber}', [StudentController::class, 'update'])->name('student.update');
    });
});

// Student Authenticated Routes
Route::middleware('auth:student')->group(function () {
    Route::get('/student/dashboard', [StudentAuthController::class, 'dashboard'])->name('student.dashboard');
    Route::post('/student/logout', [StudentAuthController::class, 'logout'])->name('student.logout');

    // Student Inbox
    Route::get('/student/inbox', [StudentInboxController::class, 'index'])->name('student.inbox');
    Route::get('/student/inbox/{message}', [StudentInboxController::class, 'show'])->name('student.inbox.show');
    Route::put('/student/inbox/{message}/read', [StudentInboxController::class, 'markAsRead'])->name('student.inbox.mark-as-read');
    Route::post('/student/inbox/mark-all-read', [StudentInboxController::class, 'markAllAsRead'])->name('student.inbox.mark-all-read');
    Route::delete('/student/inbox/{message}', [StudentInboxController::class, 'destroy'])->name('student.inbox.destroy');

    // Student Profile
    Route::get('/student/profile', [ProfileController::class, 'edit'])->name('student.profile.edit');
    Route::patch('/student/profile', [ProfileController::class, 'update'])->name('student.profile.update');
});

// Announcement
Route::get('/pengumuman', [AnnouncementController::class, 'index'])->name('announcement.index');
Route::post('/pengumuman', [AnnouncementController::class, 'check'])->name('announcement.check');

// Authenticated Routes (Admin)
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Admin - Inbox Management
    Route::get('/admin/inbox', [AdminInboxController::class, 'index'])->name('admin.inbox');
    Route::get('/admin/inbox/compose', [AdminInboxController::class, 'create'])->name('admin.inbox.compose');
    Route::post('/admin/inbox/send', [AdminInboxController::class, 'send'])->name('admin.inbox.send');
    Route::get('/admin/inbox/{message}', [AdminInboxController::class, 'show'])->name('admin.inbox.show');
    Route::delete('/admin/inbox/{message}', [AdminInboxController::class, 'destroy'])->name('admin.inbox.destroy');
    Route::get('/admin/inbox/api/students', [AdminInboxController::class, 'getStudents'])->name('admin.inbox.api.students');

    // Admin - Reports
    Route::get('/admin/reports', [AdminReportController::class, 'index'])->name('admin.reports');
    Route::get('/admin/reports/pdf', [AdminReportController::class, 'generatePdf'])->name('admin.reports.pdf');
    Route::get('/admin/reports/csv', [AdminReportController::class, 'exportCsv'])->name('admin.reports.csv');
    Route::get('/admin/reports/data', [AdminReportController::class, 'getData'])->name('admin.reports.data');

    // Admin - Students Management
    Route::get('/admin/students', [AdminController::class, 'students'])->name('admin.students');
    Route::get('/admin/students/{student}', [AdminController::class, 'showStudent'])->name('admin.students.show');
    Route::post('/admin/students/{student}/verify', [AdminController::class, 'verifyStudent'])->name('admin.students.verify');
    Route::post('/admin/students/{student}/allocate', [AdminController::class, 'allocateMajor'])->name('admin.students.allocate');
    Route::delete('/admin/students/{student}', [AdminController::class, 'destroyStudent'])->name('admin.students.destroy');

    // Admin - Academic Years Management
    Route::get('/admin/academic-years', [AcademicYearController::class, 'index'])->name('admin.academic-years');
    Route::post('/admin/academic-years', [AcademicYearController::class, 'store'])->name('admin.academic-years.store');
    Route::post('/admin/academic-years/set-context', [AcademicYearController::class, 'setContext'])->name('admin.academic-years.set-context');
    Route::put('/admin/academic-years/{academicYear}', [AcademicYearController::class, 'update'])->name('admin.academic-years.update');
    Route::post('/admin/academic-years/{academicYear}/activate', [AcademicYearController::class, 'activate'])->name('admin.academic-years.activate');
    Route::post('/admin/academic-years/{academicYear}/close', [AcademicYearController::class, 'close'])->name('admin.academic-years.close');
    Route::delete('/admin/academic-years/{academicYear}', [AcademicYearController::class, 'destroy'])->name('admin.academic-years.destroy');
    Route::get('/admin/academic-years/{academicYear}/majors', [AcademicYearController::class, 'majorConfig'])->name('admin.academic-years.major-config');
    Route::put('/admin/academic-years/{academicYear}/majors', [AcademicYearController::class, 'updateMajorConfig'])->name('admin.academic-years.update-major-config');

    // Admin - Majors Management
    Route::get('/admin/majors', [MajorController::class, 'index'])->name('admin.majors');
    Route::post('/admin/majors', [MajorController::class, 'store'])->name('admin.majors.store');
    Route::put('/admin/majors/{major}', [MajorController::class, 'update'])->name('admin.majors.update');
    Route::delete('/admin/majors/{major}', [MajorController::class, 'destroy'])->name('admin.majors.destroy');

    // Admin - Schedules Management
    Route::get('/admin/schedules', [ScheduleController::class, 'adminIndex'])->name('admin.schedules');
    Route::post('/admin/schedules', [ScheduleController::class, 'store'])->name('admin.schedules.store');
    Route::put('/admin/schedules/{schedule}', [ScheduleController::class, 'update'])->name('admin.schedules.update');
    Route::delete('/admin/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('admin.schedules.destroy');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
