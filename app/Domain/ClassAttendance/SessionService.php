<?php

namespace App\Domain\ClassAttendance;

use App\Models\ClassAttendanceSession;

class SessionService
{
    public function getOrCreate(array $attributes): ClassAttendanceSession
    {
        return ClassAttendanceSession::query()->updateOrCreate([
            'teacher_id' => $attributes['teacher_id'],
            'class_id' => $attributes['class_id'],
            'subject_id' => $attributes['subject_id'] ?? null,
            'date' => $attributes['date'],
        ]);
    }
}
