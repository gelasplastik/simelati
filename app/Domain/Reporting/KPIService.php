<?php

namespace App\Domain\Reporting;

use App\Models\Attendance;
use App\Models\TeachingJournal;
use App\Models\Teacher;
use Carbon\Carbon;

class KPIService
{
    public function calculateMonthlyKPI(int $month, int $year): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $workingDays = $this->countWorkingDays($start->copy(), $end->copy());
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

            $attendanceScore = $workingDays > 0 ? ($daysPresent / $workingDays) * 40 : 0;
            $journalScore = $daysPresent > 0 ? ($daysWithJournal / $daysPresent) * 40 : 0;
            $punctualityScore = $daysPresent > 0 ? ($onTimeDays / $daysPresent) * 20 : 0;

            $result[] = [
                'teacher' => $teacher,
                'days_present' => $daysPresent,
                'working_days' => $workingDays,
                'days_with_journal' => $daysWithJournal,
                'on_time_days' => $onTimeDays,
                'attendance_score' => round($attendanceScore, 2),
                'journal_score' => round($journalScore, 2),
                'punctuality_score' => round($punctualityScore, 2),
                'total_score' => round($attendanceScore + $journalScore + $punctualityScore, 2),
            ];
        }

        usort($result, fn ($a, $b) => $b['total_score'] <=> $a['total_score']);

        return $result;
    }

    private function countWorkingDays(Carbon $start, Carbon $end): int
    {
        $workingDays = 0;

        while ($start <= $end) {
            if (! $start->isWeekend()) {
                $workingDays++;
            }

            $start->addDay();
        }

        return $workingDays;
    }
}
