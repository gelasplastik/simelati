<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherLeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'date_from',
        'date_to',
        'reason',
        'affects_teaching_schedule',
        'proposed_substitute_teacher_id',
        'coverage_notes',
        'attachment_path',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'reviewed_at' => 'datetime',
        'affects_teaching_schedule' => 'boolean',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function proposedSubstituteTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'proposed_substitute_teacher_id');
    }

    public function substituteAssignments(): HasMany
    {
        return $this->hasMany(TeacherSubstituteAssignment::class, 'leave_id');
    }
}
