<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\ClassAttendance;
use App\Models\ClassAttendanceSession;
use App\Models\StudentPermission;
use App\Models\Teacher;
use App\Models\TeachingSchedule;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_core_pages_render_successfully(): void
    {
        $admin = User::query()->where('email', 'admin@sdplusmelati.local')->firstOrFail();
        $this->actingAs($admin);

        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.classes.index'))->assertOk();
        $this->get(route('admin.teachers.index'))->assertOk();
        $this->get(route('admin.assignments.index'))->assertOk();
        $this->get(route('admin.settings.edit'))->assertOk();
        $this->get(route('admin.permissions.index'))->assertOk();
        $this->get(route('admin.reports.attendance'))->assertOk();
        $this->get(route('admin.reports.journals'))->assertOk();
        $this->get(route('admin.reports.class-attendance'))->assertOk();
        $this->get(route('admin.reports.kpi'))->assertOk();
        $this->get(route('admin.overrides.index'))->assertOk();
        $this->get(route('admin.teacher-leave-requests.index'))->assertOk();
        $this->get(route('admin.late-entry-requests.index'))->assertOk();
        $this->get(route('admin.teaching-schedules.index'))->assertOk();
        $this->get(route('admin.reports.student-attendance-recap'))->assertOk();
    }

    public function test_teacher_restricted_before_checkin_then_can_access_workflow_after_checkin(): void
    {
        $teacherUser = User::query()->where('email', 'teacher1@sdplusmelati.local')->firstOrFail();
        $teacher = $teacherUser->teacher;
        $assignment = Assignment::query()->where('teacher_id', $teacher->id)->firstOrFail();

        TeachingSchedule::query()->updateOrCreate([
            'teacher_id' => $teacher->id,
            'class_id' => $assignment->class_id,
            'subject_id' => $assignment->subject_id,
            'day_of_week' => app(\App\Domain\MasterData\TeachingScheduleService::class)->resolveDayOfWeek(now()),
            'jam_ke' => 1,
        ], [
            'is_active' => true,
        ]);

        $this->actingAs($teacherUser);

        $this->get(route('teacher.class-attendance.index'))
            ->assertRedirect(route('teacher.dashboard'));

        Attendance::query()->updateOrCreate([
            'teacher_id' => $teacher->id,
            'date' => now()->toDateString(),
        ], [
            'checkin_at' => now(),
            'checkin_lat' => -6.2,
            'checkin_lng' => 106.8,
            'checkin_accuracy' => 10,
            'checkin_distance' => 0,
            'is_late' => false,
        ]);

        $this->get(route('teacher.dashboard'))->assertOk();
        $this->get(route('teacher.class-attendance.index', [
            'class_id' => $assignment->class_id,
            'subject_id' => $assignment->subject_id,
            'date' => now()->toDateString(),
            'jam_ke' => 1,
        ]))->assertOk();

        $this->assertStringContainsString(
            'bi-bell-fill',
            $this->get(route('teacher.dashboard'))->getContent()
        );

        $session = ClassAttendanceSession::query()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $assignment->class_id,
            'subject_id' => $assignment->subject_id,
            'date' => now()->toDateString(),
            'jam_ke' => 1,
        ]);

        $student = \App\Models\Student::query()
            ->where('class_id', $assignment->class_id)
            ->where('is_active', true)
            ->firstOrFail();
        ClassAttendance::query()->create([
            'session_id' => $session->id,
            'student_id' => $student->id,
            'status' => 'hadir',
        ]);

        $this->get(route('teacher.teaching-journals.create', $session))->assertOk();
    }

    public function test_parent_permission_pages_and_store_workflow_work(): void
    {
        $parentUser = User::query()->where('email', 'parent1@sdplusmelati.local')->firstOrFail();
        $parent = $parentUser->parentProfile;
        $student = $parent->students()->firstOrFail();

        $this->actingAs($parentUser);
        $this->get(route('parent.dashboard'))->assertOk();
        $this->get(route('parent.permissions.index'))->assertOk();
        $this->get(route('parent.permissions.create'))->assertOk();

        $before = StudentPermission::count();
        $this->post(route('parent.permissions.store'), [
            'student_id' => $student->id,
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
            'reason' => 'Smoke test izin siswa',
        ])->assertRedirect(route('parent.permissions.index'));
        $this->assertSame($before + 1, StudentPermission::count());
    }

    public function test_role_access_restrictions_work_for_core_routes(): void
    {
        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $teacher = User::query()->where('role', 'teacher')->firstOrFail();
        $parent = User::query()->where('role', 'parent')->firstOrFail();

        $this->actingAs($parent);
        $this->get(route('admin.dashboard'))->assertForbidden();

        $this->actingAs($teacher);
        $this->get(route('parent.dashboard'))->assertForbidden();

        $this->actingAs($admin);
        $this->get(route('teacher.dashboard'))->assertForbidden();
    }
}
