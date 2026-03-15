<?php

namespace App\Domain\Reporting;

use App\Domain\MasterData\AcademicCalendarService;
use App\Models\Attendance;
use App\Models\DutyReportTeacherRow;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use Carbon\Carbon;

class KPIService
{
    public function __construct(
        private readonly TeachingModuleProgressService $moduleProgressService,
        private readonly AcademicCalendarService $calendarService,
    ) {
    }

    public function calculateMonthlyKPI(int $month, int $year): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $workingDays = $this->countWorkingDays($start->copy(), $end->copy());
        $ignoredPenaltyDates = $this->calendarService->getDateRangeWhereKpiPenaltyIgnored($start->copy(), $end->copy());
        $teachers = Teacher::query()->with('user')->get();

        $result = [];
        foreach ($teachers as $teacher) {
            $daysPresent = Attendance::query()
                ->where('teacher_id', $teacher->id)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->count();

            $daysWithJournal = TeachingJournal::query()
                ->whereHas('classAttendanceSession', function ($query) {
                    $query->where(function ($q) {
                        $q->whereDate('class_attendance_sessions.date', now()->toDateString())
                            ->orWhere(function ($inner) {
                                $inner->where('class_attendance_sessions.override_allowed', true)
                                    ->whereNotNull('class_attendance_sessions.override_expires_at');
                            });
                    });
                })
                ->where('teacher_id', $teacher->id)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->distinct('date')
                ->count('date');

            $onTimeDays = Attendance::query()
                ->where('teacher_id', $teacher->id)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->where('is_late', false)
                ->count();

            $dutyVerificationGapDays = DutyReportTeacherRow::query()
                ->select('duty_reports.date')
                ->join('duty_reports', 'duty_reports.id', '=', 'duty_report_teacher_rows.report_id')
                ->leftJoin('attendances', function ($join) {
                    $join->on('attendances.teacher_id', '=', 'duty_report_teacher_rows.teacher_id')
                        ->on('attendances.date', '=', 'duty_reports.date');
                })
                ->where('duty_report_teacher_rows.teacher_id', $teacher->id)
                ->where('duty_report_teacher_rows.verified_status', 'present')
                ->whereBetween('duty_reports.date', [$start->toDateString(), $end->toDateString()])
                ->when($ignoredPenaltyDates->isNotEmpty(), fn ($q) => $q->whereNotIn('duty_reports.date', $ignoredPenaltyDates->all()))
                ->whereNull('attendances.id')
                ->distinct('duty_reports.date')
                ->count('duty_reports.date');

            $attendanceScore = $workingDays > 0 ? ($daysPresent / $workingDays) * 40 : 0;
            $journalScore = $daysPresent > 0 ? ($daysWithJournal / $daysPresent) * 40 : 0;
            $punctualityScore = $daysPresent > 0 ? ($onTimeDays / $daysPresent) * 20 : 0;

            $moduleProgress = $this->moduleProgressService->summaryForTeacher($teacher);

            $result[] = [
                'teacher' => $teacher,
                'days_present' => $daysPresent,
                'working_days' => $workingDays,
                'days_with_journal' => $daysWithJournal,
                'on_time_days' => $onTimeDays,
                'duty_verification_gap_days' => $dutyVerificationGapDays,
                'attendance_score' => round($attendanceScore, 2),
                'journal_score' => round($journalScore, 2),
                'punctuality_score' => round($punctualityScore, 2),
                'total_score' => round($attendanceScore + $journalScore + $punctualityScore, 2),
                'module_total_assignments' => $moduleProgress['total_assigned'],
                'module_completion_count' => $moduleProgress['completion_count'],
                'module_approved_count' => $moduleProgress['approved_count'],
                'module_completion_percentage' => $moduleProgress['completion_percentage'],
            ];
        }

        usort($result, fn ($a, $b) => $b['total_score'] <=> $a['total_score']);

        return $result;
    }

    private function countWorkingDays(Carbon $start, Carbon $end): int
    {
        $workingDays = 0;

        while ($start <= $end) {
            if (! $start->isWeekend()
                && $this->calendarService->isSchoolDay($start->toDateString())
                && ! $this->calendarService->shouldIgnoreKpiPenalty($start->toDateString())
                && $this->calendarService->isTeacherAttendanceEnabled($start->toDateString())) {
                $workingDays++;
            }

            $start->addDay();
        }

        return $workingDays;
    }
}
