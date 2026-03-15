<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClassAttendanceSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'original_teacher_id',
        'executing_teacher_id',
        'class_id',
        'subject_id',
        'teaching_schedule_id',
        'date',
        'jam_ke',
        'override_allowed',
        'override_reason',
        'override_allowed_by',
        'override_expires_at',
    ];

    protected $casts = [
        'date' => 'date',
        'override_allowed' => 'boolean',
        'override_expires_at' => 'datetime',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function originalTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'original_teacher_id');
    }

    public function executingTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'executing_teacher_id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teachingSchedule(): BelongsTo
    {
        return $this->belongsTo(TeachingSchedule::class, 'teaching_schedule_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(ClassAttendance::class, 'session_id');
    }

    public function teachingJournal(): HasOne
    {
        return $this->hasOne(TeachingJournal::class, 'class_attendance_session_id');
    }

    public function overrideAllowedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'override_allowed_by');
    }

    public function hasValidOverride(): bool
    {
        if (! $this->override_allowed) {
            return false;
        }

        if (! $this->override_expires_at) {
            return false;
        }

        return now()->lessThanOrEqualTo($this->override_expires_at);
    }
}
