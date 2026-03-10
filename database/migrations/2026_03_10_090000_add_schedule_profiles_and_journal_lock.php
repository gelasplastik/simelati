<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('schedule_profiles')) {
            Schema::create('schedule_profiles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->boolean('is_active')->default(false);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        DB::table('schedule_profiles')->updateOrInsert(
            ['code' => 'normal'],
            ['name' => 'Normal', 'is_active' => true, 'description' => 'Jadwal reguler sekolah', 'updated_at' => now(), 'created_at' => now()]
        );

        DB::table('schedule_profiles')->updateOrInsert(
            ['code' => 'ramadhan'],
            ['name' => 'Ramadhan', 'is_active' => false, 'description' => 'Jadwal khusus bulan Ramadhan', 'updated_at' => now(), 'created_at' => now()]
        );

        if (Schema::hasTable('teaching_schedules')) {
            $normalProfileId = (int) DB::table('schedule_profiles')->where('code', 'normal')->value('id');

            if (! Schema::hasColumn('teaching_schedules', 'schedule_profile_id')) {
                Schema::table('teaching_schedules', function (Blueprint $table) {
                    $table->foreignId('schedule_profile_id')->nullable()->after('subject_id');
                });
            }

            DB::table('teaching_schedules')
                ->whereNull('schedule_profile_id')
                ->update(['schedule_profile_id' => $normalProfileId]);

            if (! $this->foreignKeyExists('teaching_schedules', 'teaching_schedules_schedule_profile_id_foreign')) {
                Schema::table('teaching_schedules', function (Blueprint $table) {
                    $table->foreign('schedule_profile_id')->references('id')->on('schedule_profiles')->cascadeOnDelete();
                });
            }

            if ($this->indexExists('teaching_schedules', 'teaching_schedule_unique')) {
                Schema::table('teaching_schedules', function (Blueprint $table) {
                    $table->dropUnique('teaching_schedule_unique');
                });
            }

            if (! $this->indexExists('teaching_schedules', 'teaching_schedule_profile_unique')) {
                Schema::table('teaching_schedules', function (Blueprint $table) {
                    $table->unique(
                        ['schedule_profile_id', 'teacher_id', 'class_id', 'subject_id', 'day_of_week', 'jam_ke'],
                        'teaching_schedule_profile_unique'
                    );
                });
            }

            if (! $this->indexExists('teaching_schedules', 'teaching_schedule_profile_teacher_day_idx')) {
                Schema::table('teaching_schedules', function (Blueprint $table) {
                    $table->index(['schedule_profile_id', 'teacher_id', 'day_of_week', 'is_active'], 'teaching_schedule_profile_teacher_day_idx');
                });
            }
        }

        if (Schema::hasTable('settings') && ! Schema::hasColumn('settings', 'journal_lock_enabled')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->boolean('journal_lock_enabled')->default(true)->after('izin_requires_approval');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'journal_lock_enabled')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('journal_lock_enabled');
            });
        }

        if (Schema::hasTable('teaching_schedules') && Schema::hasColumn('teaching_schedules', 'schedule_profile_id')) {
            if ($this->indexExists('teaching_schedules', 'teaching_schedule_profile_teacher_day_idx')) {
                Schema::table('teaching_schedules', function (Blueprint $table) {
                    $table->dropIndex('teaching_schedule_profile_teacher_day_idx');
                });
            }

            if ($this->indexExists('teaching_schedules', 'teaching_schedule_profile_unique')) {
                Schema::table('teaching_schedules', function (Blueprint $table) {
                    $table->dropUnique('teaching_schedule_profile_unique');
                });
            }

            if ($this->foreignKeyExists('teaching_schedules', 'teaching_schedules_schedule_profile_id_foreign')) {
                Schema::table('teaching_schedules', function (Blueprint $table) {
                    $table->dropForeign(['schedule_profile_id']);
                });
            }

            if (! $this->indexExists('teaching_schedules', 'teaching_schedule_unique')) {
                Schema::table('teaching_schedules', function (Blueprint $table) {
                    $table->unique(['teacher_id', 'class_id', 'subject_id', 'day_of_week', 'jam_ke'], 'teaching_schedule_unique');
                });
            }

            Schema::table('teaching_schedules', function (Blueprint $table) {
                $table->dropColumn('schedule_profile_id');
            });
        }

        Schema::dropIfExists('schedule_profiles');
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);

        return ! empty($result);
    }

    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        $database = DB::getDatabaseName();
        $result = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? LIMIT 1',
            [$database, $table, $constraintName]
        );

        return ! empty($result);
    }
};
