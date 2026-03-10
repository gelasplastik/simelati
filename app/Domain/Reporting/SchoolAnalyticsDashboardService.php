<?php

namespace App\Domain\Reporting;

use App\Models\Attendance;
use App\Models\ClassAttendanceSession;
use App\Models\LateEntryRequest;
use App\Models\Student;
use App\Models\StudentPermission;
use App\Models\TeacherLeaveRequest;
use App\Models\TeachingJournal;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class SchoolAnalyticsDashboardService
{
    public function build(string $period = 'today'): array
    {
        [$start, $end] = $this->resolveRange($period);

        return [
            'summary' => $this->buildSummary(),
            'teacher_chart' => $this->buildTeacherAttendanceChart($start, $end),
            'student_chart' => $this->buildStudentAttendanceChart($start, $end),
            'classes_attention' => $this->buildClassAttention($start, $end),
            'students_high_absence' => $this->buildStudentsHighAbsence(),
            'activity_feed' => $this->buildActivityFeed(),
            'period' => $period,
            'range_label' => $this->buildRangeLabel($start, $end),
        ];
    }

    private function resolveRange(string $period): array
    {
        $today = now();

        return match ($period) {
            'week' => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()],
            'month' => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
            default => [$today->copy()->startOfDay(), $today->copy()->endOfDay()],
        };
    }

    private function buildRangeLabel(Carbon $start, Carbon $end): string
    {
        return $start->format('d M Y').' - '.$end->format('d M Y');
    }

    private function buildSummary(): array
    {
        $today = now()->toDateString();

        $teacherPresent = Attendance::query()
            ->whereDate('date', $today)
            ->distinct('teacher_id')
            ->count('teacher_id');

        $teacherLeave = TeacherLeaveRequest::query()
            ->where('status', 'approved')
            ->whereDate('date_from', '<=', $today)
            ->whereDate('date_to', '>=', $today)
            ->distinct('teacher_id')
            ->count('teacher_id');

        $studentRows = DB::table('class_attendances as ca')
            ->join('class_attendance_sessions as s', 's.id', '=', 'ca.session_id')
            ->whereDate('s.date', $today)
            ->select('ca.student_id', 'ca.status')
            ->get();

        $perStudent = $studentRows->groupBy('student_id')->map(function ($rows) {
            $hadirCount = $rows->where('status', 'hadir')->count();
            $absentCount = $rows->whereIn('status', ['izin', 'sakit', 'alpa'])->count();

            return [
                'hadir_count' => $hadirCount,
                'absent_count' => $absentCount,
            ];
        });

        $studentPresent = $perStudent->filter(fn ($row) => $row['hadir_count'] > 0)->count();
        $studentAbsent = $perStudent->filter(fn ($row) => $row['hadir_count'] === 0 && $row['absent_count'] > 0)->count();

        $journalMissing = ClassAttendanceSession::query()
            ->whereDate('date', $today)
            ->doesntHave('teachingJournal')
            ->count();

        $pendingRequests = LateEntryRequest::query()->where('status', 'pending')->count()
            + TeacherLeaveRequest::query()->where('status', 'submitted')->count()
            + StudentPermission::query()->where('status', 'submitted')->count();

        return [
            'teacher_present_today' => $teacherPresent,
            'teacher_leave_today' => $teacherLeave,
            'student_present_today' => $studentPresent,
            'student_absent_today' => $studentAbsent,
            'journal_missing_today' => $journalMissing,
            'pending_requests' => $pendingRequests,
        ];
    }

    private function buildTeacherAttendanceChart(Carbon $start, Carbon $end): array
    {
        $labels = [];
        $hadir = [];
        $izin = [];
        $telat = [];

        $attendanceRows = Attendance::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy(fn ($row) => Carbon::parse($row->date)->toDateString());

        $leaveRows = TeacherLeaveRequest::query()
            ->where('status', 'approved')
            ->whereDate('date_to', '>=', $start->toDateString())
            ->whereDate('date_from', '<=', $end->toDateString())
            ->get();

        foreach (CarbonPeriod::create($start->toDateString(), $end->toDateString()) as $date) {
            $key = $date->toDateString();
            $labels[] = $date->format('d/m');
            $dayAttendances = $attendanceRows->get($key, collect());

            $hadir[] = $dayAttendances->count();
            $telat[] = $dayAttendances->where('is_late', true)->count();
            $izin[] = $leaveRows
                ->filter(fn ($leave) => $leave->date_from->toDateString() <= $key && $leave->date_to->toDateString() >= $key)
                ->pluck('teacher_id')
                ->unique()
                ->count();
        }

        return compact('labels', 'hadir', 'izin', 'telat');
    }

    private function buildStudentAttendanceChart(Carbon $start, Carbon $end): array
    {
        $labels = [];
        $hadir = [];
        $izin = [];
        $sakit = [];
        $alpa = [];

        $rows = DB::table('class_attendances as ca')
            ->join('class_attendance_sessions as s', 's.id', '=', 'ca.session_id')
            ->whereBetween('s.date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw("DATE(s.date) as date, ca.status, COUNT(*) as total")
            ->groupByRaw('DATE(s.date), ca.status')
            ->get()
            ->groupBy('date');

        foreach (CarbonPeriod::create($start->toDateString(), $end->toDateString()) as $date) {
            $key = $date->toDateString();
            $labels[] = $date->format('d/m');
            $dayRows = collect($rows->get($key, []));
            $hadir[] = (int) ($dayRows->firstWhere('status', 'hadir')->total ?? 0);
            $izin[] = (int) ($dayRows->firstWhere('status', 'izin')->total ?? 0);
            $sakit[] = (int) ($dayRows->firstWhere('status', 'sakit')->total ?? 0);
            $alpa[] = (int) ($dayRows->firstWhere('status', 'alpa')->total ?? 0);
        }

        return compact('labels', 'hadir', 'izin', 'sakit', 'alpa');
    }

    private function buildClassAttention(Carbon $start, Carbon $end)
    {
        $rows = DB::table('class_attendances as ca')
            ->join('class_attendance_sessions as s', 's.id', '=', 'ca.session_id')
            ->join('classes as c', 'c.id', '=', 's.class_id')
            ->whereBetween('s.date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('c.id as class_id, c.name as class_name')
            ->selectRaw("SUM(CASE WHEN ca.status='hadir' THEN 1 ELSE 0 END) as hadir")
            ->selectRaw("SUM(CASE WHEN ca.status='izin' THEN 1 ELSE 0 END) as izin")
            ->selectRaw("SUM(CASE WHEN ca.status='sakit' THEN 1 ELSE 0 END) as sakit")
            ->selectRaw("SUM(CASE WHEN ca.status='alpa' THEN 1 ELSE 0 END) as alpa")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('c.id', 'c.name')
            ->get()
            ->map(function ($row) {
                $row->attendance_pct = $row->total > 0 ? round(($row->hadir / $row->total) * 100, 2) : 0;
                return $row;
            })
            ->sortBy([
                ['attendance_pct', 'asc'],
                ['alpa', 'desc'],
                ['izin', 'desc'],
            ])
            ->values();

        $lowestClassId = $rows->first()->class_id ?? null;
        $maxAlpa = $rows->max('alpa');
        $maxIzin = $rows->max('izin');

        return $rows->take(8)->map(function ($row) use ($lowestClassId, $maxAlpa, $maxIzin) {
            $notes = [];
            if ($row->class_id === $lowestClassId) {
                $notes[] = 'Persentase hadir terendah';
            }
            if ((int) $row->alpa === (int) $maxAlpa && $maxAlpa > 0) {
                $notes[] = 'Alpa tertinggi';
            }
            if ((int) $row->izin === (int) $maxIzin && $maxIzin > 0) {
                $notes[] = 'Izin tertinggi';
            }
            $row->attention_notes = $notes;

            return $row;
        });
    }

    private function buildStudentsHighAbsence()
    {
        $start = now()->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();

        return DB::table('class_attendances as ca')
            ->join('class_attendance_sessions as s', 's.id', '=', 'ca.session_id')
            ->join('students as st', 'st.id', '=', 'ca.student_id')
            ->leftJoin('classes as c', 'c.id', '=', 'st.class_id')
            ->whereBetween('s.date', [$start, $end])
            ->whereIn('ca.status', ['izin', 'sakit', 'alpa'])
            ->selectRaw('st.id as student_id, st.full_name, st.nisn, c.name as class_name')
            ->selectRaw("SUM(CASE WHEN ca.status='izin' THEN 1 ELSE 0 END) as izin")
            ->selectRaw("SUM(CASE WHEN ca.status='sakit' THEN 1 ELSE 0 END) as sakit")
            ->selectRaw("SUM(CASE WHEN ca.status='alpa' THEN 1 ELSE 0 END) as alpa")
            ->selectRaw('COUNT(*) as total_absence')
            ->groupBy('st.id', 'st.full_name', 'st.nisn', 'c.name')
            ->orderByDesc('total_absence')
            ->limit(10)
            ->get();
    }

    private function buildActivityFeed()
    {
        $items = collect();

        $studentPermissions = StudentPermission::query()
            ->with(['student.class'])
            ->latest()
            ->limit(6)
            ->get()
            ->map(function ($row) {
                $name = $row->submitter_name ?: 'Pengaju';
                return [
                    'time' => $row->created_at,
                    'title' => 'Izin Siswa',
                    'text' => $name.' mengajukan izin untuk '.$row->student->full_name.' ('.$row->student->class?->name.')',
                    'status' => $row->status,
                ];
            });

        $teacherLeaves = TeacherLeaveRequest::query()
            ->with('teacher.user')
            ->latest()
            ->limit(6)
            ->get()
            ->map(function ($row) {
                return [
                    'time' => $row->created_at,
                    'title' => 'Izin Guru',
                    'text' => $row->teacher->user->name.' mengajukan izin guru',
                    'status' => $row->status,
                ];
            });

        $journals = TeachingJournal::query()
            ->with(['teacher.user', 'class', 'subject'])
            ->latest()
            ->limit(6)
            ->get()
            ->map(function ($row) {
                return [
                    'time' => $row->created_at,
                    'title' => 'Jurnal Mengajar',
                    'text' => $row->teacher->user->name.' mengisi jurnal '.$row->subject->name.' kelas '.$row->class->name,
                    'status' => 'approved',
                ];
            });

        $lateRequests = LateEntryRequest::query()
            ->with(['teacher.user', 'class', 'subject'])
            ->latest()
            ->limit(6)
            ->get()
            ->map(function ($row) {
                return [
                    'time' => $row->created_at,
                    'title' => 'Permintaan Susulan',
                    'text' => $row->teacher->user->name.' meminta izin pengisian susulan '.$row->subject->name.' kelas '.$row->class->name,
                    'status' => $row->status,
                ];
            });

        $items = $items
            ->concat($studentPermissions)
            ->concat($teacherLeaves)
            ->concat($journals)
            ->concat($lateRequests)
            ->sortByDesc('time')
            ->take(20)
            ->values();

        return $items;
    }
}
