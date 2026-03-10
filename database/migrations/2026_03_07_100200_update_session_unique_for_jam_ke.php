<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('class_attendance_sessions', function (Blueprint $table) {
            $table->index('teacher_id');
            $table->index('class_id');
            $table->index('subject_id');
            $table->dropUnique('session_unique');
            $table->unique(['teacher_id', 'class_id', 'subject_id', 'date', 'jam_ke'], 'session_unique_with_jam');
        });
    }

    public function down(): void
    {
        Schema::table('class_attendance_sessions', function (Blueprint $table) {
            $table->dropUnique('session_unique_with_jam');
            $table->dropIndex(['teacher_id']);
            $table->dropIndex(['class_id']);
            $table->dropIndex(['subject_id']);
            $table->unique(['teacher_id', 'class_id', 'subject_id', 'date'], 'session_unique');
        });
    }
};
