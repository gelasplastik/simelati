<?php

namespace App\Http\Controllers\Admin;

use App\Domain\MasterData\DashboardCalendarService;
use App\Domain\Reporting\SchoolAnalyticsDashboardService;
use App\Domain\Reporting\TeacherDisciplineDashboardService;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function __invoke(
        TeacherDisciplineDashboardService $disciplineService,
        SchoolAnalyticsDashboardService $analyticsService,
        DashboardCalendarService $dashboardCalendarService,
    ) {
        $period = request('period', 'today');

        $disciplineToday = $disciplineService->buildTodaySummary();
        $disciplineRanking = $disciplineService->buildMonthlyRanking();
        $analytics = $analyticsService->build($period);
        $calendarWidget = $dashboardCalendarService->buildMonthly(
            null,
            request()->integer('calendar_year') ?: now()->year,
            request()->integer('calendar_month') ?: now()->month,
        );

        return view('admin.dashboard', compact(
            'disciplineToday',
            'disciplineRanking',
            'analytics',
            'period',
            'calendarWidget',
        ));
    }
}
