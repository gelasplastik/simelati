<?php

namespace Tests\Feature;

use App\Domain\DutyMonitoring\DutyMonitoringService;
use App\Domain\MasterData\TeachingScheduleService;
use App\Models\AdminNotificationState;
use App\Models\Attendance;
use App\Models\ClassAttendance;
use App\Models\ClassAttendanceSession;
use App\Models\DutyReport;
use App\Models\DutyTeacherAssignment;
use App\Models\ScheduleProfile;
use App\Models\SchoolClass;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentPermission;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherLeaveRequest;
use App\Models\TeacherSubstituteAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Step3DutyTeacherMonitoringTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $name, string $email, string $role): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }

    private function makeTeacher(string $name, string $email, string $code): Teacher
    {
        $user = $this->makeUser($name, $email, 'teacher');

        return Teacher::query()->create([
            'user_id' => $user->id,
            'employee_code' => $code,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-03-16 08:00:00')); // Monday for stable duty-weekday tests
        Setting::active();
        AdminNotificationState::query()->delete();
        ScheduleProfile::query()->updateOrCreate(
            ['code' => 'normal'],
            ['name' => 'Normal', 'is_active' => true]
        );
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_admin_can_assign_duty_teacher_and_duplicate_is_prevented(): void
    {
        $admin = $this->makeUser('Admin', 'admin-duty@example.test', 'admin');
        $teacher = $this->makeTeacher('Guru Piket', 'guru-piket@example.test', 'DP001');

        $this->actingAs($admin)->post(route('admin.duty-assignments.store'), [
            'day_of_week' => 'monday',
            'teacher_id' => $teacher->id,
            'notes' => 'Piket pagi',
        ])->assertRedirect();

        $this->assertDatabaseHas('duty_teacher_assignments', [
            'day_of_week' => 'monday',
            'teacher_id' => $teacher->id,
        ]);

        $this->actingAs($admin)->post(route('admin.duty-assignments.store'), [
            'day_of_week' => 'monday',
            'teacher_id' => $teacher->id,
        ])->assertSessionHasErrors('teacher_id');
    }

    public function test_assigned_teacher_can_finalize_report_and_cannot_edit_after_finalized(): void
    {
        $teacher = $this->makeTeacher('Guru Piket Aktif', 'guru-piket-aktif@example.test', 'DP002');

        DutyTeacherAssignment::query()->create([
            'day_of_week' => 'monday',
            'teacher_id' => $teacher->id,
        ]);

        $this->actingAs($teacher->user)
            ->get(route('teacher.duty.dashboard'))
            ->assertOk();

        $report = DutyReport::query()->whereDate('date', now()->toDateString())->firstOrFail();

        $this->actingAs($teacher->user)
            ->patch(route('teacher.duty.report.update', $report), [
                'action' => 'finalize',
                'notes' => 'Semua terkendali.',
            ])->assertRedirect();

        $this->assertSame('finalized', $report->fresh()->status);

        $this->actingAs($teacher->user)
            ->patch(route('teacher.duty.report.update', $report), [
                'action' => 'save',
                'notes' => 'coba ubah lagi',
            ])->assertRedirect();

        $this->assertSame('Semua terkendali.', $report->fresh()->notes);
    }

    public function test_non_assigned_teacher_cannot_open_duty_report_edit_page(): void
    {
        $assignedTeacher = $this->makeTeacher('Guru Piket 1', 'guru-piket1@example.test', 'DP003');
        $otherTeacher = $this->makeTeacher('Guru Biasa', 'guru-biasa@example.test', 'DP004');

        DutyTeacherAssignment::query()->create([
            'day_of_week' => 'monday',
            'teacher_id' => $assignedTeacher->id,
        ]);

        $this->actingAs($assignedTeacher->user)
            ->get(route('teacher.duty.dashboard'))
            ->assertOk();

        $report = DutyReport::query()->whereDate('date', now()->toDateString())->firstOrFail();

        $this->actingAs($otherTeacher->user)
            ->get(route('teacher.duty.report.edit', $report))
            ->assertRedirect(route('teacher.duty.dashboard'));
    }

    public function test_dashboard_summary_counts_teacher_and_student_and_substitute_coverage_correctly(): void
    {
        $dutyTeacher = $this->makeTeacher('Guru Piket 2', 'guru-piket2@example.test', 'DP005');
        $presentTeacher = $this->makeTeacher('Guru Hadir', 'guru-hadir@example.test', 'DP006');
        $leaveTeacher = $this->makeTeacher('Guru Izin', 'guru-izin@example.test', 'DP007');
        $absentTeacher = $this->makeTeacher('Guru Alpa', 'guru-alpa@example.test', 'DP008');
        $substituteTeacher = $this->makeTeacher('Guru Pengganti', 'guru-pengganti@example.test', 'DP009');

        $class = SchoolClass::query()->create(['name' => '4A', 'is_active' => true]);
        $subject = Subject::query()->create(['name' => 'Matematika']);

        Student::query()->create(['nis' => 'NIS-DUTY-001', 'full_name' => 'Siswa 1', 'class_id' => $class->id, 'is_active' => true]);
        Student::query()->create(['nis' => 'NIS-DUTY-002', 'full_name' => 'Siswa 2', 'class_id' => $class->id, 'is_active' => true]);

        DutyTeacherAssignment::query()->create(['day_of_week' => 'monday', 'teacher_id' => $dutyTeacher->id]);

        Attendance::query()->create([
            'teacher_id' => $presentTeacher->id,
            'date' => now()->toDateString(),
            'checkin_at' => now(),
            'checkin_lat' => -6.2,
            'checkin_lng' => 106.8,
            'checkin_accuracy' => 10,
            'checkin_distance' => 0,
            'is_late' => false,
        ]);

        TeacherLeaveRequest::query()->create([
            'teacher_id' => $leaveTeacher->id,
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
            'reason' => 'Sakit',
            'status' => 'approved',
        ]);

        $profile = ScheduleProfile::query()->where('code', 'normal')->firstOrFail();

        TeacherSubstituteAssignment::query()->create([
            'leave_id' => TeacherLeaveRequest::query()->create([
                'teacher_id' => $absentTeacher->id,
                'date_from' => now()->toDateString(),
                'date_to' => now()->toDateString(),
                'reason' => 'Dinas',
                'status' => 'pending',
            ])->id,
            'original_teacher_id' => $absentTeacher->id,
            'substitute_teacher_id' => $substituteTeacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'date' => now()->toDateString(),
            'schedule_profile_id' => $profile->id,
            'jam_ke' => 1,
            'substitution_type' => 'substitute_teacher',
            'status' => 'assigned',
        ]);

        ClassAttendanceSession::query()->create([
            'teacher_id' => $presentTeacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'date' => now()->toDateString(),
            'jam_ke' => 1,
        ]);

        $session = ClassAttendanceSession::query()->firstOrFail();
        $students = Student::query()->get();

        ClassAttendance::query()->create(['session_id' => $session->id, 'student_id' => $students[0]->id, 'status' => 'hadir', 'note' => null]);
        ClassAttendance::query()->create(['session_id' => $session->id, 'student_id' => $students[1]->id, 'status' => 'alpa', 'note' => 'Telat datang']);

        $summary = app(DutyMonitoringService::class)->buildDashboard(now()->toDateString());

        $this->assertSame(5, $summary['teacher']['total_teachers']);
        $this->assertSame(1, $summary['teacher']['present_teachers']);
        $this->assertSame(1, $summary['teacher']['leave_teachers']);
        $this->assertSame(3, $summary['teacher']['absent_teachers']);
        $this->assertSame(1, $summary['teacher']['covered_absent_teachers']);

        $this->assertSame(1, $summary['student']['total_classes']);
        $this->assertSame(2, $summary['student']['total_students']);
        $this->assertSame(1, $summary['student']['present_students']);
        $this->assertSame(1, $summary['student']['alpa_students']);
        $this->assertSame(1, $summary['student']['late_students']);
        $this->assertNotEmpty($summary['classes_need_attention']);

        $this->actingAs($dutyTeacher->user)
            ->get(route('teacher.duty.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Guru Piket');
    }

    public function test_assigned_teacher_can_save_draft_and_admin_can_access_archive_and_print(): void
    {
        $admin = $this->makeUser('Admin Arsip', 'admin-arsip@example.test', 'admin');
        $dutyTeacher = $this->makeTeacher('Guru Piket 3', 'guru-piket3@example.test', 'DP010');

        DutyTeacherAssignment::query()->create(['day_of_week' => 'monday', 'teacher_id' => $dutyTeacher->id]);

        $this->actingAs($dutyTeacher->user)
            ->get(route('teacher.duty.dashboard'))
            ->assertOk();

        $report = DutyReport::query()->whereDate('date', now()->toDateString())->firstOrFail();

        $teacherRows = $report->teacherRows()->get()->map(fn ($row) => [
            'id' => $row->id,
            'verified_status' => 'present',
            'reason' => 'Terverifikasi hadir',
            'has_substitute' => false,
            'notes' => null,
        ])->values()->all();

        $studentRows = $report->studentRows()->get()->map(fn ($row) => [
            'id' => $row->id,
            'total_students' => $row->total_students,
            'present_count' => $row->present_count,
            'sick_count' => $row->sick_count,
            'izin_count' => $row->izin_count,
            'alpa_count' => $row->alpa_count,
            'late_count' => $row->late_count,
            'notes' => 'Aman',
        ])->values()->all();

        $this->actingAs($dutyTeacher->user)
            ->patch(route('teacher.duty.report.update', $report), [
                'action' => 'save',
                'notes' => 'Catatan draft piket',
                'teacher_rows' => $teacherRows,
                'student_rows' => $studentRows,
            ])->assertRedirect();

        $this->assertSame('draft', $report->fresh()->status);
        $this->assertSame('Catatan draft piket', $report->fresh()->notes);

        $this->actingAs($admin)
            ->get(route('admin.duty-reports.index'))
            ->assertOk()
            ->assertSee('Arsip Laporan Piket Harian');

        $this->actingAs($admin)
            ->get(route('admin.duty-reports.show', $report))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.duty-reports.print', $report))
            ->assertOk()
            ->assertSee('LAPORAN HARIAN GURU PIKET');
    }
    public function test_student_permission_is_reflected_in_duty_student_summary_even_before_class_attendance_saved(): void
    {
        $class = SchoolClass::query()->create(['name' => '2A', 'is_active' => true]);

        $student1 = Student::query()->create([
            'nis' => 'NIS-PERM-001',
            'nisn' => 'NISN-PERM-001',
            'full_name' => 'Siswa Izin 1',
            'class_id' => $class->id,
            'is_active' => true,
        ]);

        Student::query()->create([
            'nis' => 'NIS-PERM-002',
            'nisn' => 'NISN-PERM-002',
            'full_name' => 'Siswa Hadir 1',
            'class_id' => $class->id,
            'is_active' => true,
        ]);

        StudentPermission::query()->create([
            'student_id' => $student1->id,
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
            'reason' => 'Izin orang tua',
            'status' => 'submitted',
        ]);

        $summary = app(DutyMonitoringService::class)->buildDashboard(now()->toDateString());
        $classRow = collect($summary['student']['rows'])->firstWhere('class_id', $class->id);

        $this->assertNotNull($classRow);
        $this->assertSame(2, $classRow['total_students']);
        $this->assertSame(1, $classRow['izin_count']);
        $this->assertSame(1, $classRow['present_count']);
    }
}
