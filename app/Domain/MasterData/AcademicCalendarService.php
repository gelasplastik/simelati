<?php

namespace App\Domain\MasterData;

use App\Models\NationalHoliday;
use App\Models\SchoolCalendarEvent;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class AcademicCalendarService
{
    public function getActiveEventsForDate(string|Carbon $date): Collection
    {
        $targetDate = Carbon::parse($date)->toDateString();

        $schoolEvents = SchoolCalendarEvent::query()
            ->where('active', true)
            ->whereDate('start_date', '<=', $targetDate)
            ->whereDate('end_date', '>=', $targetDate)
            ->orderBy('start_date')
            ->get()
            ->map(fn (SchoolCalendarEvent $event) => [
                'layer' => 'school',
                'title' => $event->title,
                'event_type' => $event->event_type,
                'operational_mode' => $event->operational_mode,
                'show_on_dashboard' => (bool) $event->show_on_dashboard,
                'is_school_day' => (bool) $event->is_school_day,
                'disable_teacher_attendance' => (bool) $event->disable_teacher_attendance,
                'disable_student_attendance' => (bool) $event->disable_student_attendance,
                'disable_journal' => (bool) $event->disable_journal,
                'disable_substitute_generation' => (bool) $event->disable_substitute_generation,
                'disable_kpi_penalty' => (bool) $event->disable_kpi_penalty,
                'source_model' => $event,
            ]);

        $nationalEvents = NationalHoliday::query()
            ->whereDate('date', $targetDate)
            ->orderBy('title')
            ->get()
            ->map(fn (NationalHoliday $holiday) => [
                'layer' => 'national',
                'title' => $holiday->title,
                'event_type' => $holiday->entry_type ?: 'national_holiday',
                'operational_mode' => 'full_holiday',
                'show_on_dashboard' => true,
                'is_school_day' => false,
                'disable_teacher_attendance' => true,
                'disable_student_attendance' => true,
                'disable_journal' => true,
                'disable_substitute_generation' => true,
                'disable_kpi_penalty' => true,
                'source_model' => $holiday,
            ]);

        return $schoolEvents->isNotEmpty()
            ? $schoolEvents->concat($nationalEvents)
            : $nationalEvents;
    }

    public function evaluateForDate(string|Carbon $date): array
    {
        $targetDate = Carbon::parse($date)->toDateString();

        $schoolEvents = SchoolCalendarEvent::query()
            ->where('active', true)
            ->whereDate('start_date', '<=', $targetDate)
            ->whereDate('end_date', '>=', $targetDate)
            ->get();

        $nationalEvents = NationalHoliday::query()
            ->whereDate('date', $targetDate)
            ->get();

        $rawEvents = $this->getActiveEventsForDate($targetDate);

        $base = [
            'is_school_day' => true,
            'disable_teacher_attendance' => false,
            'disable_student_attendance' => false,
            'disable_journal' => false,
            'disable_substitute_generation' => false,
            'disable_kpi_penalty' => false,
        ];

        $applyRestrictive = static function (array $result, Collection $events): array {
            foreach ($events as $event) {
                $result['is_school_day'] = $result['is_school_day'] && (bool) $event['is_school_day'];
                $result['disable_teacher_attendance'] = $result['disable_teacher_attendance'] || (bool) $event['disable_teacher_attendance'];
                $result['disable_student_attendance'] = $result['disable_student_attendance'] || (bool) $event['disable_student_attendance'];
                $result['disable_journal'] = $result['disable_journal'] || (bool) $event['disable_journal'];
                $result['disable_substitute_generation'] = $result['disable_substitute_generation'] || (bool) $event['disable_substitute_generation'];
                $result['disable_kpi_penalty'] = $result['disable_kpi_penalty'] || (bool) $event['disable_kpi_penalty'];
            }

            return $result;
        };

        $nationalResult = $applyRestrictive($base, $nationalEvents->map(fn (NationalHoliday $holiday) => [
            'is_school_day' => false,
            'disable_teacher_attendance' => true,
            'disable_student_attendance' => true,
            'disable_journal' => true,
            'disable_substitute_generation' => true,
            'disable_kpi_penalty' => true,
        ]));

        $schoolResult = $applyRestrictive($base, $schoolEvents->map(fn (SchoolCalendarEvent $event) => [
            'is_school_day' => (bool) $event->is_school_day,
            'disable_teacher_attendance' => (bool) $event->disable_teacher_attendance,
            'disable_student_attendance' => (bool) $event->disable_student_attendance,
            'disable_journal' => (bool) $event->disable_journal,
            'disable_substitute_generation' => (bool) $event->disable_substitute_generation,
            'disable_kpi_penalty' => (bool) $event->disable_kpi_penalty,
        ]));

        $effective = $schoolEvents->isNotEmpty() ? $schoolResult : $nationalResult;

        return [
            'date' => $targetDate,
            'events' => $rawEvents,
            'has_events' => $rawEvents->isNotEmpty(),
            'is_school_day' => $effective['is_school_day'],
            'disable_teacher_attendance' => $effective['disable_teacher_attendance'],
            'disable_student_attendance' => $effective['disable_student_attendance'],
            'disable_journal' => $effective['disable_journal'],
            'disable_substitute_generation' => $effective['disable_substitute_generation'],
            'disable_kpi_penalty' => $effective['disable_kpi_penalty'],
            'school_override_applied' => $schoolEvents->isNotEmpty(),
        ];
    }

    public function isSchoolDay(string|Carbon $date): bool
    {
        return $this->evaluateForDate($date)['is_school_day'];
    }

    public function isTeacherAttendanceEnabled(string|Carbon $date): bool
    {
        return ! $this->evaluateForDate($date)['disable_teacher_attendance'];
    }

    public function isStudentAttendanceEnabled(string|Carbon $date): bool
    {
        return ! $this->evaluateForDate($date)['disable_student_attendance'];
    }

    public function isJournalEnabled(string|Carbon $date): bool
    {
        return ! $this->evaluateForDate($date)['disable_journal'];
    }

    public function isSubstituteGenerationEnabled(string|Carbon $date): bool
    {
        return ! $this->evaluateForDate($date)['disable_substitute_generation'];
    }

    public function shouldIgnoreKpiPenalty(string|Carbon $date): bool
    {
        return (bool) $this->evaluateForDate($date)['disable_kpi_penalty'];
    }

    public function getDateRangeWhereKpiPenaltyIgnored(Carbon $start, Carbon $end): Collection
    {
        $dates = collect();

        foreach (CarbonPeriod::create($start->toDateString(), $end->toDateString()) as $date) {
            $dateString = Carbon::parse($date)->toDateString();
            if ($this->shouldIgnoreKpiPenalty($dateString)) {
                $dates->push($dateString);
            }
        }

        return $dates->values();
    }

    public function buildDashboardNoticeForDate(string|Carbon $date): ?array
    {
        $evaluation = $this->evaluateForDate($date);

        if (! $evaluation['has_events']) {
            return null;
        }

        $titles = $evaluation['events']
            ->filter(fn (array $event) => (bool) ($event['show_on_dashboard'] ?? false))
            ->pluck('title')
            ->unique()
            ->values();

        if ($titles->isEmpty()) {
            return null;
        }

        return [
            'date' => Carbon::parse($evaluation['date'])->translatedFormat('l, d F Y'),
            'titles' => $titles,
            'message' => 'Hari ini: '.$titles->join(' | '),
            'is_school_day' => $evaluation['is_school_day'],
            'school_override_applied' => $evaluation['school_override_applied'] ?? false,
        ];
    }
}
