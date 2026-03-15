<?php

namespace App\Domain\Attendance;

use App\Domain\MasterData\AcademicCalendarService;
use App\Domain\MasterData\TeachingScheduleService;
use App\Models\ClassAttendanceSession;
use App\Models\Teacher;
use App\Models\TeacherLeaveRequest;
use App\Models\TeacherSubstituteAssignment;
use App\Models\TeachingSchedule;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LeaveCoverageService
{
    public function __construct(
        private readonly TeachingScheduleService $teachingScheduleService,
        private readonly AcademicCalendarService $calendarService,
    ) {
    }

    public function detectAffectedSessions(TeacherLeaveRequest $leave): Collection
    {
        $profile = $this->teachingScheduleService->getActiveProfile();
        $dates = CarbonPeriod::create($leave->date_from, $leave->date_to);

        $sessions = collect();
        foreach ($dates as $date) {
            $dateString = Carbon::parse($date)->toDateString();
            if (! $this->calendarService->isSubstituteGenerationEnabled($dateString)) {
                continue;
            }

            $day = $this->teachingScheduleService->resolveDayOfWeek(Carbon::parse($date));

            TeachingSchedule::query()
                ->with(['class', 'subject'])
                ->where('schedule_profile_id', $profile->id)
                ->where('teacher_id', $leave->teacher_id)
                ->where('day_of_week', $day)
                ->where('is_active', true)
                ->orderBy('jam_ke')
                ->get()
                ->each(function (TeachingSchedule $schedule) use ($sessions, $leave, $dateString, $profile) {
                    $sessions->push([
                        'leave_id' => $leave->id,
                        'original_teacher_id' => $leave->teacher_id,
                        'class_id' => $schedule->class_id,
                        'subject_id' => $schedule->subject_id,
                        'date' => $dateString,
                        'schedule_profile_id' => $profile->id,
                        'jam_ke' => $schedule->jam_ke,
                        'substitution_type' => 'substitute_teacher',
                        'status' => 'pending',
                        'notes' => $leave->coverage_notes,
                    ]);
                });
        }

        return $sessions;
    }

    public function syncDetectedSessions(TeacherLeaveRequest $leave): void
    {
        if (! $leave->affects_teaching_schedule) {
            return;
        }

        $rows = $this->detectAffectedSessions($leave);

        DB::transaction(function () use ($leave, $rows) {
            foreach ($rows as $row) {
                TeacherSubstituteAssignment::query()->updateOrCreate([
                    'original_teacher_id' => $row['original_teacher_id'],
                    'class_id' => $row['class_id'],
                    'subject_id' => $row['subject_id'],
                    'date' => $row['date'],
                    'jam_ke' => $row['jam_ke'],
                    'schedule_profile_id' => $row['schedule_profile_id'],
                ], [
                    'leave_id' => $leave->id,
                    'substitution_type' => $row['substitution_type'],
                    'status' => 'pending',
                    'notes' => $row['notes'],
                ]);
            }
        });
    }

    public function assignSubstitute(TeacherSubstituteAssignment $assignment, Teacher $substitute, int $approvedBy, ?string $notes = null): void
    {
        $this->validateSubstitute($assignment, $substitute);

        $assignment->update([
            'substitute_teacher_id' => $substitute->id,
            'status' => 'assigned',
            'notes' => $notes,
            'approved_by' => $approvedBy,
        ]);
    }

    public function clearSubstitute(TeacherSubstituteAssignment $assignment, int $approvedBy, ?string $notes = null): void
    {
        $assignment->update([
            'substitute_teacher_id' => null,
            'status' => 'pending',
            'notes' => $notes,
            'approved_by' => $approvedBy,
        ]);
    }

    public function validateSubstitute(TeacherSubstituteAssignment $assignment, Teacher $substitute): void
    {
        if (! $substitute->user || $substitute->user->role !== 'teacher') {
            throw new InvalidArgumentException('Guru pengganti tidak valid atau tidak aktif.');
        }

        if ($substitute->id === $assignment->original_teacher_id) {
            throw new InvalidArgumentException('Guru pengganti tidak boleh sama dengan guru asli.');
        }

        $date = $assignment->date->toDateString();

        $isOnLeave = TeacherLeaveRequest::query()
            ->where('teacher_id', $substitute->id)
            ->where('status', 'approved')
            ->whereDate('date_from', '<=', $date)
            ->whereDate('date_to', '>=', $date)
            ->exists();

        if ($isOnLeave) {
            throw new InvalidArgumentException('Guru pengganti sedang izin pada tanggal tersebut.');
        }

        $scheduleConflict = TeachingSchedule::query()
            ->where('schedule_profile_id', $assignment->schedule_profile_id)
            ->where('teacher_id', $substitute->id)
            ->where('day_of_week', $this->teachingScheduleService->resolveDayOfWeek(Carbon::parse($date)))
            ->where('jam_ke', $assignment->jam_ke)
            ->where('is_active', true)
            ->exists();

        if ($scheduleConflict) {
            throw new InvalidArgumentException('Guru pengganti memiliki konflik jadwal pada jam tersebut.');
        }

        $substituteConflict = TeacherSubstituteAssignment::query()
            ->where('substitute_teacher_id', $substitute->id)
            ->whereDate('date', $date)
            ->where('jam_ke', $assignment->jam_ke)
            ->where('id', '!=', $assignment->id)
            ->whereIn('status', ['assigned', 'completed'])
            ->exists();

        if ($substituteConflict) {
            throw new InvalidArgumentException('Guru pengganti sudah ditugaskan pada sesi pengganti lain di jam yang sama.');
        }
    }

    public function canTeacherExecuteSession(Teacher $teacher, ClassAttendanceSession $session): bool
    {
        if ($session->teacher_id === $teacher->id || $session->executing_teacher_id === $teacher->id) {
            return true;
        }

        return TeacherSubstituteAssignment::query()
            ->where('original_teacher_id', $session->teacher_id)
            ->where('substitute_teacher_id', $teacher->id)
            ->whereDate('date', $session->date->toDateString())
            ->where('class_id', $session->class_id)
            ->where('subject_id', $session->subject_id)
            ->where('jam_ke', $session->jam_ke)
            ->whereIn('status', ['assigned', 'completed'])
            ->exists();
    }

    public function markCompletedForSession(ClassAttendanceSession $session): void
    {
        if (! $session->executing_teacher_id) {
            return;
        }

        TeacherSubstituteAssignment::query()
            ->where('original_teacher_id', $session->teacher_id)
            ->where('substitute_teacher_id', $session->executing_teacher_id)
            ->whereDate('date', $session->date->toDateString())
            ->where('class_id', $session->class_id)
            ->where('subject_id', $session->subject_id)
            ->where('jam_ke', $session->jam_ke)
            ->where('status', 'assigned')
            ->update(['status' => 'completed']);
    }
}
