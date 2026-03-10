<?php

namespace App\Domain\Permissions;

use App\Models\Student;
use App\Models\StudentPermission;
use Carbon\Carbon;

class PermissionService
{
    public function resolveStudentPermission(Student $student, string $date): ?StudentPermission
    {
        $targetDate = Carbon::parse($date)->toDateString();

        return StudentPermission::query()
            ->where('student_id', $student->id)
            ->where('status', 'approved')
            ->whereDate('date_from', '<=', $targetDate)
            ->whereDate('date_to', '>=', $targetDate)
            ->latest('id')
            ->first();
    }
}
