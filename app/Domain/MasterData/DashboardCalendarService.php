<?php

namespace App\Domain\MasterData;

use App\Models\NationalHoliday;
use App\Models\SchoolCalendarEvent;
use App\Models\Teacher;
use App\Models\TeachingSchedule;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class DashboardCalendarService
{
    public function __construct(
        private readonly TeachingScheduleService $teachingScheduleService,
    ) {
    }

    public function buildMonthly(?Teacher $teacher = null, ?int $year = null, ?int $month = null): array
    {
        $anchor = now()->setDate(
            $year ?: now()->year,
            max(1, min(12, $month ?: now()->month)),
            1
        );

        $start = $anchor->copy()->startOfMonth();
        $end = $anchor->copy()->endOfMonth();

        $schoolEvents = SchoolCalendarEvent::query()
            ->where('active', true)
            ->whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date', '>=', $start->toDateString())
            ->get();

        $nationalEvents = NationalHoliday::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $teacherSchedulesByDay = collect();
        if ($teacher) {
            $activeProfile = $this->teachingScheduleService->getActiveProfile();
            $teacherSchedulesByDay = TeachingSchedule::query()
                ->with(['class', 'subject'])
                ->where('teacher_id', $teacher->id)
                ->where('schedule_profile_id', $activeProfile->id)
                ->where('is_active', true)
                ->orderBy('jam_ke')
                ->get()
                ->groupBy('day_of_week');
        }

        $cells = collect();
        foreach (CarbonPeriod::create($start->copy()->startOfWeek(Carbon::MONDAY), $end->copy()->endOfWeek(Carbon::SUNDAY)) as $date) {
            $day = Carbon::parse($date)->startOfDay();
            $dateString = $day->toDateString();

            $daySchoolEvents = $schoolEvents
                ->filter(fn (SchoolCalendarEvent $event) => $day->betweenIncluded($event->start_date, $event->end_date))
                ->map(fn (SchoolCalendarEvent $event) => [
                    'title' => $event->title,
                    'event_type' => $event->event_type,
                    'color' => $event->color ?: SchoolCalendarEvent::resolveColorForEventType($event->event_type),
                    'label' => SchoolCalendarEvent::EVENT_TYPE_LABELS[$event->event_type] ?? ucfirst($event->event_type),
                ])
                ->values();

            $dayNationalEvents = $nationalEvents
                ->filter(fn (NationalHoliday $holiday) => $holiday->date->toDateString() === $dateString)
                ->map(fn (NationalHoliday $holiday) => [
                    'title' => $holiday->title,
                    'event_type' => $holiday->entry_type ?: 'national_holiday',
                    'color' => SchoolCalendarEvent::resolveColorForEventType($holiday->entry_type ?: 'national_holiday'),
                    'label' => $holiday->entry_type === 'collective_leave' ? 'Cuti Bersama' : 'Libur Nasional',
                ])
                ->values();

            $daySchedules = collect();
            if ($teacher) {
                $dayOfWeek = $this->teachingScheduleService->resolveDayOfWeek($day);
                $daySchedules = ($teacherSchedulesByDay->get($dayOfWeek) ?? collect())
                    ->map(fn (TeachingSchedule $schedule) => [
                        'jam_ke' => $schedule->jam_ke,
                        'subject' => $schedule->subject?->name,
                        'class' => $schedule->class?->name,
                        'time' => trim(($schedule->start_time ?: '').' - '.($schedule->end_time ?: ''), ' -'),
                    ])
                    ->values();
            }

            $cells->push([
                'date' => $day,
                'is_current_month' => $day->month === $anchor->month,
                'is_today' => $day->isSameDay(now()),
                'events' => $daySchoolEvents->concat($dayNationalEvents)->values(),
                'schedules' => $daySchedules,
            ]);
        }

        return [
            'year' => $anchor->year,
            'month' => $anchor->month,
            'month_label' => $anchor->translatedFormat('F Y'),
            'weekdays' => ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
            'weeks' => $cells->chunk(7)->map(fn (Collection $week) => $week->values())->values(),
            'prev' => [
                'year' => $anchor->copy()->subMonth()->year,
                'month' => $anchor->copy()->subMonth()->month,
            ],
            'next' => [
                'year' => $anchor->copy()->addMonth()->year,
                'month' => $anchor->copy()->addMonth()->month,
            ],
        ];
    }
}
