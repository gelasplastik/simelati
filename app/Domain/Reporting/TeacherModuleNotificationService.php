<?php

namespace App\Domain\Reporting;

use App\Models\Teacher;
use App\Models\TeachingModule;

class TeacherModuleNotificationService
{
    public function buildForTeacher(Teacher $teacher): array
    {
        $base = TeachingModule::query()->where('teacher_id', $teacher->id);

        $submittedCount = (clone $base)->where('status', TeachingModule::STATUS_SUBMITTED)->count();
        $approvedCount = (clone $base)->where('status', TeachingModule::STATUS_APPROVED)->count();
        $rejectedCount = (clone $base)->where('status', TeachingModule::STATUS_REJECTED)->count();

        $recentRejected = TeachingModule::query()
            ->where('teacher_id', $teacher->id)
            ->where('status', TeachingModule::STATUS_REJECTED)
            ->latest('updated_at')
            ->take(5)
            ->get(['id', 'title', 'admin_notes', 'updated_at']);

        return [
            'submitted_count' => (int) $submittedCount,
            'approved_count' => (int) $approvedCount,
            'rejected_count' => (int) $rejectedCount,
            'attention_count' => (int) $rejectedCount,
            'recent_rejected' => $recentRejected,
            'modules_route' => route('teacher.modules.index'),
        ];
    }
}
