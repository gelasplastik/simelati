<?php

namespace App\Domain\Reporting;

use App\Models\AdminNotificationState;
use App\Models\DutyReportTeacherRow;
use App\Models\LateEntryRequest;
use App\Models\StudentPermission;
use App\Models\TeacherLeaveRequest;
use App\Models\TeachingModule;
use App\Models\User;
use Carbon\Carbon;

class AdminNotificationService
{
    public function buildForUser(User $user): ?array
    {
        if ($user->role !== 'admin') {
            return null;
        }

        $state = AdminNotificationState::query()->firstOrCreate([
            'user_id' => $user->id,
        ]);

        $lastReadAt = $state->last_read_at;
        $today = Carbon::now('Asia/Makassar')->toDateString();

        $items = collect([
            $this->makeItem(
                key: 'teacher_leave',
                label: 'Izin Guru Pending',
                icon: 'bi-calendar2-check',
                route: route('admin.teacher-leave-requests.index'),
                query: TeacherLeaveRequest::query()->where('status', 'pending'),
                lastReadAt: $lastReadAt
            ),
            $this->makeItem(
                key: 'student_permission',
                label: 'Izin Siswa Submitted',
                icon: 'bi-people',
                route: route('admin.permissions.index'),
                query: StudentPermission::query()->where('status', 'submitted'),
                lastReadAt: $lastReadAt
            ),
            $this->makeItem(
                key: 'late_entry',
                label: 'Permintaan Absensi/Jurnal Pending',
                icon: 'bi-hourglass-split',
                route: route('admin.late-entry-requests.index'),
                query: LateEntryRequest::query()->where('status', 'pending'),
                lastReadAt: $lastReadAt
            ),
            $this->makeItem(
                key: 'teaching_module',
                label: 'Modul Ajar Menunggu Review',
                icon: 'bi-folder2-open',
                route: route('admin.teaching-modules.index', ['status' => 'submitted']),
                query: TeachingModule::query()->where('status', 'submitted'),
                lastReadAt: $lastReadAt
            ),
            $this->makeItem(
                key: 'duty_verification_gap',
                label: 'Verifikasi Hadir Piket (Belum Absen GPS)',
                icon: 'bi-exclamation-triangle',
                route: route('admin.duty-reports.index', ['date_from' => $today, 'date_to' => $today]),
                query: DutyReportTeacherRow::query()
                    ->select('duty_report_teacher_rows.*')
                    ->join('duty_reports', 'duty_reports.id', '=', 'duty_report_teacher_rows.report_id')
                    ->leftJoin('attendances', function ($join) {
                        $join->on('attendances.teacher_id', '=', 'duty_report_teacher_rows.teacher_id')
                            ->on('attendances.date', '=', 'duty_reports.date');
                    })
                    ->where('duty_report_teacher_rows.verified_status', 'present')
                    ->whereDate('duty_reports.date', $today)
                    ->whereNull('attendances.id'),
                lastReadAt: $lastReadAt
            ),
        ]);

        $totalUnread = (int) $items->sum('unread');
        $totalOpen = (int) $items->sum('count');

        return [
            'items' => $items,
            'total_unread' => $totalUnread,
            'total_open' => $totalOpen,
            'last_read_at' => $lastReadAt,
        ];
    }

    public function markAllRead(User $user): void
    {
        if ($user->role !== 'admin') {
            return;
        }

        AdminNotificationState::query()->updateOrCreate(
            ['user_id' => $user->id],
            ['last_read_at' => now()]
        );
    }

    private function makeItem(string $key, string $label, string $icon, string $route, $query, $lastReadAt): array
    {
        $count = (clone $query)->count();
        $table = $query->getModel()->getTable();
        $unread = $lastReadAt
            ? (clone $query)->where("{$table}.created_at", '>', $lastReadAt)->count()
            : $count;

        return [
            'key' => $key,
            'label' => $label,
            'icon' => $icon,
            'route' => $route,
            'count' => (int) $count,
            'unread' => (int) $unread,
        ];
    }
}
