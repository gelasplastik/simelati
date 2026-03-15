<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DutyReportTeacherRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'teacher_id',
        'teacher_name',
        'subject_label',
        'attendance_status',
        'verified_status',
        'reason',
        'has_substitute',
        'substitute_teacher_id',
        'substitute_teacher_name',
        'notes',
    ];

    protected $casts = [
        'has_substitute' => 'boolean',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(DutyReport::class, 'report_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function substituteTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'substitute_teacher_id');
    }
}
