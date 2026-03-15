<?php

namespace App\Console\Commands;

use App\Domain\MasterData\NationalHolidaySyncService;
use Illuminate\Console\Command;

class SyncNationalHolidayCommand extends Command
{
    protected $signature = 'calendar:sync-national {--year=}';

    protected $description = 'Sync Indonesian national holidays and collective leave by year into local database';

    public function handle(NationalHolidaySyncService $service): int
    {
        $year = (int) ($this->option('year') ?: now()->year);

        if ($year < 2021 || $year > 2035) {
            $this->error('Tahun harus antara 2021 dan 2035.');

            return self::FAILURE;
        }

        $result = $service->syncYear($year);

        $this->info(sprintf(
            'Sync libur nasional %d selesai. Source: %s, inserted: %d, updated: %d, skipped: %d, collective leave: %d.',
            $result['year'],
            $result['source'],
            $result['inserted'],
            $result['updated'],
            $result['skipped'],
            $result['collective_leave_count']
        ));

        return self::SUCCESS;
    }
}
