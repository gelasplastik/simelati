<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'parent_id',
        'submitter_name',
        'submitter_phone',
        'submitter_relationship',
        'date_from',
        'date_to',
        'reason',
        'attachment',
        'attachment_path',
        'ip_address',
        'user_agent',
        'status',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ParentModel::class, 'parent_id');
    }

    public function getEffectiveAttachmentPathAttribute(): ?string
    {
        return $this->attachment_path ?: $this->attachment;
    }
}
