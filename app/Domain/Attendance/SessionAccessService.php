<?php

namespace App\Domain\Attendance;

use App\Models\Attendance;
use App\Models\ClassAttendanceSession;
use App\Models\Setting;
use App\Models\Teacher;
use Carbon\Carbon;
use InvalidArgumentException;

class SessionAccessService
{
    public function ensureTeacherCheckedInToday(Teacher $teacher): void
    {
        $checkedIn = Attendance::query()
            ->where('teacher_id', $teacher->id)
            ->whereDate('date', now()->toDateString())
            ->exists();

        if (! $checkedIn) {
            throw new InvalidArgumentException('Anda harus melakukan absen guru terlebih dahulu sebelum mengisi absensi kelas atau jurnal mengajar.');
        }
    }

    public function ensureClassAttendanceDateAllowed(string $date, ?ClassAttendanceSession $session = null): void
    {
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
        $setting = Setting::active();
        if (! $setting->journal_lock_enabled) {
            return;
        }

        $date = $session->date->toDateString();

        if ($date === now()->toDateString()) {
            return;
        }

        if ($session->hasValidOverride()) {
            return;
        }

        throw new InvalidArgumentException('Absensi atau jurnal hanya dapat diisi pada hari yang sama. Silakan ajukan izin pengisian susulan kepada admin.');
    }
}
