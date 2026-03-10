<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\MajorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StudentController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public Routes
Route::get('/', [ScheduleController::class, 'index'])->name('home');

// Student Registration
Route::get('/daftar', [StudentController::class, 'create'])->name('student.register');
Route::post('/daftar', [StudentController::class, 'store'])->name('student.register.store');
Route::get('/pendaftaran/{registrationNumber}', [StudentController::class, 'certificate'])->name('student.certificate');
Route::get('/pendaftaran/{registrationNumber}/cetak', [StudentController::class, 'printCertificate'])->name('student.certificate.print');

// Announcement
Route::get('/pengumuman', [AnnouncementController::class, 'index'])->name('announcement.index');
Route::post('/pengumuman', [AnnouncementController::class, 'check'])->name('announcement.check');

// Authenticated Routes (Admin)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Admin - Students Management
    Route::get('/admin/students', [AdminController::class, 'students'])->name('admin.students');
    Route::get('/admin/students/{student}', [AdminController::class, 'showStudent'])->name('admin.students.show');
    Route::post('/admin/students/{student}/verify', [AdminController::class, 'verifyStudent'])->name('admin.students.verify');
    Route::post('/admin/students/{student}/allocate', [AdminController::class, 'allocateMajor'])->name('admin.students.allocate');
    Route::delete('/admin/students/{student}', [AdminController::class, 'destroyStudent'])->name('admin.students.destroy');

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
