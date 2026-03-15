<?php

namespace App\Http\Controllers\Admin;

use App\Domain\MasterData\NationalHolidaySyncService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSchoolCalendarEventRequest;
use App\Http\Requests\Admin\SyncNationalHolidayRequest;
use App\Http\Requests\Admin\UpdateSchoolCalendarEventRequest;
use App\Models\NationalHoliday;
use App\Models\SchoolCalendarEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicCalendarController extends Controller
{
    public function index(Request $request): View
    {
        $year = (int) $request->integer('year', now()->year);
        $month = max(1, min(12, (int) $request->integer('month', now()->month)));

        $startDate = now()->setYear($year)->setMonth($month)->startOfMonth()->toDateString();
        $endDate = now()->setYear($year)->setMonth($month)->endOfMonth()->toDateString();

        $events = SchoolCalendarEvent::query()
            ->with(['creator', 'updater'])
            ->when($request->filled('event_type'), fn ($q) => $q->where('event_type', $request->string('event_type')))
            ->when($request->filled('active'), fn ($q) => $q->where('active', $request->boolean('active')))
            ->whereDate('start_date', '<=', $endDate)
            ->whereDate('end_date', '>=', $startDate)
            ->orderBy('start_date')
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        $nationalHolidays = NationalHoliday::query()
            ->whereYear('date', $year)
            ->orderBy('date')
            ->paginate(20, ['*'], 'national_page')
            ->withQueryString();

        $yearOptions = collect(range(2021, 2035));

        return view('admin.calendar.index', [
            'events' => $events,
            'nationalHolidays' => $nationalHolidays,
            'eventTypes' => SchoolCalendarEvent::EVENT_TYPE_LABELS,
            'operationalModes' => SchoolCalendarEvent::OPERATIONAL_MODE_LABELS,
            'operationalModeEffects' => collect(SchoolCalendarEvent::OPERATIONAL_MODES)->mapWithKeys(fn ($mode) => [
                $mode => SchoolCalendarEvent::effectPreviewFromFlags(SchoolCalendarEvent::resolveFlagsByOperationalMode($mode)),
            ]),
            'eventColors' => SchoolCalendarEvent::EVENT_TYPE_COLORS,
            'yearOptions' => $yearOptions,
            'selectedYear' => $year,
            'selectedMonth' => $month,
            'selectedType' => $request->string('event_type')->value(),
        ]);
    }

    public function store(StoreSchoolCalendarEventRequest $request): RedirectResponse
    {
        $payload = $this->normalizePayload($request->validated());
        $payload['created_by'] = $request->user()->id;
        $payload['updated_by'] = $request->user()->id;

        SchoolCalendarEvent::query()->create($payload);

        return back()->with('success', 'Event kalender akademik berhasil ditambahkan.');
    }

    public function update(UpdateSchoolCalendarEventRequest $request, SchoolCalendarEvent $event): RedirectResponse
    {
        $payload = $this->normalizePayload($request->validated());
        $payload['updated_by'] = $request->user()->id;

        $event->update($payload);

        return back()->with('success', 'Event kalender akademik berhasil diperbarui.');
    }

    public function destroy(SchoolCalendarEvent $event): RedirectResponse
    {
        $event->delete();

        return back()->with('success', 'Event kalender akademik berhasil dihapus.');
    }

    public function syncNational(SyncNationalHolidayRequest $request, NationalHolidaySyncService $service): RedirectResponse
    {
        $year = (int) $request->integer('year');
        $result = $service->syncYear($year);

        return back()->with('success', sprintf(
            'Sinkronisasi %d selesai. Source: %s, inserted: %d, updated: %d, skipped: %d, collective leave: %d.',
            $year,
            $result['source'],
            $result['inserted'],
            $result['updated'],
            $result['skipped'],
            $result['collective_leave_count']
        ));
    }

    private function normalizePayload(array $payload): array
    {
        $mode = (string) ($payload['operational_mode'] ?? 'custom');
        $eventType = (string) ($payload['event_type'] ?? 'custom');

        $booleans = [
            'is_school_day',
            'disable_teacher_attendance',
            'disable_student_attendance',
            'disable_journal',
            'disable_substitute_generation',
            'disable_kpi_penalty',
            'show_on_dashboard',
            'active',
        ];

        foreach ($booleans as $key) {
            $payload[$key] = (bool) ($payload[$key] ?? false);
        }

        if ($mode !== 'custom') {
            $payload = array_merge($payload, SchoolCalendarEvent::resolveFlagsByOperationalMode($mode));
        }

        $payload['color'] = $payload['color']
            ? (str_starts_with($payload['color'], '#') ? $payload['color'] : '#'.$payload['color'])
            : SchoolCalendarEvent::resolveColorForEventType($eventType);

        return $payload;
    }
}
