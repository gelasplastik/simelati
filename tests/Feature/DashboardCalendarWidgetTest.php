<?php

namespace Tests\Feature;

use App\Domain\MasterData\DashboardCalendarService;
use App\Models\NationalHoliday;
use App\Models\ScheduleProfile;
use App\Models\SchoolCalendarEvent;
use App\Models\SchoolClass;
use App\Models\Setting;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardCalendarWidgetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::active();
    }

    private function makeUser(string $name, string $email, string $role): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }

    private function makeTeacher(string $name, string $email, string $code = 'T001'): Teacher
    {
        $user = $this->makeUser($name, $email, 'teacher');

        return Teacher::query()->create([
            'user_id' => $user->id,
            'employee_code' => $code,
        ]);
    }

    public function test_admin_dashboard_calendar_renders_and_month_navigation_is_safe(): void
    {
        $admin = $this->makeUser('Admin', 'admin-calendar@example.test', 'admin');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Kalender Bulanan Akademik');

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['calendar_year' => 2026, 'calendar_month' => 2]))
            ->assertOk()
            ->assertViewHas('calendarWidget', fn (array $widget) => $widget['year'] === 2026 && $widget['month'] === 2);
    }

    public function test_monthly_calendar_merges_school_events_national_holidays_and_multiday_events(): void
    {
        SchoolCalendarEvent::query()->create([
            'title' => 'Libur Semester Uji',
            'description' => null,
            'start_date' => '2026-03-10',
            'end_date' => '2026-03-12',
            'event_type' => 'semester_break',
            'operational_mode' => 'full_holiday',
            'color' => '#FF9800',
            'is_school_day' => false,
            'disable_teacher_attendance' => true,
            'disable_student_attendance' => true,
            'disable_journal' => true,
            'disable_substitute_generation' => true,
            'disable_kpi_penalty' => true,
            'show_on_dashboard' => true,
            'active' => true,
        ]);

        NationalHoliday::query()->create([
            'date' => '2026-03-17',
            'title' => 'Hari Libur Nasional Uji',
            'entry_type' => 'national_holiday',
            'is_national_holiday' => true,
            'is_collective_leave' => false,
            'source' => 'test',
            'source_payload' => ['from' => 'unit-test'],
        ]);

        $widget = app(DashboardCalendarService::class)->buildMonthly(null, 2026, 3);
        $cells = collect($widget['weeks'])->flatten(1);

        $march10 = $cells->first(fn (array $cell) => $cell['date']->toDateString() === '2026-03-10');
        $march12 = $cells->first(fn (array $cell) => $cell['date']->toDateString() === '2026-03-12');
        $march17 = $cells->first(fn (array $cell) => $cell['date']->toDateString() === '2026-03-17');

        $this->assertNotNull($march10);
        $this->assertNotNull($march12);
        $this->assertNotNull($march17);
        $this->assertTrue($march10['events']->contains(fn (array $event) => $event['title'] === 'Libur Semester Uji'));
        $this->assertTrue($march12['events']->contains(fn (array $event) => $event['title'] === 'Libur Semester Uji'));
        $this->assertTrue($march17['events']->contains(fn (array $event) => $event['title'] === 'Hari Libur Nasional Uji'));
    }

    public function test_teacher_calendar_shows_only_active_profile_schedules_on_correct_dates(): void
    {
        $teacher = $this->makeTeacher('Guru Uji', 'guru-calendar@example.test', 'TCAL01');
        $class = SchoolClass::query()->create(['name' => '6A', 'is_active' => true]);
        $subject = Subject::query()->create(['name' => 'Bahasa Inggris']);

        $normal = ScheduleProfile::query()->updateOrCreate(
            ['code' => 'normal'],
            ['name' => 'Normal', 'is_active' => true, 'description' => null]
        );

        $ramadhan = ScheduleProfile::query()->updateOrCreate(
            ['code' => 'ramadhan'],
            ['name' => 'Ramadhan', 'is_active' => false, 'description' => null]
        );

        ScheduleProfile::query()->where('id', '!=', $normal->id)->update(['is_active' => false]);

        TeachingSchedule::query()->create([
            'schedule_profile_id' => $normal->id,
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'day_of_week' => 'monday',
            'jam_ke' => 1,
            'start_time' => '07:30:00',
            'end_time' => '08:05:00',
            'is_active' => true,
        ]);

        TeachingSchedule::query()->create([
            'schedule_profile_id' => $ramadhan->id,
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'day_of_week' => 'tuesday',
            'jam_ke' => 2,
            'start_time' => '08:30:00',
            'end_time' => '09:00:00',
            'is_active' => true,
        ]);

        $widget = app(DashboardCalendarService::class)->buildMonthly($teacher, 2026, 3);
        $cells = collect($widget['weeks'])->flatten(1);

        $mondayCell = $cells->first(fn (array $cell) => $cell['date']->toDateString() === '2026-03-02');
        $tuesdayCell = $cells->first(fn (array $cell) => $cell['date']->toDateString() === '2026-03-03');

        $this->assertNotNull($mondayCell);
        $this->assertNotNull($tuesdayCell);
        $this->assertTrue($mondayCell['schedules']->contains(fn (array $schedule) => $schedule['subject'] === 'Bahasa Inggris' && $schedule['class'] === '6A'));
        $this->assertFalse($tuesdayCell['schedules']->contains(fn (array $schedule) => ($schedule['jam_ke'] ?? null) === 2));
    }

    public function test_teacher_dashboard_calendar_renders_even_without_schedules(): void
    {
        $teacher = $this->makeTeacher('Guru Kosong', 'guru-kosong@example.test', 'TCAL02');

        $this->actingAs($teacher->user)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('Kalender Bulanan Saya');
    }
}
