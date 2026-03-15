<?php

namespace App\Domain\MasterData;

use App\Domain\MasterData\HolidaySources\CalendarificHolidaySource;
use App\Domain\MasterData\HolidaySources\FallbackDatasetHolidaySource;
use App\Domain\MasterData\HolidaySources\HolidaySourceInterface;
use App\Domain\MasterData\HolidaySources\NagerHolidaySource;
use App\Domain\MasterData\HolidaySources\SkbThreeMinisterHolidaySource;
use App\Models\NationalHoliday;
use Illuminate\Support\Collection;

class NationalHolidaySyncService
{
    /**
     * @var array<int, HolidaySourceInterface>
     */
    private array $sources;

    public function __construct()
    {
        $this->sources = [
            new SkbThreeMinisterHolidaySource(),
            new NagerHolidaySource(),
            new CalendarificHolidaySource(),
            new FallbackDatasetHolidaySource(),
        ];
    }

    public function syncYear(int $year): array
    {
        $pickedSource = null;
        $items = collect();

        foreach ($this->sources as $source) {
            $candidate = $source->fetchYear($year);
            if ($candidate->isNotEmpty()) {
                $items = $this->normalizeItems($candidate);
                $pickedSource = $source->key();
                break;
            }
        }

        if ($pickedSource === null) {
            return [
                'year' => $year,
                'source' => 'none',
                'inserted' => 0,
                'updated' => 0,
                'skipped' => 0,
                'count' => 0,
                'collective_leave_count' => 0,
            ];
        }

        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($items as $item) {
            $existing = NationalHoliday::query()->where([
                'date' => $item['date'],
                'title' => $item['title'],
                'entry_type' => $item['entry_type'],
            ])->first();

            if (! $existing) {
                NationalHoliday::query()->create([
                    'date' => $item['date'],
                    'title' => $item['title'],
                    'entry_type' => $item['entry_type'],
                    'is_national_holiday' => $item['is_national_holiday'],
                    'is_collective_leave' => $item['is_collective_leave'],
                    'source' => $pickedSource,
                    'source_payload' => $item['source_payload'] ?? null,
                ]);
                $inserted++;

                continue;
            }

            $changes = [
                'is_national_holiday' => $item['is_national_holiday'],
                'is_collective_leave' => $item['is_collective_leave'],
                'source' => $pickedSource,
                'source_payload' => $item['source_payload'] ?? null,
            ];

            if ($existing->is_national_holiday !== $changes['is_national_holiday']
                || $existing->is_collective_leave !== $changes['is_collective_leave']
                || $existing->source !== $changes['source']
                || $existing->source_payload !== $changes['source_payload']) {
                $existing->update($changes);
                $updated++;
            } else {
                $skipped++;
            }
        }

        return [
            'year' => $year,
            'source' => $pickedSource,
            'inserted' => $inserted,
            'updated' => $updated,
            'skipped' => $skipped,
            'count' => $inserted + $updated,
            'collective_leave_count' => $items->where('is_collective_leave', true)->count(),
        ];
    }

    private function normalizeItems(Collection $items): Collection
    {
        return $items
            ->filter(fn ($item) => ! empty($item['date']) && ! empty($item['title']))
            ->map(function (array $item) {
                $isCollectiveLeave = (bool) ($item['is_collective_leave'] ?? false)
                    || (($item['entry_type'] ?? '') === 'collective_leave')
                    || str_contains(mb_strtolower((string) $item['title']), 'cuti bersama');

                return [
                    'date' => (string) $item['date'],
                    'title' => trim((string) $item['title']),
                    'entry_type' => $isCollectiveLeave ? 'collective_leave' : 'national_holiday',
                    'is_national_holiday' => ! $isCollectiveLeave,
                    'is_collective_leave' => $isCollectiveLeave,
                    'source_payload' => $item['source_payload'] ?? $item,
                ];
            })
            ->unique(fn ($item) => $item['date'].'|'.$item['title'].'|'.$item['entry_type'])
            ->values();
    }
}
