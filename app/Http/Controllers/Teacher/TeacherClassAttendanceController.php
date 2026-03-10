<?php

namespace App\Http\Controllers\Teacher;

use App\Domain\Attendance\SessionAccessService;
use App\Domain\MasterData\TeachingScheduleService;
use App\Domain\Permissions\PermissionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\ClassAttendanceSaveRequest;
use App\Models\Assignment;
use App\Models\ClassAttendance;
use App\Models\ClassAttendanceSession;
use App\Models\Student;
use App\Models\TeachingSchedule;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TeacherClassAttendanceController extends Controller
{
    public function __construct(
        private readonly PermissionService $permissionService,
        private readonly SessionAccessService $accessService,
        private readonly TeachingScheduleService $teachingScheduleService,
    ) {
    }

    public function index()
    {
        $teacher = auth()->user()->teacher;
        $assignments = Assignment::query()->with(['class', 'subject'])->where('teacher_id', $teacher->id)->get();

        $selectedScheduleId = request('teaching_schedule_id');
        $selectedSchedule = null;
        $classes = $assignments->pluck('class')->unique('id')->sortBy('name')->values();
        $selectedClass = request('class_id');
        $selectedSubject = request('subject_id');
        $selectedDate = request('date', now()->toDateString());
        $selectedJamKe = (int) request('jam_ke', 1);

        try {
            $this->accessService->ensureTeacherCheckedInToday($teacher);
        } catch (InvalidArgumentException $exception) {
            return redirect()->route('teacher.dashboard')->with('error', $exception->getMessage());
        }

        if ($selectedScheduleId) {
            $activeProfile = $this->teachingScheduleService->getActiveProfile();
            $selectedSchedule = TeachingSchedule::query()
                ->with(['class', 'subject'])
                ->where('schedule_profile_id', $activeProfile->id)
                ->where('teacher_id', $teacher->id)
                ->where('id', (int) $selectedScheduleId)
                ->first();

            if ($selectedSchedule) {
                $selectedClass = (string) $selectedSchedule->class_id;
                $selectedSubject = (string) $selectedSchedule->subject_id;
                $selectedJamKe = (int) $selectedSchedule->jam_ke;
                $selectedDate = request('date', now()->toDateString());
            }
        }

        $subjects = $assignments
            ->when($selectedClass, fn ($items) => $items->where('class_id', (int) $selectedClass))
            ->pluck('subject')
            ->unique('id')
            ->sortBy('name')
            ->values();

        $students = collect();
        $session = null;
        $hasOverride = false;

        if ($selectedClass && $selectedSubject) {
            $session = ClassAttendanceSession::query()
                ->where('teacher_id', $teacher->id)
                ->where('class_id', $selectedClass)
                ->where('subject_id', $selectedSubject)
                ->whereDate('date', $selectedDate)
                ->where('jam_ke', $selectedJamKe)
                ->first();

            try {
                $this->accessService->ensureClassAttendanceDateAllowed($selectedDate, $session);
            } catch (InvalidArgumentException $exception) {
                $requestUrl = route('teacher.late-entry-requests.create', [
                    'date' => $selectedDate,
                    'class_id' => $selectedClass,
                    'subject_id' => $selectedSubject,
                    'jam_ke' => $selectedJamKe,
                    'request_type' => 'attendance',
                ]);

                return redirect()->route('teacher.class-attendance.index', [
                    'class_id' => $selectedClass,
                    'subject_id' => $selectedSubject,
                    'date' => now()->toDateString(),
                    'jam_ke' => $selectedJamKe,
                    'teaching_schedule_id' => $selectedScheduleId,
                ])->with('error', $exception->getMessage())->with('late_entry_request_url', $requestUrl);
            }

            $hasOverride = $session?->hasValidOverride() ?? false;
            $existingRows = $session
                ? ClassAttendance::query()->where('session_id', $session->id)->get()->keyBy('student_id')
                : collect();

            $students = Student::query()
                ->where('class_id', $selectedClass)
                ->where('is_active', true)
                ->orderBy('full_name')
                ->get()
                ->map(function ($student) use ($selectedDate, $existingRows) {
                    $permission = $this->permissionService->resolveStudentPermission($student, $selectedDate);
                    $existing = $existingRows->get($student->id);
                    $student->auto_status = $existing?->status ?? ($permission ? 'izin' : 'hadir');
                    $student->auto_note = $existing?->note ?? ($permission ? $permission->reason : null);
                    $student->is_auto = (bool) $permission;

                    return $student;
                });
        }

        return view('teacher.class_attendance.index', compact(
            'classes',
            'subjects',
            'students',
            'selectedClass',
            'selectedSubject',
            'selectedDate',
            'selectedJamKe',
            'selectedScheduleId',
            'selectedSchedule',
            'session',
            'hasOverride',
        ));
    }

    public function store(ClassAttendanceSaveRequest $request)
    {
        try {
            $teacher = $request->user()->teacher;
            $this->accessService->ensureTeacherCheckedInToday($teacher);

            $existingSession = ClassAttendanceSession::query()
                ->where('teacher_id', $teacher->id)
                ->where('class_id', $request->integer('class_id'))
                ->where('subject_id', $request->integer('subject_id'))
                ->whereDate('date', $request->date('date')->toDateString())
                ->where('jam_ke', $request->integer('jam_ke'))
                ->first();

            $this->accessService->ensureClassAttendanceDateAllowed($request->date, $existingSession);

            $isAssigned = Assignment::query()
                ->where('teacher_id', $teacher->id)
                ->where('class_id', $request->integer('class_id'))
                ->where('subject_id', $request->integer('subject_id'))
                ->exists();

            if (! $isAssigned) {
                throw new InvalidArgumentException('Mapel dan kelas tidak sesuai assignment guru.');
            }

            $teachingSchedule = null;
            if ($request->filled('teaching_schedule_id')) {
                $activeProfile = $this->teachingScheduleService->getActiveProfile();
                $teachingSchedule = TeachingSchedule::query()
                    ->where('schedule_profile_id', $activeProfile->id)
                    ->where('teacher_id', $teacher->id)
                    ->find($request->integer('teaching_schedule_id'));

                if (! $teachingSchedule) {
                    throw new InvalidArgumentException('Jadwal mengajar tidak ditemukan.');
                }

                $scheduleMatch = $teachingSchedule->class_id === $request->integer('class_id')
                    && $teachingSchedule->subject_id === $request->integer('subject_id')
                    && (int) $teachingSchedule->jam_ke === $request->integer('jam_ke');

                if (! $scheduleMatch) {
                    throw new InvalidArgumentException('Data absensi tidak sesuai dengan jadwal mengajar terpilih.');
                }
            }

            $session = DB::transaction(function () use ($request, $teacher, $existingSession, $teachingSchedule) {
                if ($existingSession) {
                    $session = $existingSession;
                    $session->update([
                        'date' => $request->date('date')->toDateString(),
                        'jam_ke' => $request->integer('jam_ke'),
                        'teaching_schedule_id' => $teachingSchedule?->id ?? $session->teaching_schedule_id,
                    ]);
                } else {
                    $session = ClassAttendanceSession::query()->create([
                        'teacher_id' => $teacher->id,
                        'class_id' => $request->integer('class_id'),
                        'subject_id' => $request->integer('subject_id'),
                        'teaching_schedule_id' => $teachingSchedule?->id,
                        'date' => $request->date('date')->toDateString(),
                        'jam_ke' => $request->integer('jam_ke'),
                    ]);
                }

                foreach ($request->validated('rows') as $row) {
                    ClassAttendance::query()->updateOrCreate([
                        'session_id' => $session->id,
                        'student_id' => $row['student_id'],
                    ], [
                        'status' => $row['status'],
                        'note' => $row['note'] ?? null,
                    ]);
                }

                return $session;
            });

            return redirect()->route('teacher.class-attendance.index', [
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'date' => $request->date,
                'jam_ke' => $request->jam_ke,
                'teaching_schedule_id' => $request->teaching_schedule_id,
            ])->with('success', 'Absensi kelas tersimpan.')->with('saved_session_id', $session->id);
        } catch (InvalidArgumentException $exception) {
            $requestUrl = route('teacher.late-entry-requests.create', [
                'date' => $request->date,
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'jam_ke' => $request->jam_ke,
                'request_type' => 'attendance',
            ]);

            return back()->with('error', $exception->getMessage())->with('late_entry_request_url', $requestUrl)->withInput();
        }
    }
}
