<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('duty_teacher_assignments')) {
            Schema::create('duty_teacher_assignments', function (Blueprint $table) {
                $table->id();
                $table->date('date')->index();
                $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
                $table->text('notes')->nullable();
                $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['date', 'teacher_id']);
            });
        }

        if (! Schema::hasTable('duty_reports')) {
            Schema::create('duty_reports', function (Blueprint $table) {
                $table->id();
                $table->date('date')->unique();
                $table->foreignId('duty_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
                $table->enum('status', ['draft', 'finalized'])->default('draft')->index();
                $table->text('notes')->nullable();
                $table->timestamp('finalized_at')->nullable();
                $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('duty_report_teacher_rows')) {
            Schema::create('duty_report_teacher_rows', function (Blueprint $table) {
                $table->id();
                $table->foreignId('report_id')->constrained('duty_reports')->cascadeOnDelete();
                $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
                $table->string('teacher_name');
                $table->string('subject_label')->nullable();
                $table->enum('attendance_status', ['present', 'leave', 'absent'])->default('absent');
                $table->enum('verified_status', ['present', 'leave', 'absent'])->nullable();
                $table->text('reason')->nullable();
                $table->boolean('has_substitute')->default(false);
                $table->foreignId('substitute_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
                $table->string('substitute_teacher_name')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['report_id', 'teacher_id']);
            });
        }

        if (! Schema::hasTable('duty_report_student_rows')) {
            Schema::create('duty_report_student_rows', function (Blueprint $table) {
                $table->id();
                $table->foreignId('report_id')->constrained('duty_reports')->cascadeOnDelete();
                $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
                $table->string('class_name');
                $table->unsignedInteger('total_students')->default(0);
                $table->unsignedInteger('present_count')->default(0);
                $table->unsignedInteger('sick_count')->default(0);
                $table->unsignedInteger('izin_count')->default(0);
                $table->unsignedInteger('alpa_count')->default(0);
                $table->unsignedInteger('late_count')->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['report_id', 'class_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('duty_report_student_rows');
        Schema::dropIfExists('duty_report_teacher_rows');
        Schema::dropIfExists('duty_reports');
        Schema::dropIfExists('duty_teacher_assignments');
    }
};
