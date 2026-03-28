<?php

namespace App\Http\Controllers\Teacher;

use App\Domain\Attendance\SessionAccessService;
use App\Domain\MasterData\ScheduleProfileService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\TeacherScheduleJournalStoreRequest;
use App\Models\ClassAttendanceSession;
use App\Models\Setting;
use App\Models\TeachingJournal;
use App\Models\TeachingSchedule;
use App\Support\SafeFileUpload;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class TeacherJournalController extends Controller
{
    public function __construct(
        private readonly ScheduleProfileService $profileService,
        private readonly SessionAccessService $accessService,
    ) {
    }

    public function index()
    {
        $teacher = auth()->user()->teacher;
        $today = now()->toDateString();
        $todayDay = $this->resolveDayOfWeek();
        $activeProfile = $this->profileService->active();

        try {
            $this->accessService->ensureTeacherCheckedInToday($teacher);
            $this->accessService->ensureJournalEnabledForDate($today);
        } catch (InvalidArgumentException $exception) {
            return redirect()->route('teacher.dashboard')->with('error', $exception->getMessage());
        }

        $schedules = TeachingSchedule::query()
            ->with(['class', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->where('schedule_profile_id', $activeProfile->id)
            ->where('day_of_week', $todayDay)
            ->where('is_active', true)
            ->orderBy('jam_ke')
            ->get()
            ->map(function (TeachingSchedule $schedule) use ($today, $teacher) {
                $session = ClassAttendanceSession::query()
                    ->with('teachingJournal')
                    ->where('teacher_id', $teacher->id)
                    ->whereDate('date', $today)
                    ->where('class_id', $schedule->class_id)
                    ->where('subject_id', $schedule->subject_id)
                    ->where('jam_ke', $schedule->jam_ke)
                    ->first();

                return [
                    'schedule' => $schedule,
                    'session' => $session,
                    'journal' => $session?->teachingJournal,
                ];
            });

        return view('teacher.journals.index', compact('schedules', 'activeProfile'));
    }

    public function create(TeachingSchedule $schedule)
    {
        $teacher = auth()->user()->teacher;
        $today = now()->toDateString();

        $this->assertScheduleAccess($schedule, $teacher->id);

        try {
            $this->accessService->ensureTeacherCheckedInToday($teacher);
            $this->accessService->ensureJournalEnabledForDate($today);
        } catch (InvalidArgumentException $exception) {
            return redirect()->route('teacher.dashboard')->with('error', $exception->getMessage());
        }

        $session = ClassAttendanceSession::query()->firstOrCreate(
            [
                'teacher_id' => $teacher->id,
                'class_id' => $schedule->class_id,
                'subject_id' => $schedule->subject_id,
                'date' => $today,
                'jam_ke' => $schedule->jam_ke,
            ],
            [
                'teaching_schedule_id' => $schedule->id,
            ]
        );

        if (! $session->teaching_schedule_id) {
            $session->update(['teaching_schedule_id' => $schedule->id]);
        }

        if (! $this->isAttendanceJournalOpenEnabled() && $session->attendances()->count() === 0) {
            return redirect()->route('teacher.class-attendance.index', [
                'teaching_schedule_id' => $schedule->id,
                'date' => $today,
                'class_id' => $schedule->class_id,
                'subject_id' => $schedule->subject_id,
                'jam_ke' => $schedule->jam_ke,
            ])->with('error', 'Silakan isi absensi kelas terlebih dahulu sebelum mengisi jurnal.');
        }

        $session->loadMissing(['class', 'subject', 'attendances.student', 'teachingJournal']);
        $journal = $session->teachingJournal;

        $absentText = $session->attendances
            ->where('status', '!=', 'hadir')
            ->pluck('student.full_name')
            ->filter()
            ->implode(', ');

        return view('teacher.journals.form', [
            'schedule' => $schedule,
            'session' => $session,
            'journal' => $journal,
            'absentText' => $absentText,
        ]);
    }

    public function store(TeacherScheduleJournalStoreRequest $request, TeachingSchedule $schedule)
    {
        $teacher = $request->user()->teacher;
        $today = now()->toDateString();

        $this->assertScheduleAccess($schedule, $teacher->id);

        try {
            $this->accessService->ensureTeacherCheckedInToday($teacher);
            $this->accessService->ensureJournalEnabledForDate($today);
        } catch (InvalidArgumentException $exception) {
            return redirect()->route('teacher.dashboard')->with('error', $exception->getMessage());
        }

        $payload = $request->validated();

        try {
            DB::transaction(function () use ($teacher, $today, $schedule, $payload, $request) {
                $session = ClassAttendanceSession::query()->firstOrCreate(
                    [
                        'teacher_id' => $teacher->id,
                        'class_id' => $schedule->class_id,
                        'subject_id' => $schedule->subject_id,
                        'date' => $today,
                        'jam_ke' => $schedule->jam_ke,
                    ],
                    [
                        'teaching_schedule_id' => $schedule->id,
                    ]
                );

                if (! $session->teaching_schedule_id) {
                    $session->update(['teaching_schedule_id' => $schedule->id]);
                }

                if (! $this->isAttendanceJournalOpenEnabled() && $session->attendances()->count() === 0) {
                    throw new InvalidArgumentException('Silakan isi absensi kelas terlebih dahulu sebelum mengisi jurnal.');
                }

                $absentText = $session->attendances()
                    ->where('status', '!=', 'hadir')
                    ->with('student')
                    ->get()
                    ->pluck('student.full_name')
                    ->filter()
                    ->implode(', ');

                $journal = TeachingJournal::query()->firstOrNew([
                    'class_attendance_session_id' => $session->id,
                ]);

                $attachmentPath = $journal->attachment_path;
                if ($request->hasFile('attachment')) {
                    $newPath = SafeFileUpload::storePublic($request->file('attachment'), 'teaching-journals');
                    if ($attachmentPath) {
                        Storage::disk('public')->delete($attachmentPath);
                    }
                    $attachmentPath = $newPath;
                }

                $journal->fill([
                    'teacher_id' => $teacher->id,
                    'date' => $today,
                    'class_id' => $schedule->class_id,
                    'subject_id' => $schedule->subject_id,
                    'jam_ke' => $schedule->jam_ke,
                    'materi' => $payload['materi'],
                    'student_notes' => $payload['student_notes'] ?? null,
                    'absent_students_text' => $absentText,
                    'attachment_path' => $attachmentPath,
                ]);
                $journal->save();
            });
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage())->withInput();
        }

        return redirect()->route('teacher.journals.index')->with('success', 'Jurnal berhasil disimpan.');
    }

    private function assertScheduleAccess(TeachingSchedule $schedule, int $teacherId): void
    {
        $activeProfile = $this->profileService->active();

        abort_unless($schedule->teacher_id === $teacherId, 403);
        abort_unless($schedule->schedule_profile_id === $activeProfile->id, 403);
        if (! $this->isAttendanceJournalOpenEnabled()) {
            abort_unless($schedule->day_of_week === $this->resolveDayOfWeek(), 403);
        }
    }
    private function isAttendanceJournalOpenEnabled(): bool
    {
        return (bool) (Setting::active()->attendance_journal_open_enabled ?? false);
    }

    private function resolveDayOfWeek(): string
    {
        return match ((int) now()->dayOfWeekIso) {
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
            default => 'sunday',
        };
    }
}


