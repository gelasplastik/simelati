<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Reporting\SchoolAnalyticsDashboardService;
use App\Domain\Reporting\TeacherDisciplineDashboardService;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function __invoke(
        TeacherDisciplineDashboardService $disciplineService,
        SchoolAnalyticsDashboardService $analyticsService
    )
    {
        $period = request('period', 'today');

        $disciplineToday = $disciplineService->buildTodaySummary();
        $disciplineRanking = $disciplineService->buildMonthlyRanking();
        $analytics = $analyticsService->build($period);

        return view('admin.dashboard', compact(
            'disciplineToday',
            'disciplineRanking',
            'analytics',
            'period',
        ));
    }
}
