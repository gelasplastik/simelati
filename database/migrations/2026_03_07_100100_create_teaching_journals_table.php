<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teaching_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_attendance_session_id')->unique()->constrained('class_attendance_sessions')->cascadeOnDelete();
            $table->date('date')->index();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->unsignedTinyInteger('jam_ke');
            $table->unsignedInteger('pertemuan_ke')->nullable();
            $table->text('materi')->nullable();
            $table->text('absent_students_text')->nullable();
            $table->text('student_notes')->nullable();
            $table->text('mastery_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_journals');
    }
};
