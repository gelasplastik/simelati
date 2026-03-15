<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'employee_code', 'homeroom_class_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function classAttendanceSessions(): HasMany
    {
        return $this->hasMany(ClassAttendanceSession::class);
    }

    public function teachingJournals(): HasMany
    {
        return $this->hasMany(TeachingJournal::class);
    }

    public function teachingModules(): HasMany
    {
        return $this->hasMany(TeachingModule::class);
    }

    public function homeroomClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'homeroom_teacher_id');
    }

    public function homeroomClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'homeroom_class_id');
    }

    public function lateEntryRequests(): HasMany
    {
        return $this->hasMany(LateEntryRequest::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(TeacherLeaveRequest::class);
    }

    public function teachingSchedules(): HasMany
    {
        return $this->hasMany(TeachingSchedule::class);
    }

    public function substituteAssignmentsReceived(): HasMany
    {
        return $this->hasMany(TeacherSubstituteAssignment::class, 'substitute_teacher_id');
    }

    public function substituteAssignmentsAsOriginal(): HasMany
    {
        return $this->hasMany(TeacherSubstituteAssignment::class, 'original_teacher_id');
    }

    public function dutyAssignments(): HasMany
    {
        return $this->hasMany(DutyTeacherAssignment::class);
    }

    public function dutyReportsAsPic(): HasMany
    {
        return $this->hasMany(DutyReport::class, 'duty_teacher_id');
    }
}
