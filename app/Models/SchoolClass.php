<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = ['name', 'is_active', 'homeroom_teacher_id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'homeroom_teacher_id');
    }

    public function attendanceSessions(): HasMany
    {
        return $this->hasMany(ClassAttendanceSession::class, 'class_id');
    }

    public function teachingSchedules(): HasMany
    {
        return $this->hasMany(TeachingSchedule::class, 'class_id');
    }

    public function teachingModules(): HasMany
    {
        return $this->hasMany(TeachingModule::class, 'class_id');
    }
}
