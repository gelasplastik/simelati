<?php

namespace App\Domain\Reporting;

use App\Domain\MasterData\TeachingScheduleService;
use App\Models\Attendance;
use App\Models\Teacher;

class TeacherJournalNotificationService
{
    public function __construct(
        private readonly TeachingScheduleService $scheduleService,
    ) {
    }

    public function buildToday(Teacher $teacher): array
    {
        $items = $this->scheduleService->getTodaySchedules($teacher);
        $checkedInToday = Attendance::query()
            ->where('teacher_id', $teacher->id)
            ->whereDate('date', now()->toDateString())
            ->exists();

        $needsAttendance = $items->filter(fn ($item) => ! $item['attendance_done']);
        $needsJournal = $items->filter(fn ($item) => $item['attendance_done'] && ! $item['journal_done']);
        $complete = $items->filter(fn ($item) => $item['attendance_done'] && $item['journal_done']);
        $incompleteItems = $items->filter(fn ($item) => ! $item['attendance_done'] || ! $item['journal_done']);

        $bellVariant = 'success';
        if ($items->isEmpty()) {
            $bellVariant = 'secondary';
        } elseif ($incompleteItems->isNotEmpty()) {
            $bellVariant = 'danger';
        }

        return [
            'checked_in_today' => $checkedInToday,
            'total_schedules' => $items->count(),
            'attendance_pending_count' => $needsAttendance->count(),
            'journal_pending_count' => $needsJournal->count(),
            'complete_count' => $complete->count(),
            'items' => $items,
            'incomplete_items' => $incompleteItems->map(function ($item) {
                $schedule = $item['schedule'];
                $status = ! $item['attendance_done']
                    ? 'Belum Absensi Kelas'
                    : 'Belum Jurnal';

                return [
                    'text' => 'Jam '.$schedule->jam_ke.' - '.$schedule->subject->name.' - Kelas '.$schedule->class->name.' - '.$status,
                    'status' => $status,
                ];
            })->values(),
            'bell_variant' => $bellVariant,
            'has_incomplete' => $incompleteItems->isNotEmpty(),
            'no_schedule' => $items->isEmpty(),
            'dropdown_message' => $items->isEmpty()
                ? 'Tidak ada jadwal mengajar hari ini.'
                : ($incompleteItems->isEmpty()
                    ? 'Semua tugas mengajar hari ini sudah lengkap.'
                    : null),
        ];
    }
}
