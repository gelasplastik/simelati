<?php

use App\Http\Controllers\Admin\AdminLateEntryRequestController;
use App\Http\Controllers\Admin\AssignmentController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\AdminStudentImportController;
use App\Http\Controllers\Admin\AcademicCalendarController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DutyReportController;
use App\Http\Controllers\Admin\DutyTeacherAssignmentController;
use App\Http\Controllers\Admin\ParentController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SessionOverrideController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentAttendanceRecapController;
use App\Http\Controllers\Admin\SystemUpdateController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\TeacherLeaveRequestController;
use App\Http\Controllers\Admin\TeachingModuleController;
use App\Http\Controllers\Admin\TeachingScheduleController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/notifications/mark-all-read', [AdminNotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');


    Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
    Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
    Route::put('/teachers/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');

    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::get('/students/import', [AdminStudentImportController::class, 'index'])->name('students.import.index');
    Route::post('/students/import', [AdminStudentImportController::class, 'store'])->name('students.import.store');
    Route::get('/students/import/template', [AdminStudentImportController::class, 'template'])->name('students.import.template');

    Route::get('/parents', [ParentController::class, 'index'])->name('parents.index');

    Route::get('/classes', [ClassController::class, 'index'])->name('classes.index');
    Route::post('/classes', [ClassController::class, 'store'])->name('classes.store');
    Route::put('/classes/{class}', [ClassController::class, 'update'])->name('classes.update');

    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');

    Route::get('/teaching-schedules', [TeachingScheduleController::class, 'index'])->name('teaching-schedules.index');
    Route::get('/teaching-schedules/profiles', [TeachingScheduleController::class, 'profiles'])->name('teaching-schedules.profiles');
    Route::patch('/teaching-schedules/profiles/{profile}/activate', [TeachingScheduleController::class, 'activateProfile'])->name('teaching-schedules.profiles.activate');
    Route::get('/teaching-schedules/export', [TeachingScheduleController::class, 'exportCsv'])->name('teaching-schedules.export');
    Route::post('/teaching-schedules', [TeachingScheduleController::class, 'store'])->name('teaching-schedules.store');
    Route::put('/teaching-schedules/{schedule}', [TeachingScheduleController::class, 'update'])->name('teaching-schedules.update');

    Route::get('/teaching-modules', [TeachingModuleController::class, 'index'])->name('teaching-modules.index');
    Route::patch('/teaching-modules/{module}/review', [TeachingModuleController::class, 'review'])->name('teaching-modules.review');

    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::put('/assignments/{assignment}', [AssignmentController::class, 'update'])->name('assignments.update');
    Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');

    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::get('/calendar', [AcademicCalendarController::class, 'index'])->name('calendar.index');
    Route::post('/calendar', [AcademicCalendarController::class, 'store'])->name('calendar.store');
    Route::put('/calendar/{event}', [AcademicCalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/{event}', [AcademicCalendarController::class, 'destroy'])->name('calendar.destroy');
    Route::post('/calendar/sync-national', [AcademicCalendarController::class, 'syncNational'])->name('calendar.sync-national');

    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

    Route::middleware('superadmin')->prefix('/system/update')->name('system.update.')->group(function () {
        Route::get('/', [SystemUpdateController::class, 'index'])->name('index');
        Route::post('/run', [SystemUpdateController::class, 'run'])->name('run');
    });

    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('/permissions/{permission}/approve', [PermissionController::class, 'approve'])->name('permissions.approve');
    Route::post('/permissions/{permission}/reject', [PermissionController::class, 'reject'])->name('permissions.reject');

    Route::get('/overrides', [SessionOverrideController::class, 'index'])->name('overrides.index');
    Route::put('/overrides/{session}', [SessionOverrideController::class, 'update'])->name('overrides.update');

    Route::get('/late-entry-requests', [AdminLateEntryRequestController::class, 'index'])->name('late-entry-requests.index');
    Route::patch('/late-entry-requests/{lateEntryRequest}/approve', [AdminLateEntryRequestController::class, 'approve'])->name('late-entry-requests.approve');
    Route::patch('/late-entry-requests/{lateEntryRequest}/reject', [AdminLateEntryRequestController::class, 'reject'])->name('late-entry-requests.reject');
    Route::get('/late-entry-requests/export', [AdminLateEntryRequestController::class, 'exportCsv'])->name('late-entry-requests.export');

    Route::get('/teacher-leave-requests', [TeacherLeaveRequestController::class, 'index'])->name('teacher-leave-requests.index');
    Route::patch('/teacher-leave-requests/{teacherLeaveRequest}/review', [TeacherLeaveRequestController::class, 'review'])->name('teacher-leave-requests.review');
    Route::patch('/teacher-leave-requests/substitutes/{assignment}/assign', [TeacherLeaveRequestController::class, 'assignSubstitute'])->name('teacher-leave-requests.substitutes.assign');

    Route::get('/duty-teacher-assignments', [DutyTeacherAssignmentController::class, 'index'])->name('duty-assignments.index');
    Route::post('/duty-teacher-assignments', [DutyTeacherAssignmentController::class, 'store'])->name('duty-assignments.store');
    Route::delete('/duty-teacher-assignments/{assignment}', [DutyTeacherAssignmentController::class, 'destroy'])->name('duty-assignments.destroy');

    Route::get('/duty-reports', [DutyReportController::class, 'index'])->name('duty-reports.index');
    Route::get('/duty-reports/{report}', [DutyReportController::class, 'show'])->name('duty-reports.show');
    Route::patch('/duty-reports/{report}', [DutyReportController::class, 'update'])->name('duty-reports.update');
    Route::get('/duty-reports/{report}/print', [DutyReportController::class, 'print'])->name('duty-reports.print');

    Route::get('/reports/attendance', [ReportController::class, 'attendance'])->name('reports.attendance');
    Route::get('/reports/journals', [ReportController::class, 'journals'])->name('reports.journals');
    Route::get('/reports/journals/export', [ReportController::class, 'exportJournalsCsv'])->name('reports.journals.export');
    Route::get('/reports/class-attendance', [ReportController::class, 'classAttendance'])->name('reports.class-attendance');
    Route::get('/reports/kpi', [ReportController::class, 'kpi'])->name('reports.kpi');
    Route::get('/reports/kpi/export', [ReportController::class, 'exportKpiCsv'])->name('reports.kpi.export');
    Route::get('/reports/student-permissions', [ReportController::class, 'permissions'])->name('reports.permissions');
    Route::get('/reports/student-attendance-recap', StudentAttendanceRecapController::class)->name('reports.student-attendance-recap');
    Route::get('/reports/student-attendance-recap/export', [StudentAttendanceRecapController::class, 'export'])->name('reports.student-attendance-recap.export');
});



