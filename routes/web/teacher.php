<?php

use App\Http\Controllers\Teacher\DutyDashboardController;
use App\Http\Controllers\Teacher\DutyReportController;
use App\Http\Controllers\Teacher\TeacherAttendanceController;
use App\Http\Controllers\Teacher\TeacherClassAttendanceController;
use App\Http\Controllers\Teacher\TeacherDashboardController;
use App\Http\Controllers\Teacher\TeacherJournalController;
use App\Http\Controllers\Teacher\TeacherLateEntryRequestController;
use App\Http\Controllers\Teacher\TeacherLeaveRequestController;
use App\Http\Controllers\Teacher\TeacherStudentAttendanceRecapController;
use App\Http\Controllers\Teacher\TeacherSubstituteTaskController;
use App\Http\Controllers\Teacher\TeacherTeachingJournalController;
use App\Http\Controllers\Teacher\TeacherTeachingModuleController;
use Illuminate\Support\Facades\Route;

Route::middleware('role:teacher')->group(function () {
    Route::get('/dashboard', TeacherDashboardController::class)->name('teacher.dashboard');

    Route::post('/attendance/checkin', [TeacherAttendanceController::class, 'checkin'])->name('teacher.attendance.checkin');
    Route::post('/attendance/checkout', [TeacherAttendanceController::class, 'checkout'])->name('teacher.attendance.checkout');
    Route::get('/attendance/history', [TeacherAttendanceController::class, 'history'])->name('teacher.attendance.history');

    Route::get('/class-attendance', [TeacherClassAttendanceController::class, 'index'])->name('teacher.class-attendance.index');
    Route::post('/class-attendance', [TeacherClassAttendanceController::class, 'store'])->name('teacher.class-attendance.store');

    Route::get('/teaching-journals', [TeacherTeachingJournalController::class, 'index'])->name('teacher.teaching-journals.index');
    Route::get('/teaching-journals/create/{session}', [TeacherTeachingJournalController::class, 'create'])->name('teacher.teaching-journals.create');
    Route::post('/teaching-journals', [TeacherTeachingJournalController::class, 'store'])->name('teacher.teaching-journals.store');
    Route::get('/teaching-journals/{journal}/edit', [TeacherTeachingJournalController::class, 'edit'])->name('teacher.teaching-journals.edit');
    Route::put('/teaching-journals/{journal}', [TeacherTeachingJournalController::class, 'update'])->name('teacher.teaching-journals.update');
    Route::get('/teaching-journals/history', [TeacherTeachingJournalController::class, 'history'])->name('teacher.teaching-journals.history');

    Route::get('/teacher/journals', [TeacherJournalController::class, 'index'])->name('teacher.journals.index');
    Route::get('/teacher/journals/{schedule}/create', [TeacherJournalController::class, 'create'])->name('teacher.journals.create');
    Route::post('/teacher/journals/{schedule}', [TeacherJournalController::class, 'store'])->name('teacher.journals.store');

    Route::get('/teacher/substitute-tasks', [TeacherSubstituteTaskController::class, 'index'])->name('teacher.substitute-tasks.index');

    Route::get('/teacher/modules', [TeacherTeachingModuleController::class, 'index'])->name('teacher.modules.index');
    Route::get('/teacher/modules/create', [TeacherTeachingModuleController::class, 'create'])->name('teacher.modules.create');
    Route::post('/teacher/modules', [TeacherTeachingModuleController::class, 'store'])->name('teacher.modules.store');
    Route::get('/teacher/modules/{module}/edit', [TeacherTeachingModuleController::class, 'edit'])->name('teacher.modules.edit');
    Route::put('/teacher/modules/{module}', [TeacherTeachingModuleController::class, 'update'])->name('teacher.modules.update');
    Route::post('/teacher/modules/{module}/resubmit', [TeacherTeachingModuleController::class, 'resubmit'])->name('teacher.modules.resubmit');
    Route::delete('/teacher/modules/{module}', [TeacherTeachingModuleController::class, 'destroy'])->name('teacher.modules.destroy');

    Route::get('/teacher/duty-dashboard', DutyDashboardController::class)->name('teacher.duty.dashboard');
    Route::get('/teacher/duty-reports/{report}/edit', [DutyReportController::class, 'edit'])->name('teacher.duty.report.edit');
    Route::patch('/teacher/duty-reports/{report}', [DutyReportController::class, 'update'])->name('teacher.duty.report.update');
    Route::get('/teacher/duty-reports/{report}/print', [DutyReportController::class, 'print'])->name('teacher.duty.report.print');

    Route::get('/late-entry-requests', [TeacherLateEntryRequestController::class, 'index'])->name('teacher.late-entry-requests.index');
    Route::get('/late-entry-requests/create', [TeacherLateEntryRequestController::class, 'create'])->name('teacher.late-entry-requests.create');
    Route::post('/late-entry-requests', [TeacherLateEntryRequestController::class, 'store'])->name('teacher.late-entry-requests.store');

    Route::get('/teacher-leave-requests', [TeacherLeaveRequestController::class, 'index'])->name('teacher.leave-requests.index');
    Route::get('/teacher-leave-requests/create', [TeacherLeaveRequestController::class, 'create'])->name('teacher.leave-requests.create');
    Route::post('/teacher-leave-requests', [TeacherLeaveRequestController::class, 'store'])->name('teacher.leave-requests.store');

    Route::get('/student-attendance-recap', TeacherStudentAttendanceRecapController::class)->name('teacher.student-attendance-recap');
});

