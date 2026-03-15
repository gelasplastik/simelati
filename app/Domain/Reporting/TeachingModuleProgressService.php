<?php

namespace App\Domain\Reporting;

use App\Models\Assignment;
use App\Models\Teacher;
use App\Models\TeachingModule;

class TeachingModuleProgressService
{
    public function summaryForTeacher(Teacher $teacher): array
    {
        $totalAssigned = Assignment::query()
            ->where('teacher_id', $teacher->id)
            ->distinct()
            ->count('id');

        $baseQuery = TeachingModule::query()->where('teacher_id', $teacher->id);

        $uploadedCount = (clone $baseQuery)->count();
        $approvedCount = (clone $baseQuery)->where('status', TeachingModule::STATUS_APPROVED)->count();
        $completionCount = (clone $baseQuery)
            ->whereIn('status', [TeachingModule::STATUS_SUBMITTED, TeachingModule::STATUS_APPROVED])
            ->count();

        $completionPercentage = $totalAssigned > 0
            ? round(($completionCount / $totalAssigned) * 100, 2)
            : 0;

        return [
            'total_assigned' => $totalAssigned,
            'uploaded_count' => $uploadedCount,
            'approved_count' => $approvedCount,
            'completion_count' => $completionCount,
            'completion_percentage' => $completionPercentage,
        ];
    }
}
