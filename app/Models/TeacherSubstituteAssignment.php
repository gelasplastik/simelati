<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherSubstituteAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_id',
        'original_teacher_id',
        'substitute_teacher_id',
        'class_id',
        'subject_id',
        'date',
        'schedule_profile_id',
        'jam_ke',
        'substitution_type',
        'status',
        'notes',
        'approved_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function leave(): BelongsTo
    {
        return $this->belongsTo(TeacherLeaveRequest::class, 'leave_id');
    }

    public function originalTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'original_teacher_id');
    }

    public function substituteTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'substitute_teacher_id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function scheduleProfile(): BelongsTo
    {
        return $this->belongsTo(ScheduleProfile::class, 'schedule_profile_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
