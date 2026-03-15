<?php

namespace App\Http\Controllers\Teacher;

use App\Domain\DutyMonitoring\DutyMonitoringService;
use App\Http\Controllers\Controller;
use App\Models\DutyReport;
use Carbon\Carbon;

class DutyDashboardController extends Controller
{
    public function __construct(
        private readonly DutyMonitoringService $service,
    ) {
    }

    public function __invoke()
    {
        $teacher = auth()->user()->teacher;
        $today = Carbon::now('Asia/Makassar')->toDateString();
        $isAssigned = $this->service->isAssignedTeacher($teacher, $today);
        $assignedTeachers = $this->service->getAssignedTeachersForDate($today);
        $dashboard = $isAssigned ? $this->service->buildDashboard($today) : null;
        $report = null;

        if ($isAssigned) {
            $report = $this->service->getOrCreateReport($today, $teacher, auth()->id());
            if ($this->shouldSyncSnapshot($report)) {
                $this->service->syncReportSnapshot($report);
            }
            $report->refresh();
        }

        return view('teacher.duty.dashboard', compact('isAssigned', 'assignedTeachers', 'dashboard', 'report'));
    }

    private function shouldSyncSnapshot(DutyReport $report): bool
    {
        if ($report->isFinalized()) {
            return false;
        }

        return $report->teacherRows()->count() === 0 || $report->studentRows()->count() === 0;
    }
}
