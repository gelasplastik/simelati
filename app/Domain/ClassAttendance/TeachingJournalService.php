<?php

namespace App\Domain\ClassAttendance;

use App\Models\ClassAttendanceSession;
use App\Models\TeachingJournal;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TeachingJournalService
{
    public function absentStudentsText(ClassAttendanceSession $session): string
    {
        $names = $session->attendances()
            ->with('student:id,full_name')
            ->where('status', '!=', 'hadir')
            ->get()
            ->pluck('student.full_name')
            ->filter()
            ->unique()
            ->values();

        return $names->implode(', ');
    }

    public function preloadForForm(ClassAttendanceSession $session): array
    {
        $absentText = $this->absentStudentsText($session);
        $journal = TeachingJournal::query()->where('class_attendance_session_id', $session->id)->first();

        if ($journal) {
            $journal->update(['absent_students_text' => $absentText]);
            $journal->refresh();
        }

        return [
            'session' => $session,
            'journal' => $journal,
            'absent_students_text' => $journal?->absent_students_text ?? $absentText,
        ];
    }

    public function createOrUpdate(ClassAttendanceSession $session, int $teacherId, array $payload): TeachingJournal
    {
        if ($session->teacher_id !== $teacherId) {
            throw new InvalidArgumentException('Sesi absensi tidak valid untuk guru ini.');
        }

        $absentText = $this->absentStudentsText($session);

        return DB::transaction(function () use ($session, $teacherId, $payload, $absentText) {
            $existing = TeachingJournal::query()->where('class_attendance_session_id', $session->id)->first();

            return TeachingJournal::query()->updateOrCreate([
                'class_attendance_session_id' => $session->id,
            ], [
                'teacher_id' => $teacherId,
                'date' => $session->date,
                'class_id' => $session->class_id,
                'subject_id' => $session->subject_id,
                'jam_ke' => $session->jam_ke,
                'pertemuan_ke' => $payload['pertemuan_ke'],
                'materi' => $payload['materi'],
                'absent_students_text' => $absentText,
                'student_notes' => $payload['student_notes'] ?? null,
                'mastery_notes' => $payload['mastery_notes'] ?? null,
                'attachment_path' => $payload['attachment_path'] ?? $existing?->attachment_path,
            ]);
        });
    }
}
