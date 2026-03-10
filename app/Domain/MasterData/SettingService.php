<?php

namespace App\Domain\MasterData;

use App\Models\Setting;

class SettingService
{
    public function active(): Setting
    {
        return Setting::active();
    }
}
