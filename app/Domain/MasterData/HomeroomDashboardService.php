<?php

namespace App\Domain\MasterData;

use App\Models\ClassAttendance;
use App\Models\SchoolClass;
use App\Models\StudentPermission;
use App\Models\Teacher;

class HomeroomDashboardService
{
    public function getSummary(Teacher $teacher): ?array
    {
        if (! $teacher->homeroom_class_id) {
            return null;
        }

        $class = SchoolClass::query()
            ->with('students')
            ->where('id', $teacher->homeroom_class_id)
            ->where('is_active', true)
            ->first();

        if (! $class) {
            return null;
        }

        $activeStudents = $class->students()->where('is_active', true)->get();

        $todayRows = ClassAttendance::query()
            ->whereHas('session', function ($query) use ($class) {
                $query->where('class_id', $class->id)->whereDate('date', now()->toDateString());
            })
            ->with('student')
            ->latest('id')
            ->get()
            ->unique('student_id');

        $hadir = $todayRows->where('status', 'hadir')->count();
        $izin = $todayRows->where('status', 'izin')->count();
        $sakit = $todayRows->where('status', 'sakit')->count();
        $alpa = $todayRows->where('status', 'alpa')->count();

        $absentStudents = $todayRows->where('status', '!=', 'hadir')->pluck('student.full_name')->filter()->values();

        $start = now()->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();

        $monthlyRows = ClassAttendance::query()
            ->whereHas('session', function ($query) use ($class, $start, $end) {
                $query->where('class_id', $class->id)->whereBetween('date', [$start, $end]);
            })
            ->latest('id')
            ->get();

        $monthlyHadir = $monthlyRows->where('status', 'hadir')->count();
        $monthlyIzin = $monthlyRows->where('status', 'izin')->count();
        $monthlySakit = $monthlyRows->where('status', 'sakit')->count();
        $monthlyAlpa = $monthlyRows->where('status', 'alpa')->count();
        $monthlyTotal = $monthlyRows->count();
        $monthlyPercentage = $monthlyTotal > 0 ? round(($monthlyHadir / $monthlyTotal) * 100, 2) : 0;

        $topAbsentStudents = ClassAttendance::query()
            ->whereHas('session', function ($query) use ($class, $start, $end) {
                $query->where('class_id', $class->id)->whereBetween('date', [$start, $end]);
            })
            ->whereIn('status', ['izin', 'sakit', 'alpa'])
            ->with('student')
            ->get()
            ->groupBy('student_id')
            ->map(function ($rows) {
                return [
                    'student_name' => $rows->first()?->student?->full_name ?? '-',
                    'izin_count' => $rows->where('status', 'izin')->count(),
                    'sakit_count' => $rows->where('status', 'sakit')->count(),
                    'alpa_count' => $rows->where('status', 'alpa')->count(),
                    'total_absence' => $rows->count(),
                ];
            })
            ->sortByDesc('total_absence')
            ->take(5)
            ->values();

        $recentPermissions = StudentPermission::query()
            ->with('student')
            ->whereHas('student', function ($query) use ($class) {
                $query->where('class_id', $class->id);
            })
            ->latest('id')
            ->limit(10)
            ->get();

        return [
            'class' => $class,
            'active_students_count' => $activeStudents->count(),
            'today_hadir' => $hadir,
            'today_izin' => $izin,
            'today_sakit' => $sakit,
            'today_alpa' => $alpa,
            'absent_students' => $absentStudents,
            'monthly_percentage' => $monthlyPercentage,
            'monthly_hadir' => $monthlyHadir,
            'monthly_izin' => $monthlyIzin,
            'monthly_sakit' => $monthlySakit,
            'monthly_alpa' => $monthlyAlpa,
            'top_absent_students' => $topAbsentStudents,
            'recent_permissions' => $recentPermissions,
        ];
    }
}
