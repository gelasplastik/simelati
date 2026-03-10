<?php

namespace App\Domain\Reporting;

use App\Models\ClassAttendance;
use App\Models\SchoolClass;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StudentAttendanceRecapService
{
    public function build(int $classId, int $month, int $year): array
    {
        $class = SchoolClass::query()->with(['students' => function ($query) {
            $query->where('is_active', true)->orderBy('full_name');
        }])->findOrFail($classId);

        $start = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $end = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $rows = ClassAttendance::query()
            ->with('student:id,full_name')
            ->whereHas('session', function ($query) use ($classId, $start, $end) {
                $query->where('class_id', $classId)->whereBetween('date', [$start, $end]);
            })
            ->get()
            ->groupBy('student_id');

        $recap = $class->students->map(function ($student) use ($rows) {
            $studentRows = $rows->get($student->id, collect());
            $hadir = $studentRows->where('status', 'hadir')->count();
            $izin = $studentRows->where('status', 'izin')->count();
            $sakit = $studentRows->where('status', 'sakit')->count();
            $alpa = $studentRows->where('status', 'alpa')->count();
            $total = $studentRows->count();
            $percentage = $total > 0 ? round(($hadir / $total) * 100, 2) : 0;

            return [
                'student' => $student,
                'hadir' => $hadir,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpa' => $alpa,
                'total' => $total,
                'percentage' => $percentage,
            ];
        });

        return [
            'class' => $class,
            'month' => $month,
            'year' => $year,
            'rows' => $recap,
            'summary' => $this->summary($recap),
        ];
    }

    private function summary(Collection $rows): array
    {
        $totalStudents = $rows->count();
        $avgPercentage = $totalStudents > 0 ? round($rows->avg('percentage'), 2) : 0;
        $mostAbsentCount = $rows->filter(fn ($row) => (($row['izin'] + $row['sakit'] + $row['alpa']) > 0))->count();

        return [
            'total_students' => $totalStudents,
            'avg_percentage' => $avgPercentage,
            'most_absent_students_count' => $mostAbsentCount,
        ];
    }
}
