<?php

namespace App\Domain\MasterData;

use App\Models\Assignment;
use App\Models\ClassAttendanceSession;
use App\Models\ScheduleProfile;
use App\Models\Teacher;
use App\Models\TeachingSchedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class TeachingScheduleService
{
    public function getActiveProfile(): ScheduleProfile
    {
        return ScheduleProfile::active() ?? ScheduleProfile::query()->firstOrCreate(
            ['code' => 'normal'],
            ['name' => 'Normal', 'is_active' => true, 'description' => 'Jadwal reguler sekolah']
        );
    }

    public function getTodaySchedules(Teacher $teacher): Collection
    {
        $today = now()->toDateString();
        $dayOfWeek = $this->resolveDayOfWeek(now());
        $activeProfile = $this->getActiveProfile();

        $schedules = TeachingSchedule::query()
            ->with(['class', 'subject'])
            ->where('schedule_profile_id', $activeProfile->id)
            ->where('teacher_id', $teacher->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->orderBy('jam_ke')
            ->get();

        $sessions = ClassAttendanceSession::query()
            ->with('teachingJournal')
            ->where('teacher_id', $teacher->id)
            ->whereDate('date', $today)
            ->get()
            ->keyBy(function (ClassAttendanceSession $session) {
                return $session->class_id.'-'.$session->subject_id.'-'.$session->jam_ke;
            });

        return $schedules->map(function (TeachingSchedule $schedule) use ($sessions) {
            $key = $schedule->class_id.'-'.$schedule->subject_id.'-'.$schedule->jam_ke;
            $session = $sessions->get($key);

            return [
                'schedule' => $schedule,
                'session' => $session,
                'attendance_done' => (bool) $session,
                'journal_done' => (bool) ($session?->teachingJournal),
            ];
        });
    }

    public function ensureAssignmentMatches(int $teacherId, int $classId, int $subjectId): void
    {
        $isAssigned = Assignment::query()
            ->where('teacher_id', $teacherId)
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->exists();

        if (! $isAssigned) {
            throw new InvalidArgumentException('Jadwal hanya dapat dibuat untuk assignment guru yang valid.');
        }
    }

    public function ensureNoDuplicate(array $payload, ?int $ignoreId = null): void
    {
        $query = TeachingSchedule::query()
            ->where('schedule_profile_id', $payload['schedule_profile_id'])
            ->where('teacher_id', $payload['teacher_id'])
            ->where('class_id', $payload['class_id'])
            ->where('subject_id', $payload['subject_id'])
            ->where('day_of_week', $payload['day_of_week'])
            ->where('jam_ke', $payload['jam_ke']);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw new InvalidArgumentException('Jadwal duplikat untuk guru/kelas/mapel/hari/jam tersebut.');
        }
    }

    public function resolveDayOfWeek(Carbon $date): string
    {
        return match ((int) $date->dayOfWeekIso) {
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
            default => 'sunday',
        };
    }

    public function dayOptions(): array
    {
        return [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
        ];
    }
}
