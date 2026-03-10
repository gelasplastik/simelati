<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'date',
        'checkin_at',
        'checkin_lat',
        'checkin_lng',
        'checkin_accuracy',
        'checkin_distance',
        'is_late',
        'checkout_at',
        'checkout_lat',
        'checkout_lng',
        'checkout_accuracy',
        'checkout_distance',
    ];

    protected $casts = [
        'date' => 'date',
        'checkin_at' => 'datetime',
        'checkout_at' => 'datetime',
        'is_late' => 'boolean',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function journals(): HasMany
    {
        return $this->hasMany(Journal::class);
    }
}
