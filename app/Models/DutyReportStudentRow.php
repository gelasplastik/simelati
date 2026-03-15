<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DutyReportStudentRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'class_id',
        'class_name',
        'total_students',
        'present_count',
        'sick_count',
        'izin_count',
        'alpa_count',
        'late_count',
        'notes',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(DutyReport::class, 'report_id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
