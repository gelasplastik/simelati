<?php

namespace App\Domain\MasterData;

use App\Models\ScheduleProfile;
use Illuminate\Support\Facades\DB;

class ScheduleProfileService
{
    public function all()
    {
        return ScheduleProfile::query()->orderBy('id')->get();
    }

    public function active(): ScheduleProfile
    {
        $active = ScheduleProfile::active();

        if ($active) {
            return $active;
        }

        return ScheduleProfile::query()->firstOrCreate(
            ['code' => 'normal'],
            ['name' => 'Normal', 'is_active' => true, 'description' => 'Jadwal reguler sekolah']
        );
    }

    public function activate(ScheduleProfile $profile): void
    {
        DB::transaction(function () use ($profile) {
            ScheduleProfile::query()->update(['is_active' => false]);
            $profile->update(['is_active' => true]);
        });
    }
}
