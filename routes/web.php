<?php

use App\Http\Controllers\AcademicTranscriptController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CourseApprovalController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseEnrollmentController;
use App\Http\Controllers\CourseMaterialController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\TeacherManagementController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
})->name('home');

Route::get('/language/{locale}', LanguageController::class)->name('language.switch');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');

    Route::middleware('permission:courses.create')->group(function () {
        Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
        Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
    });

    Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');

    Route::middleware('permission:courses.update.own')->group(function () {
        Route::get('/courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
        Route::put('/courses/{course}', [CourseController::class, 'update'])->name('courses.update');
        Route::patch('/courses/{course}', [CourseController::class, 'update']);
    });

    Route::post('/courses/{course}/submit-approval', [CourseController::class, 'submitForApproval'])
        ->middleware('permission:courses.submit')
        ->name('courses.submit');

    Route::middleware('permission:materials.manage.own')->group(function () {
        Route::post('/courses/{course}/materials', [CourseMaterialController::class, 'store'])
            ->name('courses.materials.store');
        Route::delete('/courses/{course}/materials/{material}', [CourseMaterialController::class, 'destroy'])
            ->name('courses.materials.destroy');
    });

    Route::middleware('permission:materials.view')->group(function () {
        Route::get('/courses/{course}/materials/{material}/download', [CourseMaterialController::class, 'download'])
            ->name('courses.materials.download');
    });

    Route::middleware('permission:courses.enroll_students')->group(function () {
        Route::post('/courses/{course}/students', [CourseEnrollmentController::class, 'store'])
            ->name('courses.students.store');
        Route::delete('/courses/{course}/students/{student}', [CourseEnrollmentController::class, 'destroy'])
            ->name('courses.students.destroy');
    });

    Route::patch('/courses/{course}/students/{student}/grade', [CourseEnrollmentController::class, 'grade'])
        ->middleware('permission:courses.grade.own')
        ->name('courses.students.grade');

    Route::prefix('hod')->name('hod.')->middleware(['role:head_of_department', 'permission:courses.review'])->group(function () {
        Route::get('/courses/pending', [CourseApprovalController::class, 'index'])->name('courses.pending');
        Route::post('/courses/{course}/approve', [CourseApprovalController::class, 'approve'])->name('courses.approve');
        Route::post('/courses/{course}/reject', [CourseApprovalController::class, 'reject'])->name('courses.reject');
        Route::post('/courses/{course}/complete', [CourseApprovalController::class, 'markCompleted'])->name('courses.complete');
    });

    Route::prefix('hod')->name('hod.')->middleware(['role:head_of_department', 'permission:teachers.manage'])->group(function () {
        Route::get('/teachers', [TeacherManagementController::class, 'index'])->name('teachers.index');
        Route::get('/teachers/create', [TeacherManagementController::class, 'create'])->name('teachers.create');
        Route::post('/teachers', [TeacherManagementController::class, 'store'])->name('teachers.store');
        Route::get('/teachers/{teacher}/edit', [TeacherManagementController::class, 'edit'])->name('teachers.edit');
        Route::put('/teachers/{teacher}', [TeacherManagementController::class, 'update'])->name('teachers.update');
        Route::delete('/teachers/{teacher}', [TeacherManagementController::class, 'destroy'])->name('teachers.destroy');
    });

    Route::prefix('admin')->name('admin.')->middleware(['role:administrator', 'permission:users.manage'])->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::patch('/users/{user}/roles', [UserManagementController::class, 'syncRoles'])->name('users.roles');
    });

    Route::prefix('admin')->name('admin.')->middleware(['role:administrator', 'permission:transcripts.view'])->group(function () {
        Route::get('/students/{student}/transcripts', [AcademicTranscriptController::class, 'index'])->name('transcripts.index');
        Route::get('/transcripts/{transcript}', [AcademicTranscriptController::class, 'show'])->name('transcripts.show');
    });

    Route::post('/admin/students/{student}/transcripts', [AcademicTranscriptController::class, 'store'])
        ->middleware(['role:administrator', 'permission:transcripts.create'])
        ->name('admin.transcripts.store');
});
