<?php

namespace App\Domain\MasterData\HolidaySources;

use Illuminate\Support\Collection;

class SkbThreeMinisterHolidaySource implements HolidaySourceInterface
{
    public function key(): string
    {
        return 'skb_3_menteri';
    }

    public function fetchYear(int $year): Collection
    {
        return collect(config('academic_calendar.skb_3_menteri.'.$year, []))
            ->filter(fn ($item) => ! empty($item['date']) && ! empty($item['title']))
            ->map(function (array $item) {
                $entryType = (string) ($item['entry_type'] ?? 'national_holiday');
                $isCollectiveLeave = $entryType === 'collective_leave'
                    || (bool) ($item['is_collective_leave'] ?? false)
                    || str_contains(mb_strtolower((string) $item['title']), 'cuti bersama');

                return [
                    'date' => (string) $item['date'],
                    'title' => (string) $item['title'],
                    'entry_type' => $isCollectiveLeave ? 'collective_leave' : 'national_holiday',
                    'is_national_holiday' => ! $isCollectiveLeave,
                    'is_collective_leave' => $isCollectiveLeave,
                    'source_payload' => [
                        'issuer' => 'SKB 3 Menteri',
                        'reference' => $item['reference'] ?? null,
                        'raw' => $item,
                    ],
                ];
            });
    }
}
