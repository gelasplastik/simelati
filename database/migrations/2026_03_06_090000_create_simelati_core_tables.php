<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('employee_code')->unique();
            $table->timestamps();
        });

        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['teacher_id', 'subject_id', 'class_id']);
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nis')->unique();
            $table->string('full_name');
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('parent_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('parents')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['parent_id', 'student_id']);
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->date('date')->index();
            $table->timestamp('checkin_at')->nullable();
            $table->decimal('checkin_lat', 10, 7)->nullable();
            $table->decimal('checkin_lng', 10, 7)->nullable();
            $table->decimal('checkin_accuracy', 8, 2)->nullable();
            $table->decimal('checkin_distance', 8, 2)->nullable();
            $table->boolean('is_late')->default(false);
            $table->timestamp('checkout_at')->nullable();
            $table->decimal('checkout_lat', 10, 7)->nullable();
            $table->decimal('checkout_lng', 10, 7)->nullable();
            $table->decimal('checkout_accuracy', 8, 2)->nullable();
            $table->decimal('checkout_distance', 8, 2)->nullable();
            $table->timestamps();
            $table->unique(['teacher_id', 'date']);
        });

        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_id')->constrained('attendances')->cascadeOnDelete();
            $table->date('date')->index();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->string('topic');
            $table->string('method');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('student_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->constrained('parents')->cascadeOnDelete();
            $table->date('date_from');
            $table->date('date_to');
            $table->text('reason');
            $table->string('attachment')->nullable();
            $table->enum('status', ['submitted', 'approved', 'rejected'])->default('submitted');
            $table->timestamps();
        });

        Schema::create('class_attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->date('date')->index();
            $table->timestamps();
            $table->unique(['teacher_id', 'class_id', 'subject_id', 'date'], 'session_unique');
        });

        Schema::create('class_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('class_attendance_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpa'])->default('hadir');
            $table->string('note')->nullable();
            $table->timestamps();
            $table->unique(['session_id', 'student_id']);
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('school_lat', 10, 7)->default(-7.2574720);
            $table->decimal('school_lng', 10, 7)->default(112.7520883);
            $table->integer('radius_m')->default(150);
            $table->time('start_time')->default('07:00:00');
            $table->time('late_tolerance_time')->default('07:15:00');
            $table->boolean('izin_requires_approval')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('class_attendances');
        Schema::dropIfExists('class_attendance_sessions');
        Schema::dropIfExists('student_permissions');
        Schema::dropIfExists('journals');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('parent_student');
        Schema::dropIfExists('students');
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('parents');
        Schema::dropIfExists('teachers');
    }
};
