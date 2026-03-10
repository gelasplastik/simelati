<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Reporting\StudentAttendanceRecapService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shared\StudentAttendanceRecapFilterRequest;
use App\Models\SchoolClass;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentAttendanceRecapController extends Controller
{
    public function __invoke(StudentAttendanceRecapFilterRequest $request, StudentAttendanceRecapService $service)
    {
        $classes = SchoolClass::query()->where('is_active', true)->orderBy('name')->get();
        $selectedClassId = (int) ($request->input('class_id') ?: ($classes->first()?->id ?? 0));
        $month = (int) ($request->input('month') ?: now()->month);
        $year = (int) ($request->input('year') ?: now()->year);
        $recap = $selectedClassId > 0 ? $service->build($selectedClassId, $month, $year) : null;

        return view('admin.reports.student-attendance-recap', compact('classes', 'selectedClassId', 'month', 'year', 'recap'));
    }

    public function export(StudentAttendanceRecapFilterRequest $request, StudentAttendanceRecapService $service): StreamedResponse
    {
        $classId = (int) $request->input('class_id');
        $month = (int) ($request->input('month') ?: now()->month);
        $year = (int) ($request->input('year') ?: now()->year);
        $recap = $service->build($classId, $month, $year);
        $filename = "rekap-kehadiran-siswa-{$year}-{$month}-kelas-{$classId}.csv";

        return response()->streamDownload(function () use ($recap) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Siswa', 'Hadir', 'Izin', 'Sakit', 'Alpa', 'Persentase']);
            foreach ($recap['rows'] as $row) {
                fputcsv($handle, [
                    $row['student']->full_name,
                    $row['hadir'],
                    $row['izin'],
                    $row['sakit'],
                    $row['alpa'],
                    $row['percentage'].'%',
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
