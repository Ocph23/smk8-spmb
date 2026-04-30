<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\EnrollmentWaveController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminAnnouncementController;
use App\Http\Controllers\AdminInboxController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\DocumentTemplateController;
use App\Http\Controllers\MajorController;
use App\Http\Controllers\PanitiaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistrationDocumentController;
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
    Route::get('/login', [StudentAuthController::class, 'showLogin'])->name('student.login');
    Route::post('/login', [StudentAuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/daftar-akun', [StudentAuthController::class, 'showRegister'])->name('student.register');
    Route::post('/daftar-akun', [StudentAuthController::class, 'register'])->middleware('throttle:10,1');
    Route::get('/lupa-password', [StudentAuthController::class, 'showForgotPassword'])->name('student.forgot-password');
    Route::post('/lupa-password', [StudentAuthController::class, 'sendResetLink'])->name('student.forgot-password.send')->middleware('throttle:5,1');
    Route::get('/reset-password/{token}', [StudentAuthController::class, 'showResetPassword'])->name('student.reset-password');
    Route::post('/reset-password', [StudentAuthController::class, 'resetPassword'])->name('student.reset-password.update');
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

// Public document template download
Route::get('/dokumen', [DocumentTemplateController::class, 'publicIndex'])->name('documents.indexx');
Route::get('/dokumen/{document}/download', [DocumentTemplateController::class, 'download'])->name('documents.download');

// Announcement
Route::get('/pengumuman', [AnnouncementController::class, 'index'])->name('announcement.index');
Route::post('/pengumuman', [AnnouncementController::class, 'check'])->name('announcement.check');

// Authenticated Routes (Admin & Panitia) — dapat diakses oleh admin dan panitia
Route::middleware(['auth', 'verified', 'panitia'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Inbox Management
    Route::get('/admin/inbox', [AdminInboxController::class, 'index'])->name('admin.inbox');
    Route::get('/admin/inbox/compose', [AdminInboxController::class, 'create'])->name('admin.inbox.compose');
    Route::post('/admin/inbox/send', [AdminInboxController::class, 'send'])->name('admin.inbox.send');
    Route::get('/admin/inbox/{message}', [AdminInboxController::class, 'show'])->name('admin.inbox.show');
    Route::delete('/admin/inbox/{message}', [AdminInboxController::class, 'destroy'])->name('admin.inbox.destroy');
    Route::get('/admin/inbox/api/students', [AdminInboxController::class, 'getStudents'])->name('admin.inbox.api.students');


    // Reports (view-only)
    Route::get('/admin/reports', [AdminReportController::class, 'index'])->name('admin.reports');
    Route::get('/admin/reports/data', [AdminReportController::class, 'getData'])->name('admin.reports.data');

    // Students Management
    Route::get('/admin/students', [AdminController::class, 'students'])->name('admin.students');
    Route::get('/admin/students/{student}', [AdminController::class, 'showStudent'])->name('admin.students.show');
    Route::post('/admin/students/{student}/verify', [AdminController::class, 'verifyStudent'])->name('admin.students.verify');
    Route::delete('/admin/students/{student}', [AdminController::class, 'destroyStudent'])->name('admin.students.destroy');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Authenticated Routes (Admin only) — eksklusif untuk admin
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    // Students — admin-only actions
    Route::post('/admin/students/{student}/allocate', [AdminController::class, 'allocateMajor'])->name('admin.students.allocate');

    // Reports — export (admin-only)
    Route::get('/admin/reports/pdf', [AdminReportController::class, 'generatePdf'])->name('admin.reports.pdf');
    Route::get('/admin/reports/csv', [AdminReportController::class, 'exportCsv'])->name('admin.reports.csv');

    // Panitia Management
    Route::get('/admin/panitia', [PanitiaController::class, 'index'])->name('admin.panitia');
    Route::post('/admin/panitia', [PanitiaController::class, 'store'])->name('admin.panitia.store');
    Route::put('/admin/panitia/{user}', [PanitiaController::class, 'update'])->name('admin.panitia.update');
    Route::delete('/admin/panitia/{user}', [PanitiaController::class, 'destroy'])->name('admin.panitia.destroy');

    // Academic Years Management
    Route::get('/admin/academic-years', [AcademicYearController::class, 'index'])->name('admin.academic-years');
    Route::post('/admin/academic-years', [AcademicYearController::class, 'store'])->name('admin.academic-years.store');
    Route::post('/admin/academic-years/set-context', [AcademicYearController::class, 'setContext'])->name('admin.academic-years.set-context');
    Route::put('/admin/academic-years/{academicYear}', [AcademicYearController::class, 'update'])->name('admin.academic-years.update');
    Route::post('/admin/academic-years/{academicYear}/activate', [AcademicYearController::class, 'activate'])->name('admin.academic-years.activate');
    Route::post('/admin/academic-years/{academicYear}/close', [AcademicYearController::class, 'close'])->name('admin.academic-years.close');
    Route::delete('/admin/academic-years/{academicYear}', [AcademicYearController::class, 'destroy'])->name('admin.academic-years.destroy');
    Route::get('/admin/academic-years/{academicYear}/majors', [AcademicYearController::class, 'majorConfig'])->name('admin.academic-years.major-config');
    Route::put('/admin/academic-years/{academicYear}/majors', [AcademicYearController::class, 'updateMajorConfig'])->name('admin.academic-years.update-major-config');

    // Majors Management
    Route::get('/admin/majors', [MajorController::class, 'index'])->name('admin.majors');
    Route::post('/admin/majors', [MajorController::class, 'store'])->name('admin.majors.store');
    Route::put('/admin/majors/{major}', [MajorController::class, 'update'])->name('admin.majors.update');
    Route::delete('/admin/majors/{major}', [MajorController::class, 'destroy'])->name('admin.majors.destroy');

    // Schedules Management
    Route::get('/admin/schedules', [ScheduleController::class, 'adminIndex'])->name('admin.schedules');
    Route::post('/admin/schedules', [ScheduleController::class, 'store'])->name('admin.schedules.store');
    Route::put('/admin/schedules/{schedule}', [ScheduleController::class, 'update'])->name('admin.schedules.update');
    Route::delete('/admin/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('admin.schedules.destroy');

    // Document Templates
    Route::get('/admin/documents', [DocumentTemplateController::class, 'index'])->name('admin.documents');
    Route::post('/admin/documents', [DocumentTemplateController::class, 'store'])->name('admin.documents.store');
    Route::post('/admin/documents/{document}', [DocumentTemplateController::class, 'update'])->name('admin.documents.update');
    Route::delete('/admin/documents/{document}', [DocumentTemplateController::class, 'destroy'])->name('admin.documents.destroy');
    Route::patch('/admin/documents/{document}/toggle', [DocumentTemplateController::class, 'toggleActive'])->name('admin.documents.toggle');

    // Registration Documents
    Route::get('/admin/registration-documents', [RegistrationDocumentController::class, 'index'])->name('admin.registration-documents');
    Route::post('/admin/registration-documents', [RegistrationDocumentController::class, 'store'])->name('admin.registration-documents.store');
    Route::put('/admin/registration-documents/{registrationDocument}', [RegistrationDocumentController::class, 'update'])->name('admin.registration-documents.update');
    Route::delete('/admin/registration-documents/{registrationDocument}', [RegistrationDocumentController::class, 'destroy'])->name('admin.registration-documents.destroy');
    Route::patch('/admin/registration-documents/{registrationDocument}/toggle', [RegistrationDocumentController::class, 'toggleActive'])->name('admin.registration-documents.toggle');
    Route::post('/admin/registration-documents/reorder', [RegistrationDocumentController::class, 'reorder'])->name('admin.registration-documents.reorder');

    // Announcements
    Route::get('/admin/pengumuman', [AdminAnnouncementController::class, 'index'])->name('admin.announcements');
    Route::post('/admin/pengumuman', [AdminAnnouncementController::class, 'store'])->name('admin.announcements.store');
    Route::put('/admin/pengumuman/{announcement}', [AdminAnnouncementController::class, 'update'])->name('admin.announcements.update');
    Route::delete('/admin/pengumuman/{announcement}', [AdminAnnouncementController::class, 'destroy'])->name('admin.announcements.destroy');

    // Enrollment Waves Management
    Route::prefix('/admin/enrollment-waves')->group(function () {
        Route::get('/', [EnrollmentWaveController::class, 'index'])->name('admin.enrollment-waves.index');
        Route::post('/', [EnrollmentWaveController::class, 'store'])->name('admin.enrollment-waves.store');
        Route::get('/{enrollmentWave}', [EnrollmentWaveController::class, 'show'])->name('admin.enrollment-waves.show');
        Route::put('/{enrollmentWave}', [EnrollmentWaveController::class, 'update'])->name('admin.enrollment-waves.update');
        Route::delete('/{enrollmentWave}', [EnrollmentWaveController::class, 'destroy'])->name('admin.enrollment-waves.destroy');
        Route::post('/{enrollmentWave}/open', [EnrollmentWaveController::class, 'open'])->name('admin.enrollment-waves.open');
        Route::post('/{enrollmentWave}/close', [EnrollmentWaveController::class, 'close'])->name('admin.enrollment-waves.close');
        Route::post('/{enrollmentWave}/announce', [EnrollmentWaveController::class, 'announce'])->name('admin.enrollment-waves.announce');
        Route::put('/{enrollmentWave}/quotas', [EnrollmentWaveController::class, 'updateQuotas'])->name('admin.enrollment-waves.update-quotas');
    });
});

require __DIR__ . '/auth.php';
