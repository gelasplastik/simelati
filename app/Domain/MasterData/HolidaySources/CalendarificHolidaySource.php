<?php

namespace App\Domain\MasterData\HolidaySources;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class CalendarificHolidaySource implements HolidaySourceInterface
{
    public function key(): string
    {
        return 'calendarific';
    }

    public function fetchYear(int $year): Collection
    {
        $apiKey = (string) config('academic_calendar.national_holiday_sources.calendarific_api_key');
        if ($apiKey === '') {
            return collect();
        }

        $endpoint = (string) config('academic_calendar.national_holiday_sources.calendarific_api');

        try {
            $response = Http::timeout(20)->acceptJson()->get($endpoint, [
                'api_key' => $apiKey,
                'country' => 'ID',
                'year' => $year,
            ]);

            if (! $response->successful()) {
                return collect();
            }

            $items = data_get($response->json(), 'response.holidays', []);

            return collect($items)
                ->filter(fn ($item) => ! empty($item['date']['iso']) && ! empty($item['name']))
                ->map(function (array $item) {
                    $date = substr((string) data_get($item, 'date.iso'), 0, 10);
                    $title = (string) $item['name'];
                    $types = collect((array) ($item['type'] ?? []))->map(fn ($v) => mb_strtolower((string) $v));

                    $isCollectiveLeave = str_contains(mb_strtolower($title), 'cuti bersama')
                        || $types->contains(fn ($t) => str_contains($t, 'observance'));

                    return [
                        'date' => $date,
                        'title' => $title,
                        'entry_type' => $isCollectiveLeave ? 'collective_leave' : 'national_holiday',
                        'is_national_holiday' => ! $isCollectiveLeave,
                        'is_collective_leave' => $isCollectiveLeave,
                        'source_payload' => $item,
                    ];
                });
        } catch (\Throwable) {
            return collect();
        }
    }
}
