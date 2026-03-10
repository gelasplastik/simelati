<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('teaching_schedules')) {
            Schema::create('teaching_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
                $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
                $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']);
                $table->unsignedTinyInteger('jam_ke');
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['teacher_id', 'class_id', 'subject_id', 'day_of_week', 'jam_ke'], 'teaching_schedule_unique');
                $table->index(['teacher_id', 'day_of_week', 'is_active'], 'teaching_schedule_teacher_day_idx');
                $table->index(['class_id', 'day_of_week'], 'teaching_schedule_class_day_idx');
            });
        }

        if (Schema::hasTable('class_attendance_sessions') && ! Schema::hasColumn('class_attendance_sessions', 'teaching_schedule_id')) {
            Schema::table('class_attendance_sessions', function (Blueprint $table) {
                $table->foreignId('teaching_schedule_id')->nullable()->after('subject_id')->constrained('teaching_schedules')->nullOnDelete();
                $table->index('teaching_schedule_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('class_attendance_sessions') && Schema::hasColumn('class_attendance_sessions', 'teaching_schedule_id')) {
            Schema::table('class_attendance_sessions', function (Blueprint $table) {
                $table->dropForeign(['teaching_schedule_id']);
                $table->dropIndex(['teaching_schedule_id']);
                $table->dropColumn('teaching_schedule_id');
            });
        }

        Schema::dropIfExists('teaching_schedules');
    }
};
