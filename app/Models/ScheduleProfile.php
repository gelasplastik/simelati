<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduleProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function teachingSchedules(): HasMany
    {
        return $this->hasMany(TeachingSchedule::class, 'schedule_profile_id');
    }

    public static function active(): ?self
    {
        return self::query()->where('is_active', true)->first();
    }
}
