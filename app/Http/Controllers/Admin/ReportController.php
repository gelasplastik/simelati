<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Reporting\KPIService;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassAttendanceSession;
use App\Models\SchoolClass;
use App\Models\StudentPermission;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function attendance()
    {
        $items = Attendance::query()->with('teacher.user')->latest('date')->paginate(30);

        return view('admin.reports.attendance', compact('items'));
    }

    public function journals()
    {
        $teacherId = request('teacher_id');
        $classId = request('class_id');
        $subjectId = request('subject_id');
        $dateFrom = request('date_from');
        $dateTo = request('date_to');
        $chartYear = (int) request('year', now()->year);

        $baseQuery = TeachingJournal::query()->with(['teacher.user', 'class', 'subject']);

        if ($teacherId) {
            $baseQuery->where('teacher_id', $teacherId);
        }

        if ($classId) {
            $baseQuery->where('class_id', $classId);
        }

        if ($subjectId) {
            $baseQuery->where('subject_id', $subjectId);
        }

        if ($dateFrom) {
            $baseQuery->whereDate('date', '>=', Carbon::parse($dateFrom)->toDateString());
        }

        if ($dateTo) {
            $baseQuery->whereDate('date', '<=', Carbon::parse($dateTo)->toDateString());
        }

        $query = clone $baseQuery;
        $items = $query->latest('date')->paginate(30)->withQueryString();
        $teachers = Teacher::query()->with('user')->get();
        $classes = SchoolClass::query()->orderBy('name')->get();
        $subjects = Subject::query()->orderBy('name')->get();

        $summaryQuery = clone $baseQuery;
        $totalJournals = (clone $summaryQuery)->count();
        $activeTeachers = (clone $summaryQuery)->distinct('teacher_id')->count('teacher_id');
        $activeClasses = (clone $summaryQuery)->distinct('class_id')->count('class_id');
        $avgPerDay = 0;
        if ($totalJournals > 0) {
            $minDate = (clone $summaryQuery)->min('date');
            $maxDate = (clone $summaryQuery)->max('date');
            $days = Carbon::parse($minDate)->diffInDays(Carbon::parse($maxDate)) + 1;
            $avgPerDay = round($totalJournals / max($days, 1), 2);
        }

        $monthlyRows = (clone $baseQuery)
            ->whereYear('date', $chartYear)
            ->selectRaw('MONTH(date) as month_no, COUNT(*) as total')
            ->groupByRaw('MONTH(date)')
            ->orderByRaw('MONTH(date)')
            ->get()
            ->keyBy('month_no');

        $monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $monthData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthData[] = (int) ($monthlyRows->get($i)->total ?? 0);
        }

        $teacherRecap = (clone $baseQuery)
            ->selectRaw('teacher_id, COUNT(*) as total')
            ->groupBy('teacher_id')
            ->orderByDesc('total')
            ->with('teacher.user')
            ->limit(10)
            ->get();

        return view('admin.reports.journals', compact(
            'items',
            'teachers',
            'classes',
            'subjects',
            'totalJournals',
            'activeTeachers',
            'activeClasses',
            'avgPerDay',
            'chartYear',
            'monthLabels',
            'monthData',
            'teacherRecap'
        ));
    }

    public function exportJournalsCsv(): StreamedResponse
    {
        $query = TeachingJournal::query()->with(['teacher.user', 'class', 'subject']);

        if (request('teacher_id')) {
            $query->where('teacher_id', request('teacher_id'));
        }

        if (request('class_id')) {
            $query->where('class_id', request('class_id'));
        }

        if (request('subject_id')) {
            $query->where('subject_id', request('subject_id'));
        }

        if (request('date_from')) {
            $query->whereDate('date', '>=', request('date_from'));
        }

        if (request('date_to')) {
            $query->whereDate('date', '<=', request('date_to'));
        }

        $rows = $query->latest('date')->get();

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Guru', 'Kelas', 'Mapel', 'Jam Ke', 'Pertemuan Ke', 'Materi', 'Peserta Tidak Hadir', 'Lampiran']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->date->format('Y-m-d'),
                    $row->teacher->user->name,
                    $row->class->name,
                    $row->subject->name,
                    $row->jam_ke,
                    $row->pertemuan_ke,
                    $row->materi,
                    $row->absent_students_text,
                    $row->attachment_path ? asset('storage/'.$row->attachment_path) : '',
                ]);
            }
            fclose($handle);
        }, 'teaching-journals.csv', ['Content-Type' => 'text/csv']);
    }

    public function classAttendance()
    {
        $items = ClassAttendanceSession::query()->with(['teacher.user', 'class', 'subject', 'attendances.student'])->latest('date')->paginate(20);

        return view('admin.reports.class-attendance', compact('items'));
    }

    public function permissions()
    {
        $items = StudentPermission::query()->with(['student.class', 'parent.user'])->latest()->paginate(30);

        return view('admin.reports.permissions', compact('items'));
    }

    public function kpi(KPIService $service)
    {
        $month = (int) request('month', now()->month);
        $year = (int) request('year', now()->year);
        $items = $service->calculateMonthlyKPI($month, $year);

        return view('admin.reports.kpi', compact('items', 'month', 'year'));
    }

    public function exportKpiCsv(KPIService $service): StreamedResponse
    {
        $month = (int) request('month', now()->month);
        $year = (int) request('year', now()->year);
        $rows = $service->calculateMonthlyKPI($month, $year);

        $filename = "kpi-{$year}-{$month}.csv";

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Nama Guru', 'Hari Hadir', 'Hari Kerja', 'Jurnal', 'Tepat Waktu', 'Skor Kehadiran', 'Skor Jurnal', 'Skor Ketepatan', 'Modul Disubmit/Approved', 'Modul Completion %', 'Total']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['teacher']->user->name,
                    $row['days_present'],
                    $row['working_days'],
                    $row['days_with_journal'],
                    $row['on_time_days'],
                    $row['attendance_score'],
                    $row['journal_score'],
                    $row['punctuality_score'],
                    $row['total_score'],
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}

