<?php

namespace App\Domain\Attendance;

use App\Domain\MasterData\AcademicCalendarService;
use App\Models\Attendance;
use App\Models\ClassAttendanceSession;
use App\Models\Setting;
use App\Models\Teacher;
use Carbon\Carbon;
use InvalidArgumentException;

class SessionAccessService
{
    public function __construct(
        private readonly AcademicCalendarService $calendarService,
    ) {
    }

    public function ensureTeacherCheckedInToday(Teacher $teacher): void
    {
        $today = now()->toDateString();

        if (! $this->calendarService->isTeacherAttendanceEnabled($today)) {
            return;
        }

        $checkedIn = Attendance::query()
            ->where('teacher_id', $teacher->id)
            ->whereDate('date', $today)
            ->exists();

        if (! $checkedIn) {
            throw new InvalidArgumentException('Anda harus melakukan absen guru terlebih dahulu sebelum mengisi absensi kelas atau jurnal mengajar.');
        }
    }

    public function ensureStudentAttendanceEnabledForDate(string $date): void
    {
        if ($this->isAttendanceJournalOpenEnabled()) {
            return;
        }

        if (! $this->calendarService->isStudentAttendanceEnabled($date)) {
            throw new InvalidArgumentException('Absensi siswa dinonaktifkan pada tanggal ini berdasarkan Kalender Akademik.');
        }
    }

    public function ensureJournalEnabledForDate(string $date): void
    {
        if ($this->isAttendanceJournalOpenEnabled()) {
            return;
        }

        if (! $this->calendarService->isJournalEnabled($date)) {
            throw new InvalidArgumentException('Jurnal mengajar dinonaktifkan pada tanggal ini berdasarkan Kalender Akademik.');
        }
    }

    public function ensureClassAttendanceDateAllowed(string $date, ?ClassAttendanceSession $session = null): void
    {
        if ($this->isAttendanceJournalOpenEnabled()) {
            return;
        }

        $this->ensureStudentAttendanceEnabledForDate($date);

        $target = Carbon::parse($date)->toDateString();

        if ($target === now()->toDateString()) {
            return;
        }

        if ($session && $session->hasValidOverride()) {
            return;
        }

        throw new InvalidArgumentException('Absensi atau jurnal hanya dapat diisi pada hari yang sama. Silakan ajukan izin pengisian susulan kepada admin.');
    }

    public function ensureJournalAccessAllowed(ClassAttendanceSession $session): void
    {
        if ($this->isAttendanceJournalOpenEnabled()) {
            return;
        }

        $date = $session->date->toDateString();
        $this->ensureJournalEnabledForDate($date);

        $setting = Setting::active();
        if (! $setting->journal_lock_enabled) {
            return;
        }

        if ($date === now()->toDateString()) {
            return;
        }

        if ($session->hasValidOverride()) {
            return;
        }

        throw new InvalidArgumentException('Absensi atau jurnal hanya dapat diisi pada hari yang sama. Silakan ajukan izin pengisian susulan kepada admin.');
    }

    private function isAttendanceJournalOpenEnabled(): bool
    {
        return (bool) (Setting::active()->attendance_journal_open_enabled ?? false);
    }
}
