<?php

namespace App\Http\Controllers\Teacher;

use App\Domain\MasterData\HomeroomDashboardService;
use App\Domain\MasterData\TeachingScheduleService;
use App\Domain\Reporting\KPIService;
use App\Domain\Reporting\TeacherJournalNotificationService;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\TeachingJournal;

class TeacherDashboardController extends Controller
{
    public function __invoke(
        KPIService $kpiService,
        HomeroomDashboardService $homeroomService,
        TeachingScheduleService $scheduleService,
        TeacherJournalNotificationService $notificationService
    )
    {
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

        return view('teacher.dashboard', compact(
            'todayAttendance',
            'journalCount',
            'kpi',
            'homeroom',
            'todaySchedules',
            'journalNotifications',
        ));
    }
}
