<?php

namespace Tests\Feature;

use App\Models\AdminNotificationState;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\ClassAttendance;
use App\Models\ClassAttendanceSession;
use App\Models\ScheduleProfile;
use App\Models\SchoolClass;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherLeaveRequest;
use App\Models\TeacherSubstituteAssignment;
use App\Models\TeachingSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Step1TeacherLeaveCoverageTest extends TestCase
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

    private function makeClass(string $name): SchoolClass
    {
        return SchoolClass::query()->create([
            'name' => $name,
            'is_active' => true,
        ]);
    }

    private function ensureProfile(string $code, string $name, bool $active): ScheduleProfile
    {
        $profile = ScheduleProfile::query()->firstOrCreate(
            ['code' => $code],
            ['name' => $name, 'is_active' => $active]
        );

        $profile->update(['name' => $name, 'is_active' => $active]);

        if ($active) {
            ScheduleProfile::query()->where('id', '!=', $profile->id)->update(['is_active' => false]);
        }

        return $profile->fresh();
    }

    protected function setUp(): void
    {
        parent::setUp();
        Setting::active();
        AdminNotificationState::query()->delete();
    }

    public function test_leave_approval_with_affects_schedule_creates_coverage_for_active_profile_only(): void
    {
        $admin = $this->makeUser('Admin', 'admin-step1@example.test', 'admin');
        $original = $this->makeTeacher('Guru Asli', 'guru-asli@example.test', 'TS001');
        $classNormal = $this->makeClass('X-A');
        $classRamadhan = $this->makeClass('X-B');
        $subject = Subject::query()->create(['name' => 'Mapel Uji']);

        Assignment::query()->create([
            'teacher_id' => $original->id,
            'class_id' => $classNormal->id,
            'subject_id' => $subject->id,
        ]);
        Assignment::query()->create([
            'teacher_id' => $original->id,
            'class_id' => $classRamadhan->id,
            'subject_id' => $subject->id,
        ]);

        $normal = $this->ensureProfile('normal', 'Normal', true);
        $ramadhan = $this->ensureProfile('ramadhan', 'Ramadhan', false);

        $day = app(\App\Domain\MasterData\TeachingScheduleService::class)->resolveDayOfWeek(now());
        TeachingSchedule::query()->create([
            'schedule_profile_id' => $normal->id,
            'teacher_id' => $original->id,
            'class_id' => $classNormal->id,
            'subject_id' => $subject->id,
            'day_of_week' => $day,
            'jam_ke' => 1,
            'is_active' => true,
        ]);
        TeachingSchedule::query()->create([
            'schedule_profile_id' => $ramadhan->id,
            'teacher_id' => $original->id,
            'class_id' => $classRamadhan->id,
            'subject_id' => $subject->id,
            'day_of_week' => $day,
            'jam_ke' => 1,
            'is_active' => true,
        ]);

        $scheduleCountBefore = TeachingSchedule::query()->count();

        $leave = TeacherLeaveRequest::query()->create([
            'teacher_id' => $original->id,
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
            'reason' => 'Izin uji',
            'affects_teaching_schedule' => true,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->patch(route('admin.teacher-leave-requests.review', $leave), [
            'status' => 'approved',
            'review_notes' => 'ok',
        ])->assertRedirect();

        $this->assertDatabaseHas('teacher_substitute_assignments', [
            'leave_id' => $leave->id,
            'original_teacher_id' => $original->id,
            'class_id' => $classNormal->id,
            'subject_id' => $subject->id,
            'jam_ke' => 1,
            'schedule_profile_id' => $normal->id,
        ]);

        $this->assertDatabaseMissing('teacher_substitute_assignments', [
            'leave_id' => $leave->id,
            'class_id' => $classRamadhan->id,
            'schedule_profile_id' => $ramadhan->id,
        ]);

        $this->assertSame($scheduleCountBefore, TeachingSchedule::query()->count(), 'Permanent schedules must stay unchanged.');
    }

    public function test_sync_detected_sessions_is_idempotent_and_prevents_duplicates(): void
    {
        $original = $this->makeTeacher('Guru A', 'guru-a@example.test', 'TS002');
        $class = $this->makeClass('X-C');
        $subject = Subject::query()->create(['name' => 'Mapel Uji 2']);
        Assignment::query()->create([
            'teacher_id' => $original->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
        ]);

        $profile = $this->ensureProfile('normal', 'Normal', true);

        $day = app(\App\Domain\MasterData\TeachingScheduleService::class)->resolveDayOfWeek(now());
        TeachingSchedule::query()->create([
            'schedule_profile_id' => $profile->id,
            'teacher_id' => $original->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'day_of_week' => $day,
            'jam_ke' => 2,
            'is_active' => true,
        ]);

        $leave = TeacherLeaveRequest::query()->create([
            'teacher_id' => $original->id,
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
            'reason' => 'izin',
            'affects_teaching_schedule' => true,
            'status' => 'approved',
        ]);

        $service = app(\App\Domain\Attendance\LeaveCoverageService::class);
        $service->syncDetectedSessions($leave);
        $service->syncDetectedSessions($leave);

        $count = TeacherSubstituteAssignment::query()->where('leave_id', $leave->id)->count();
        $this->assertSame(1, $count);
    }

    public function test_substitute_validation_rules_are_enforced(): void
    {
        $admin = $this->makeUser('Admin', 'admin2@example.test', 'admin');
        $original = $this->makeTeacher('Guru Origin', 'guru-origin@example.test', 'TS003');
        $substitute = $this->makeTeacher('Guru Sub', 'guru-sub@example.test', 'TS004');
        $otherSubstitute = $this->makeTeacher('Guru Sub2', 'guru-sub2@example.test', 'TS005');
        $class = $this->makeClass('X-D');
        $subject = Subject::query()->create(['name' => 'Mapel Uji 3']);

        $profile = $this->ensureProfile('normal', 'Normal', true);

        $assignment = TeacherSubstituteAssignment::query()->create([
            'leave_id' => TeacherLeaveRequest::query()->create([
                'teacher_id' => $original->id,
                'date_from' => now()->toDateString(),
                'date_to' => now()->toDateString(),
                'reason' => 'izin',
                'affects_teaching_schedule' => true,
                'status' => 'approved',
            ])->id,
            'original_teacher_id' => $original->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'date' => now()->toDateString(),
            'schedule_profile_id' => $profile->id,
            'jam_ke' => 3,
            'substitution_type' => 'substitute_teacher',
            'status' => 'pending',
        ]);

        $service = app(\App\Domain\Attendance\LeaveCoverageService::class);

        try {
            $service->assignSubstitute($assignment, $original, $admin->id);
            $this->fail('Expected self-substitute validation to fail');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('tidak boleh sama', $e->getMessage());
        }

        TeacherLeaveRequest::query()->create([
            'teacher_id' => $substitute->id,
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
            'reason' => 'sedang izin',
            'status' => 'approved',
        ]);

        try {
            $service->assignSubstitute($assignment, $substitute, $admin->id);
            $this->fail('Expected substitute-on-leave validation to fail');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('sedang izin', $e->getMessage());
        }

        TeacherLeaveRequest::query()->where('teacher_id', $substitute->id)->delete();

        TeachingSchedule::query()->create([
            'schedule_profile_id' => $profile->id,
            'teacher_id' => $substitute->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'day_of_week' => app(\App\Domain\MasterData\TeachingScheduleService::class)->resolveDayOfWeek(now()),
            'jam_ke' => 3,
            'is_active' => true,
        ]);

        try {
            $service->assignSubstitute($assignment, $substitute, $admin->id);
            $this->fail('Expected schedule-conflict validation to fail');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('konflik jadwal', $e->getMessage());
        }

        TeachingSchedule::query()->where('teacher_id', $substitute->id)->delete();

        $conflictClass = $this->makeClass('X-D2');
        $conflictSubject = Subject::query()->create(['name' => 'Mapel Uji 3B']);

        TeacherSubstituteAssignment::query()->create([
            'leave_id' => TeacherLeaveRequest::query()->create([
                'teacher_id' => $original->id,
                'date_from' => now()->toDateString(),
                'date_to' => now()->toDateString(),
                'reason' => 'izin 2',
                'status' => 'approved',
            ])->id,
            'original_teacher_id' => $original->id,
            'substitute_teacher_id' => $substitute->id,
            'class_id' => $conflictClass->id,
            'subject_id' => $conflictSubject->id,
            'date' => now()->toDateString(),
            'schedule_profile_id' => $profile->id,
            'jam_ke' => 3,
            'substitution_type' => 'substitute_teacher',
            'status' => 'assigned',
        ]);

        try {
            $service->assignSubstitute($assignment, $substitute, $admin->id);
            $this->fail('Expected substitute-task-conflict validation to fail');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('sesi pengganti lain', $e->getMessage());
        }

        $service->assignSubstitute($assignment, $otherSubstitute, $admin->id);
        $this->assertSame('assigned', $assignment->fresh()->status);
        $this->assertSame($otherSubstitute->id, $assignment->fresh()->substitute_teacher_id);
    }

    public function test_substitute_can_fill_attendance_and_traceability_is_stored_and_journal_requires_attendance(): void
    {
        $original = $this->makeTeacher('Guru Origin 2', 'guru-origin2@example.test', 'TS006');
        $substitute = $this->makeTeacher('Guru Substitute 2', 'guru-substitute2@example.test', 'TS007');
        $class = $this->makeClass('X-E');
        $subject = Subject::query()->create(['name' => 'Mapel Uji 4']);
        $student = Student::query()->create([
            'nis' => 'NIS-STEP1-001',
            'full_name' => 'Siswa Uji',
            'class_id' => $class->id,
            'is_active' => true,
        ]);

        $profile = $this->ensureProfile('normal', 'Normal', true);

        Assignment::query()->create([
            'teacher_id' => $original->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
        ]);

        $leave = TeacherLeaveRequest::query()->create([
            'teacher_id' => $original->id,
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
            'reason' => 'izin',
            'affects_teaching_schedule' => true,
            'status' => 'approved',
        ]);

        $subAssignment = TeacherSubstituteAssignment::query()->create([
            'leave_id' => $leave->id,
            'original_teacher_id' => $original->id,
            'substitute_teacher_id' => $substitute->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'date' => now()->toDateString(),
            'schedule_profile_id' => $profile->id,
            'jam_ke' => 1,
            'substitution_type' => 'substitute_teacher',
            'status' => 'assigned',
        ]);

        Attendance::query()->create([
            'teacher_id' => $substitute->id,
            'date' => now()->toDateString(),
            'checkin_at' => now(),
            'checkin_lat' => -6.2,
            'checkin_lng' => 106.8,
            'checkin_accuracy' => 10,
            'checkin_distance' => 0,
            'is_late' => false,
        ]);

        $this->actingAs($substitute->user)
            ->get(route('teacher.class-attendance.index', ['substitute_assignment_id' => $subAssignment->id]))
            ->assertOk();

        $this->actingAs($substitute->user)
            ->post(route('teacher.class-attendance.store'), [
                'substitute_assignment_id' => $subAssignment->id,
                'class_id' => $class->id,
                'subject_id' => $subject->id,
                'date' => now()->toDateString(),
                'jam_ke' => 1,
                'rows' => [
                    ['student_id' => $student->id, 'status' => 'hadir', 'note' => null],
                ],
            ])->assertRedirect();

        $session = ClassAttendanceSession::query()
            ->where('teacher_id', $original->id)
            ->where('class_id', $class->id)
            ->where('subject_id', $subject->id)
            ->whereDate('date', now()->toDateString())
            ->where('jam_ke', 1)
            ->firstOrFail();

        $this->assertSame($original->id, $session->original_teacher_id);
        $this->assertSame($substitute->id, $session->executing_teacher_id);

        $emptySession = ClassAttendanceSession::query()->create([
            'teacher_id' => $original->id,
            'original_teacher_id' => $original->id,
            'executing_teacher_id' => $substitute->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'date' => now()->toDateString(),
            'jam_ke' => 2,
        ]);

        $this->actingAs($substitute->user)
            ->get(route('teacher.teaching-journals.create', $emptySession))
            ->assertStatus(422);

        ClassAttendance::query()->create([
            'session_id' => $emptySession->id,
            'student_id' => $student->id,
            'status' => 'hadir',
        ]);

        $this->actingAs($substitute->user)
            ->get(route('teacher.teaching-journals.create', $emptySession))
            ->assertOk();
    }
}
