<?php

use App\Domain\MasterData\NationalHolidaySyncService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('calendar:sync-national {--year=}', function (NationalHolidaySyncService $service) {
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
})->purpose('Sync Indonesian national holidays by year into local database');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
