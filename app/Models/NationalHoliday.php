<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NationalHoliday extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'title',
        'entry_type',
        'is_national_holiday',
        'is_collective_leave',
        'source',
        'source_payload',
    ];

    protected $casts = [
        'date' => 'date',
        'is_national_holiday' => 'boolean',
        'is_collective_leave' => 'boolean',
        'source_payload' => 'array',
    ];

    public function isCollectiveLeave(): bool
    {
        return $this->entry_type === 'collective_leave' || $this->is_collective_leave;
    }
}
