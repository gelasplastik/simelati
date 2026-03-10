<?php

namespace App\Domain\Reporting;

use App\Models\Attendance;
use App\Models\ClassAttendanceSession;
use App\Models\LateEntryRequest;
use App\Models\Teacher;
use App\Models\TeachingJournal;

class TeacherDisciplineDashboardService
{
    public function buildTodaySummary(): array
    {
        $today = now()->toDateString();
        $teachers = Teacher::query()->with('user')->get();

        $checkedInTeacherIds = Attendance::query()->whereDate('date', $today)->pluck('teacher_id');
        $lateAttendances = Attendance::query()->with('teacher.user')->whereDate('date', $today)->where('is_late', true)->get();

        $sessionsToday = ClassAttendanceSession::query()->whereDate('date', $today)->get()->groupBy('teacher_id');
        $journalsTodayByTeacher = TeachingJournal::query()->whereDate('date', $today)->pluck('class_attendance_session_id')->all();

        $missingJournalTeachers = $teachers->filter(function ($teacher) use ($sessionsToday, $journalsTodayByTeacher) {
            $sessions = $sessionsToday->get($teacher->id, collect());
            if ($sessions->isEmpty()) {
                return false;
            }

            $allHaveJournal = $sessions->every(fn ($session) => in_array($session->id, $journalsTodayByTeacher, true));

            return ! $allHaveJournal;
        });

        $notCheckedInTeachers = $teachers->whereNotIn('id', $checkedInTeacherIds);
        $pendingRequests = LateEntryRequest::query()->where('status', 'pending')->count();

        return [
            'total_teachers' => $teachers->count(),
            'checked_in_count' => $checkedInTeacherIds->unique()->count(),
            'not_checked_in_count' => $notCheckedInTeachers->count(),
            'late_count' => $lateAttendances->count(),
            'missing_journal_count' => $missingJournalTeachers->count(),
            'pending_requests_count' => $pendingRequests,
            'not_checked_in_teachers' => $notCheckedInTeachers,
            'late_teachers' => $lateAttendances,
            'missing_journal_teachers' => $missingJournalTeachers,
            'latest_requests' => LateEntryRequest::query()->with(['teacher.user', 'class', 'subject'])->latest()->limit(10)->get(),
        ];
    }

    public function buildMonthlyRanking(): array
    {
        $start = now()->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();

        $teachers = Teacher::query()->with('user')->get();

        $rows = $teachers->map(function ($teacher) use ($start, $end) {
            $present = Attendance::query()->where('teacher_id', $teacher->id)->whereBetween('date', [$start, $end])->count();
            $onTime = Attendance::query()->where('teacher_id', $teacher->id)->whereBetween('date', [$start, $end])->where('is_late', false)->count();
            $sessionCount = ClassAttendanceSession::query()->where('teacher_id', $teacher->id)->whereBetween('date', [$start, $end])->count();
            $journalCount = TeachingJournal::query()->where('teacher_id', $teacher->id)->whereBetween('date', [$start, $end])->count();
            $overrideRequests = LateEntryRequest::query()->where('teacher_id', $teacher->id)->whereBetween('date', [$start, $end])->count();

            $attendanceScore = min(30, $present);
            $punctualityScore = $present > 0 ? round(($onTime / $present) * 20, 2) : 0;
            $classCompletionScore = min(20, $sessionCount);
            $journalCompletionScore = $sessionCount > 0 ? round(($journalCount / $sessionCount) * 20, 2) : 0;
            // Fewer override requests increases discipline score.
            $overrideScore = max(0, 10 - ($overrideRequests * 1));
            $disciplineScore = round($attendanceScore + $punctualityScore + $classCompletionScore + $journalCompletionScore + $overrideScore, 2);

            return [
                'teacher' => $teacher,
                'present' => $present,
                'on_time' => $onTime,
                'class_completion' => $sessionCount,
                'journal_completion' => $journalCount,
                'override_requests' => $overrideRequests,
                'discipline_score' => $disciplineScore,
            ];
        })->sortByDesc('discipline_score')->values()->all();

        return $rows;
    }
}
