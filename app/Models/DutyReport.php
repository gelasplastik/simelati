<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DutyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'duty_teacher_id',
        'status',
        'notes',
        'finalized_at',
        'finalized_by',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'finalized_at' => 'datetime',
    ];

    public function dutyTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'duty_teacher_id');
    }

    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function teacherRows(): HasMany
    {
        return $this->hasMany(DutyReportTeacherRow::class, 'report_id');
    }

    public function studentRows(): HasMany
    {
        return $this->hasMany(DutyReportStudentRow::class, 'report_id');
    }

    public function isFinalized(): bool
    {
        return $this->status === 'finalized';
    }
}
