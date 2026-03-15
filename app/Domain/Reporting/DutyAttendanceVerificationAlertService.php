<?php

namespace App\Domain\Reporting;

use App\Models\DutyReportTeacherRow;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DutyAttendanceVerificationAlertService
{
    public function getOpenAlertsForTeacher(Teacher $teacher): Collection
    {
        $today = Carbon::now('Asia/Makassar')->toDateString();

        return DutyReportTeacherRow::query()
            ->select('duty_report_teacher_rows.*')
            ->join('duty_reports', 'duty_reports.id', '=', 'duty_report_teacher_rows.report_id')
            ->leftJoin('attendances', function ($join) {
                $join->on('attendances.teacher_id', '=', 'duty_report_teacher_rows.teacher_id')
                    ->on('attendances.date', '=', 'duty_reports.date');
            })
            ->where('duty_report_teacher_rows.teacher_id', $teacher->id)
            ->where('duty_report_teacher_rows.verified_status', 'present')
            ->whereDate('duty_reports.date', $today)
            ->whereNull('attendances.id')
            ->with(['report.dutyTeacher.user'])
            ->orderByDesc('duty_reports.date')
            ->get();
    }

    public function buildForTeacher(Teacher $teacher): array
    {
        $rows = $this->getOpenAlertsForTeacher($teacher);

        return [
            'count' => $rows->count(),
            'items' => $rows->map(function (DutyReportTeacherRow $row) {
                return [
                    'date' => optional($row->report?->date)->format('d-m-Y'),
                    'duty_teacher' => $row->report?->dutyTeacher?->user?->name,
                    'message' => 'Anda diverifikasi hadir oleh guru piket, tetapi absensi guru belum tercatat. Silakan absen agar status kehadiran valid.',
                ];
            })->values(),
        ];
    }

    public function countOpenAlertsForAdmin(): int
    {
        $today = Carbon::now('Asia/Makassar')->toDateString();

        return (int) DutyReportTeacherRow::query()
            ->join('duty_reports', 'duty_reports.id', '=', 'duty_report_teacher_rows.report_id')
            ->leftJoin('attendances', function ($join) {
                $join->on('attendances.teacher_id', '=', 'duty_report_teacher_rows.teacher_id')
                    ->on('attendances.date', '=', 'duty_reports.date');
            })
            ->where('duty_report_teacher_rows.verified_status', 'present')
            ->whereDate('duty_reports.date', $today)
            ->whereNull('attendances.id')
            ->count();
    }
}
