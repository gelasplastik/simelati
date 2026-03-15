<?php

namespace App\Http\Controllers\Teacher;

use App\Domain\DutyMonitoring\DutyMonitoringService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\UpdateDutyReportRequest;
use App\Models\DutyReport;
use InvalidArgumentException;

class DutyReportController extends Controller
{
    public function __construct(
        private readonly DutyMonitoringService $service,
    ) {
    }

    public function edit(DutyReport $report)
    {
        $teacher = auth()->user()->teacher;

        try {
            $this->service->ensureTeacherCanManageReport($teacher, $report);
        } catch (InvalidArgumentException $exception) {
            return redirect()->route('teacher.duty.dashboard')->with('error', $exception->getMessage());
        }

        if ($this->shouldSyncSnapshot($report)) {
            $report = $this->service->syncReportSnapshot($report);
        } else {
            $report->load(['teacherRows.teacher.user', 'teacherRows.substituteTeacher.user', 'studentRows.class', 'dutyTeacher.user']);
        }

        return view('teacher.duty.report', compact('report'));
    }

    public function update(UpdateDutyReportRequest $request, DutyReport $report)
    {
        $teacher = $request->user()->teacher;

        try {
            $this->service->ensureTeacherCanManageReport($teacher, $report);

            if ($report->isFinalized()) {
                throw new InvalidArgumentException('Laporan piket yang sudah final tidak dapat diubah.');
            }

            $finalize = $request->input('action') === 'finalize';
            $report = $this->service->saveDraft(
                $report,
                $request->validated('teacher_rows', []),
                $request->validated('student_rows', []),
                $request->input('notes'),
                $finalize,
                $request->user()->id,
            );

            return redirect()->route('teacher.duty.report.edit', $report)
                ->with('success', $finalize ? 'Laporan piket harian berhasil difinalisasi.' : 'Draft laporan piket berhasil disimpan.');
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage())->withInput();
        }
    }

    public function print(DutyReport $report)
    {
        $teacher = auth()->user()->teacher;

        try {
            $this->service->ensureTeacherCanManageReport($teacher, $report);
        } catch (InvalidArgumentException $exception) {
            return redirect()->route('teacher.duty.dashboard')->with('error', $exception->getMessage());
        }

        if ($this->shouldSyncSnapshot($report)) {
            $report = $this->service->syncReportSnapshot($report);
        } else {
            $report->load(['teacherRows.teacher.user', 'teacherRows.substituteTeacher.user', 'studentRows.class', 'dutyTeacher.user']);
        }

        return view('teacher.duty.print', compact('report'));
    }

    private function shouldSyncSnapshot(DutyReport $report): bool
    {
        if ($report->isFinalized()) {
            return false;
        }

        return $report->teacherRows()->count() === 0 || $report->studentRows()->count() === 0;
    }
}
