<?php

namespace App\Domain\MasterData\HolidaySources;

use Illuminate\Support\Collection;

interface HolidaySourceInterface
{
    public function key(): string;

    public function fetchYear(int $year): Collection;
}
