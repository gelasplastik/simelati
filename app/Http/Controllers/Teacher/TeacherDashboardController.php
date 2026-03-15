<?php

namespace App\Http\Controllers\Teacher;

use App\Domain\MasterData\DashboardCalendarService;
use App\Domain\MasterData\HomeroomDashboardService;
use App\Domain\MasterData\TeachingScheduleService;
use App\Domain\Reporting\DutyAttendanceVerificationAlertService;
use App\Domain\Reporting\KPIService;
use App\Domain\Reporting\TeacherJournalNotificationService;
use App\Domain\Reporting\TeachingModuleProgressService;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\TeachingJournal;

class TeacherDashboardController extends Controller
{
    public function __invoke(
        KPIService $kpiService,
        HomeroomDashboardService $homeroomService,
        TeachingScheduleService $scheduleService,
        TeacherJournalNotificationService $notificationService,
        TeachingModuleProgressService $moduleProgressService,
        DutyAttendanceVerificationAlertService $dutyAttendanceAlertService,
        DashboardCalendarService $dashboardCalendarService,
    ) {
        $teacher = auth()->user()->teacher;
        $todayAttendance = Attendance::query()
            ->where('teacher_id', $teacher->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        $journalCount = TeachingJournal::query()
            ->where('teacher_id', $teacher->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->count();

        $kpis = $kpiService->calculateMonthlyKPI(now()->month, now()->year);
        $kpi = collect($kpis)->firstWhere('teacher.id', $teacher->id);
        $homeroom = $homeroomService->getSummary($teacher);
        $todaySchedules = $scheduleService->getTodaySchedules($teacher);
        $journalNotifications = $notificationService->buildToday($teacher);
        $moduleProgress = $moduleProgressService->summaryForTeacher($teacher);
        $dutyAttendanceAlerts = $dutyAttendanceAlertService->buildForTeacher($teacher);
        $calendarWidget = $dashboardCalendarService->buildMonthly(
            $teacher,
            request()->integer('calendar_year') ?: now()->year,
            request()->integer('calendar_month') ?: now()->month,
        );

        return view('teacher.dashboard', compact(
            'todayAttendance',
            'journalCount',
            'kpi',
            'homeroom',
            'todaySchedules',
            'journalNotifications',
            'moduleProgress',
            'dutyAttendanceAlerts',
            'calendarWidget',
        ));
    }
}
