<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolCalendarEvent extends Model
{
    use HasFactory;

    public const EVENT_TYPES = [
        'national_holiday',
        'collective_leave',
        'school_holiday',
        'semester_break',
        'eid_holiday',
        'religious_activity',
        'special_activity',
        'assessment',
        'non_teaching_day',
        'custom',
    ];

    public const EVENT_TYPE_LABELS = [
        'national_holiday' => 'Libur Nasional',
        'collective_leave' => 'Cuti Bersama',
        'school_holiday' => 'Libur Sekolah',
        'semester_break' => 'Libur Semester',
        'eid_holiday' => 'Libur Idul Fitri',
        'religious_activity' => 'Kegiatan Keagamaan',
        'special_activity' => 'Kegiatan Khusus',
        'assessment' => 'Asesmen/Ujian',
        'non_teaching_day' => 'Hari Non-Pembelajaran',
        'custom' => 'Custom',
    ];

    public const EVENT_TYPE_COLORS = [
        'national_holiday' => '#E53935',
        'collective_leave' => '#EF6C00',
        'school_holiday' => '#FB8C00',
        'semester_break' => '#FF9800',
        'eid_holiday' => '#F4511E',
        'religious_activity' => '#7E57C2',
        'special_activity' => '#43A047',
        'assessment' => '#1E88E5',
        'non_teaching_day' => '#78909C',
        'custom' => '#607D8B',
    ];

    public const OPERATIONAL_MODES = [
        'full_holiday',
        'students_off',
        'teachers_off',
        'school_activity',
        'assessment_day',
        'custom',
    ];

    public const OPERATIONAL_MODE_LABELS = [
        'full_holiday' => 'Libur Penuh',
        'students_off' => 'Siswa Libur',
        'teachers_off' => 'Guru Libur',
        'school_activity' => 'Kegiatan Sekolah',
        'assessment_day' => 'Hari Asesmen/Ujian',
        'custom' => 'Custom (Lanjutan)',
    ];

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'event_type',
        'operational_mode',
        'color',
        'is_school_day',
        'disable_teacher_attendance',
        'disable_student_attendance',
        'disable_journal',
        'disable_substitute_generation',
        'disable_kpi_penalty',
        'show_on_dashboard',
        'active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_school_day' => 'boolean',
        'disable_teacher_attendance' => 'boolean',
        'disable_student_attendance' => 'boolean',
        'disable_journal' => 'boolean',
        'disable_substitute_generation' => 'boolean',
        'disable_kpi_penalty' => 'boolean',
        'show_on_dashboard' => 'boolean',
        'active' => 'boolean',
    ];

    public static function resolveColorForEventType(string $eventType): string
    {
        return self::EVENT_TYPE_COLORS[$eventType] ?? self::EVENT_TYPE_COLORS['custom'];
    }

    public static function resolveFlagsByOperationalMode(string $mode): array
    {
        return match ($mode) {
            'full_holiday' => [
                'is_school_day' => false,
                'disable_teacher_attendance' => true,
                'disable_student_attendance' => true,
                'disable_journal' => true,
                'disable_substitute_generation' => true,
                'disable_kpi_penalty' => true,
            ],
            'students_off' => [
                'is_school_day' => true,
                'disable_teacher_attendance' => false,
                'disable_student_attendance' => true,
                'disable_journal' => true,
                'disable_substitute_generation' => true,
                'disable_kpi_penalty' => true,
            ],
            'teachers_off' => [
                'is_school_day' => true,
                'disable_teacher_attendance' => true,
                'disable_student_attendance' => false,
                'disable_journal' => true,
                'disable_substitute_generation' => true,
                'disable_kpi_penalty' => true,
            ],
            'school_activity' => [
                'is_school_day' => true,
                'disable_teacher_attendance' => false,
                'disable_student_attendance' => false,
                'disable_journal' => true,
                'disable_substitute_generation' => false,
                'disable_kpi_penalty' => false,
            ],
            'assessment_day' => [
                'is_school_day' => true,
                'disable_teacher_attendance' => false,
                'disable_student_attendance' => false,
                'disable_journal' => true,
                'disable_substitute_generation' => false,
                'disable_kpi_penalty' => false,
            ],
            default => [
                'is_school_day' => true,
                'disable_teacher_attendance' => false,
                'disable_student_attendance' => false,
                'disable_journal' => false,
                'disable_substitute_generation' => false,
                'disable_kpi_penalty' => false,
            ],
        };
    }

    public static function effectPreviewFromFlags(array $flags): array
    {
        return [
            ($flags['disable_teacher_attendance'] ?? false) ? 'Guru tidak wajib absen' : 'Guru tetap absen',
            ($flags['disable_student_attendance'] ?? false) ? 'Siswa tidak wajib absen' : 'Siswa tetap absen',
            ($flags['disable_journal'] ?? false) ? 'Jurnal tidak wajib' : 'Jurnal tetap wajib',
            ($flags['disable_substitute_generation'] ?? false) ? 'Pengganti tidak dibuat otomatis' : 'Pengganti tetap dapat dibuat',
            ($flags['disable_kpi_penalty'] ?? false) ? 'Penalti KPI diabaikan' : 'Penalti KPI tetap berjalan',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
