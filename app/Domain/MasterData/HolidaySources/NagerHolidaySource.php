<?php

namespace App\Domain\MasterData\HolidaySources;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class NagerHolidaySource implements HolidaySourceInterface
{
    public function key(): string
    {
        return 'nager';
    }

    public function fetchYear(int $year): Collection
    {
        $url = str_replace('{year}', (string) $year, (string) config('academic_calendar.national_holiday_sources.nager_api'));

        try {
            $response = Http::timeout(20)->acceptJson()->get($url);
            if (! $response->successful()) {
                return collect();
            }

            return collect($response->json())
                ->filter(fn ($item) => ! empty($item['date']) && (! empty($item['localName']) || ! empty($item['name'])))
                ->map(function (array $item) {
                    $title = (string) ($item['localName'] ?? $item['name']);
                    $isCollectiveLeave = str_contains(mb_strtolower($title), 'cuti bersama');

                    return [
                        'date' => (string) $item['date'],
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
