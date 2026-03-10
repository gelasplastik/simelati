<?php

namespace App\Domain\Attendance;

use App\Models\Attendance;
use App\Models\Setting;
use App\Models\Teacher;
use Carbon\Carbon;
use InvalidArgumentException;

class AttendanceService
{
    public function __construct(private readonly GeoService $geoService)
    {
    }

    public function checkin(Teacher $teacher, array $payload): Attendance
    {
        $setting = Setting::active();
        $today = now()->toDateString();

        if (Attendance::query()->where('teacher_id', $teacher->id)->whereDate('date', $today)->exists()) {
            throw new InvalidArgumentException('Anda sudah absen masuk hari ini.');
        }

        $distance = $this->geoService->calculateDistance(
            (float) $setting->school_lat,
            (float) $setting->school_lng,
            (float) $payload['lat'],
            (float) $payload['lng']
        );

        if (! $this->geoService->validateRadius($distance, (int) $setting->radius_m)) {
            throw new InvalidArgumentException('Lokasi di luar radius sekolah.');
        }

        $isLate = Carbon::now()->format('H:i:s') > Carbon::parse($setting->late_tolerance_time)->format('H:i:s');

        return Attendance::query()->create([
            'teacher_id' => $teacher->id,
            'date' => $today,
            'checkin_at' => now(),
            'checkin_lat' => $payload['lat'],
            'checkin_lng' => $payload['lng'],
            'checkin_accuracy' => $payload['accuracy'],
            'checkin_distance' => $distance,
            'is_late' => $isLate,
        ]);
    }

    public function checkout(Teacher $teacher, array $payload): Attendance
    {
        $setting = Setting::active();
        $today = now()->toDateString();

        $attendance = Attendance::query()->where('teacher_id', $teacher->id)->whereDate('date', $today)->first();

        if (! $attendance) {
            throw new InvalidArgumentException('Absen masuk belum dilakukan.');
        }

        if ($attendance->checkout_at) {
            throw new InvalidArgumentException('Anda sudah absen pulang hari ini.');
        }

        $distance = $this->geoService->calculateDistance(
            (float) $setting->school_lat,
            (float) $setting->school_lng,
            (float) $payload['lat'],
            (float) $payload['lng']
        );

        if (! $this->geoService->validateRadius($distance, (int) $setting->radius_m)) {
            throw new InvalidArgumentException('Lokasi di luar radius sekolah.');
        }

        $minCheckoutTime = Carbon::parse($setting->min_checkout_time ?? '12:00:00')->format('H:i:s');
        if (Carbon::now()->format('H:i:s') < $minCheckoutTime) {
            throw new InvalidArgumentException('Absen pulang hanya bisa dilakukan mulai pukul '.Carbon::parse($minCheckoutTime)->format('H:i').'.');
        }

        $attendance->update([
            'checkout_at' => now(),
            'checkout_lat' => $payload['lat'],
            'checkout_lng' => $payload['lng'],
            'checkout_accuracy' => $payload['accuracy'],
            'checkout_distance' => $distance,
        ]);

        return $attendance->fresh();
    }
}
