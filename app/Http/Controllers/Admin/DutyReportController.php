<?php

namespace App\Http\Controllers\Admin;

use App\Domain\DutyMonitoring\DutyMonitoringService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateDutyReportByAdminRequest;
use App\Models\DutyReport;
use Illuminate\Http\Request;

class DutyReportController extends Controller
{
    public function __construct(
        private readonly DutyMonitoringService $service,
    ) {
    }

    public function index(Request $request)
    {
        $query = DutyReport::query()->with(['dutyTeacher.user', 'finalizedBy']);

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->input('date_to'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $items = $query->orderByDesc('date')->paginate(25)->withQueryString();

        return view('admin.duty.reports.index', compact('items'));
    }

    public function show(DutyReport $report)
    {
        if ($this->shouldSyncSnapshot($report)) {
            $report = $this->service->syncReportSnapshot($report);
        } else {
            $report->load(['teacherRows.teacher.user', 'teacherRows.substituteTeacher.user', 'studentRows.class', 'dutyTeacher.user', 'finalizedBy']);
        }

        return view('admin.duty.reports.show', compact('report'));
    }

    public function update(UpdateDutyReportByAdminRequest $request, DutyReport $report)
    {
        $report = $this->service->saveDraft(
            $report,
            $request->validated('teacher_rows', []),
            $request->validated('student_rows', []),
            $request->input('notes'),
            false,
            $request->user()->id,
        );

        return redirect()->route('admin.duty-reports.show', $report)->with('success', 'Laporan piket berhasil diperbarui oleh admin.');
    }

    public function print(DutyReport $report)
    {
        if ($this->shouldSyncSnapshot($report)) {
            $report = $this->service->syncReportSnapshot($report);
        } else {
            $report->load(['teacherRows.teacher.user', 'teacherRows.substituteTeacher.user', 'studentRows.class', 'dutyTeacher.user', 'finalizedBy']);
        }

        return view('admin.duty.reports.print', compact('report'));
    }

    private function shouldSyncSnapshot(DutyReport $report): bool
    {
        if ($report->isFinalized()) {
            return false;
        }

        return $report->teacherRows()->count() === 0 || $report->studentRows()->count() === 0;
    }
}
