<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeachingJournal extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'class_attendance_session_id',
        'date',
        'class_id',
        'subject_id',
        'jam_ke',
        'pertemuan_ke',
        'materi',
        'absent_students_text',
        'student_notes',
        'mastery_notes',
        'attachment_path',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function classAttendanceSession(): BelongsTo
    {
        return $this->belongsTo(ClassAttendanceSession::class, 'class_attendance_session_id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
