<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_lat',
        'school_lng',
        'radius_m',
        'start_time',
        'late_tolerance_time',
        'min_checkout_time',
        'izin_requires_approval',
        'journal_lock_enabled',
        'attendance_journal_open_enabled',
    ];

    protected $casts = [
        'izin_requires_approval' => 'boolean',
        'journal_lock_enabled' => 'boolean',
        'attendance_journal_open_enabled' => 'boolean',
    ];

    public static function active(): self
    {
        return self::query()->firstOrCreate([], [
            'school_lat' => -7.2574720,
            'school_lng' => 112.7520883,
            'radius_m' => 150,
            'start_time' => '07:00:00',
            'late_tolerance_time' => '07:15:00',
            'min_checkout_time' => '12:00:00',
            'izin_requires_approval' => true,
            'journal_lock_enabled' => true,
            'attendance_journal_open_enabled' => false,
        ]);
    }
}

