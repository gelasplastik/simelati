<?php

namespace App\Domain\DutyMonitoring;

use App\Domain\MasterData\TeachingScheduleService;
use App\Models\Attendance;
use App\Models\ClassAttendance;
use App\Models\DutyReport;
use App\Models\DutyReportStudentRow;
use App\Models\DutyReportTeacherRow;
use App\Models\DutyTeacherAssignment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentPermission;
use App\Models\Teacher;
use App\Models\TeacherLeaveRequest;
use App\Models\TeacherSubstituteAssignment;
use App\Models\TeachingSchedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DutyMonitoringService
{
    public function __construct(
        private readonly TeachingScheduleService $teachingScheduleService,
    ) {
    }

    public function getAssignedTeachersForDate(Carbon|string $date): Collection
    {
        $targetDate = Carbon::parse($date)->toDateString();
        $dayOfWeek = $this->resolveDutyDayOfWeek($targetDate);

        return DutyTeacherAssignment::query()
            ->with('teacher.user')
            ->where(function ($query) use ($targetDate, $dayOfWeek) {
                $query->where('day_of_week', $dayOfWeek)
                    ->orWhere(function ($legacy) use ($targetDate) {
                        $legacy->whereNull('day_of_week')->whereDate('date', $targetDate);
                    });
            })
            ->orderBy('teacher_id')
            ->get();
    }

    public function isAssignedTeacher(Teacher $teacher, Carbon|string $date): bool
    {
        $targetDate = Carbon::parse($date)->toDateString();
        $dayOfWeek = $this->resolveDutyDayOfWeek($targetDate);

        return DutyTeacherAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->where(function ($query) use ($targetDate, $dayOfWeek) {
                $query->where('day_of_week', $dayOfWeek)
                    ->orWhere(function ($legacy) use ($targetDate) {
                        $legacy->whereNull('day_of_week')->whereDate('date', $targetDate);
                    });
            })
            ->exists();
    }

    public function ensureTeacherCanManageReport(Teacher $teacher, DutyReport $report): void
    {
        if (! $this->isAssignedTeacher($teacher, $report->date)) {
            throw new InvalidArgumentException('Anda tidak ditugaskan sebagai guru piket pada tanggal laporan ini.');
        }
    }

    public function getOrCreateReport(Carbon|string $date, ?Teacher $dutyTeacher, int $userId): DutyReport
    {
        $targetDate = Carbon::parse($date)->toDateString();

        $report = DutyReport::query()->firstOrCreate(
            ['date' => $targetDate],
            [
                'duty_teacher_id' => $dutyTeacher?->id,
                'status' => 'draft',
                'created_by' => $userId,
            ]
        );

        if (! $report->duty_teacher_id && $dutyTeacher) {
            $report->update(['duty_teacher_id' => $dutyTeacher->id]);
            $report->refresh();
        }

        return $report;
    }

    public function syncReportSnapshot(DutyReport $report): DutyReport
    {
        $date = $report->date->toDateString();

        DB::transaction(function () use ($report, $date) {
            $this->syncTeacherRows($report, $date);
            $this->syncStudentRows($report, $date);
        });

        return $report->fresh(['teacherRows.teacher.user', 'teacherRows.substituteTeacher.user', 'studentRows.class']);
    }

    public function buildDashboard(Carbon|string $date): array
    {
        $targetDate = Carbon::parse($date)->toDateString();
        $teacherSummary = $this->buildTeacherSummary($targetDate);
        $studentSummary = $this->buildStudentSummary($targetDate);

        $classesNeedAttention = collect($studentSummary['rows'])
            ->filter(fn (array $row) => ($row['alpa_count'] + $row['late_count']) > 0)
            ->sortByDesc(fn (array $row) => $row['alpa_count'] * 2 + $row['late_count'])
            ->values();

        return [
            'date' => $targetDate,
            'teacher' => $teacherSummary,
            'student' => $studentSummary,
            'classes_need_attention' => $classesNeedAttention,
        ];
    }

    public function saveDraft(
        DutyReport $report,
        array $teacherRowsPayload,
        array $studentRowsPayload,
        ?string $notes,
        bool $finalize,
        int $userId
    ): DutyReport {
        DB::transaction(function () use ($report, $teacherRowsPayload, $studentRowsPayload, $notes, $finalize, $userId) {
            foreach ($teacherRowsPayload as $rowPayload) {
                $row = DutyReportTeacherRow::query()
                    ->where('report_id', $report->id)
                    ->where('id', (int) $rowPayload['id'])
                    ->first();

                if (! $row) {
                    continue;
                }

                $row->update([
                    'verified_status' => $rowPayload['verified_status'],
                    'reason' => $rowPayload['reason'] ?? $row->reason,
                    'has_substitute' => (bool) ($rowPayload['has_substitute'] ?? false),
                    'notes' => $rowPayload['notes'] ?? null,
                ]);
            }

            foreach ($studentRowsPayload as $rowPayload) {
                $row = DutyReportStudentRow::query()
                    ->where('report_id', $report->id)
                    ->where('id', (int) $rowPayload['id'])
                    ->first();

                if (! $row) {
                    continue;
                }

                $row->update([
                    'total_students' => max(0, (int) $rowPayload['total_students']),
                    'present_count' => max(0, (int) $rowPayload['present_count']),
                    'sick_count' => max(0, (int) $rowPayload['sick_count']),
                    'izin_count' => max(0, (int) $rowPayload['izin_count']),
                    'alpa_count' => max(0, (int) $rowPayload['alpa_count']),
                    'late_count' => max(0, (int) $rowPayload['late_count']),
                    'notes' => $rowPayload['notes'] ?? null,
                ]);
            }

            $updatePayload = [
                'notes' => $notes,
            ];

            if ($finalize) {
                if ($report->isFinalized()) {
                    throw new InvalidArgumentException('Laporan piket harian sudah difinalisasi.');
                }

                $updatePayload['status'] = 'finalized';
                $updatePayload['finalized_at'] = now();
                $updatePayload['finalized_by'] = $userId;
            }

            $report->update($updatePayload);
        });

        return $report->fresh(['teacherRows.teacher.user', 'teacherRows.substituteTeacher.user', 'studentRows.class']);
    }

    private function syncTeacherRows(DutyReport $report, string $date): void
    {
        $summary = $this->buildTeacherSummary($date);

        foreach ($summary['rows'] as $item) {
            DutyReportTeacherRow::query()->updateOrCreate(
                [
                    'report_id' => $report->id,
                    'teacher_id' => $item['teacher_id'],
                ],
                [
                    'teacher_name' => $item['teacher_name'],
                    'subject_label' => $item['subject_label'],
                    'attendance_status' => $item['attendance_status'],
                    'verified_status' => $item['verified_status'],
                    'reason' => $item['reason'],
                    'has_substitute' => $item['has_substitute'],
                    'substitute_teacher_id' => $item['substitute_teacher_id'],
                    'substitute_teacher_name' => $item['substitute_teacher_name'],
                    'notes' => $item['notes'],
                ]
            );
        }
    }

    private function syncStudentRows(DutyReport $report, string $date): void
    {
        $summary = $this->buildStudentSummary($date);

        foreach ($summary['rows'] as $item) {
            DutyReportStudentRow::query()->updateOrCreate(
                [
                    'report_id' => $report->id,
                    'class_id' => $item['class_id'],
                ],
                [
                    'class_name' => $item['class_name'],
                    'total_students' => $item['total_students'],
                    'present_count' => $item['present_count'],
                    'sick_count' => $item['sick_count'],
                    'izin_count' => $item['izin_count'],
                    'alpa_count' => $item['alpa_count'],
                    'late_count' => $item['late_count'],
                    'notes' => $item['notes'],
                ]
            );
        }
    }

    private function buildTeacherSummary(string $date): array
    {
        $teachers = Teacher::query()->with('user')->get();
        $dayOfWeek = $this->teachingScheduleService->resolveDayOfWeek(Carbon::parse($date));
        $activeProfile = $this->teachingScheduleService->getActiveProfile();

        $presentIds = Attendance::query()
            ->whereDate('date', $date)
            ->pluck('teacher_id')
            ->all();

        $leaveRows = TeacherLeaveRequest::query()
            ->where('status', 'approved')
            ->whereDate('date_from', '<=', $date)
            ->whereDate('date_to', '>=', $date)
            ->get()
            ->keyBy('teacher_id');

        $subjectsByTeacher = TeachingSchedule::query()
            ->with('subject')
            ->where('schedule_profile_id', $activeProfile->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->get()
            ->groupBy('teacher_id')
            ->map(fn (Collection $rows) => $rows->pluck('subject.name')->filter()->unique()->implode(', '));

        $substituteByTeacher = TeacherSubstituteAssignment::query()
            ->with('substituteTeacher.user')
            ->whereDate('date', $date)
            ->whereIn('status', ['assigned', 'completed'])
            ->whereNotNull('substitute_teacher_id')
            ->get()
            ->groupBy('original_teacher_id');

        $rows = $teachers->map(function (Teacher $teacher) use ($presentIds, $leaveRows, $subjectsByTeacher, $substituteByTeacher) {
            $isPresent = in_array($teacher->id, $presentIds, true);
            $leave = $leaveRows->get($teacher->id);

            $attendanceStatus = $isPresent ? 'present' : ($leave ? 'leave' : 'absent');
            $reason = $leave?->reason;

            $substitute = $substituteByTeacher->get($teacher->id)?->first();

            return [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->user?->name ?? 'Guru',
                'subject_label' => $subjectsByTeacher->get($teacher->id),
                'attendance_status' => $attendanceStatus,
                'verified_status' => $attendanceStatus,
                'reason' => $reason,
                'has_substitute' => (bool) $substitute,
                'substitute_teacher_id' => $substitute?->substitute_teacher_id,
                'substitute_teacher_name' => $substitute?->substituteTeacher?->user?->name,
                'notes' => null,
            ];
        })->values();

        $teacherAbsentRows = $rows->where('attendance_status', 'absent')->values();

        return [
            'total_teachers' => $teachers->count(),
            'present_teachers' => $rows->where('attendance_status', 'present')->count(),
            'leave_teachers' => $rows->where('attendance_status', 'leave')->count(),
            'absent_teachers' => $teacherAbsentRows->count(),
            'covered_absent_teachers' => $teacherAbsentRows->where('has_substitute', true)->count(),
            'uncovered_absent_teachers' => $teacherAbsentRows->where('has_substitute', false)->count(),
            'rows' => $rows,
        ];
    }

    private function buildStudentSummary(string $date): array
    {
        $classes = SchoolClass::query()->orderBy('name')->get();

        $totalsByClass = Student::query()
            ->select('class_id', DB::raw('COUNT(*) as total'))
            ->where('is_active', true)
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        $attendanceRows = ClassAttendance::query()
            ->select(
                'class_attendance_sessions.class_id',
                DB::raw("SUM(CASE WHEN class_attendances.status = 'hadir' THEN 1 ELSE 0 END) as present_count"),
                DB::raw("SUM(CASE WHEN class_attendances.status = 'sakit' THEN 1 ELSE 0 END) as sick_count"),
                DB::raw("SUM(CASE WHEN class_attendances.status = 'izin' THEN 1 ELSE 0 END) as izin_count"),
                DB::raw("SUM(CASE WHEN class_attendances.status = 'alpa' THEN 1 ELSE 0 END) as alpa_count"),
                DB::raw("SUM(CASE WHEN LOWER(COALESCE(class_attendances.note, '')) LIKE '%telat%' THEN 1 ELSE 0 END) as late_count")
            )
            ->join('class_attendance_sessions', 'class_attendance_sessions.id', '=', 'class_attendances.session_id')
            ->whereDate('class_attendance_sessions.date', $date)
            ->groupBy('class_attendance_sessions.class_id')
            ->get()
            ->keyBy('class_id');

        $permissionIzinByClass = StudentPermission::query()
            ->select(
                'students.class_id',
                DB::raw('COUNT(DISTINCT student_permissions.student_id) as izin_count')
            )
            ->join('students', 'students.id', '=', 'student_permissions.student_id')
            ->where('students.is_active', true)
            ->whereIn('student_permissions.status', ['submitted', 'approved'])
            ->whereDate('student_permissions.date_from', '<=', $date)
            ->whereDate('student_permissions.date_to', '>=', $date)
            ->groupBy('students.class_id')
            ->pluck('izin_count', 'students.class_id');

        $rows = $classes->map(function (SchoolClass $class) use ($totalsByClass, $attendanceRows, $permissionIzinByClass) {
            $attendance = $attendanceRows->get($class->id);
            $totalStudents = (int) ($totalsByClass[$class->id] ?? 0);
            $presentCount = (int) ($attendance->present_count ?? 0);
            $sickCount = (int) ($attendance->sick_count ?? 0);
            $izinFromAttendance = (int) ($attendance->izin_count ?? 0);
            $alpaCount = (int) ($attendance->alpa_count ?? 0);
            $lateCount = (int) ($attendance->late_count ?? 0);
            $izinFromPermission = (int) ($permissionIzinByClass[$class->id] ?? 0);
            $izinCount = max($izinFromAttendance, $izinFromPermission);

            $hasAttendanceRows = ($presentCount + $sickCount + $izinFromAttendance + $alpaCount) > 0;
            if (! $hasAttendanceRows) {
                $presentCount = max(0, $totalStudents - $izinCount);
            }

            return [
                'class_id' => $class->id,
                'class_name' => $class->name,
                'total_students' => $totalStudents,
                'present_count' => $presentCount,
                'sick_count' => $sickCount,
                'izin_count' => $izinCount,
                'alpa_count' => $alpaCount,
                'late_count' => $lateCount,
                'notes' => null,
            ];
        })->values();

        return [
            'total_classes' => $classes->count(),
            'total_students' => $rows->sum('total_students'),
            'present_students' => $rows->sum('present_count'),
            'sick_students' => $rows->sum('sick_count'),
            'izin_students' => $rows->sum('izin_count'),
            'alpa_students' => $rows->sum('alpa_count'),
            'late_students' => $rows->sum('late_count'),
            'rows' => $rows,
        ];
    }
private function resolveDutyDayOfWeek(string $date): string
    {
        return $this->teachingScheduleService->resolveDayOfWeek(Carbon::parse($date));
    }
}



