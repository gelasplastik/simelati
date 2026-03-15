<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('teacher_leave_requests')) {
            Schema::table('teacher_leave_requests', function (Blueprint $table) {
                if (! Schema::hasColumn('teacher_leave_requests', 'affects_teaching_schedule')) {
                    $table->boolean('affects_teaching_schedule')->default(false)->after('reason');
                }
                if (! Schema::hasColumn('teacher_leave_requests', 'proposed_substitute_teacher_id')) {
                    $table->foreignId('proposed_substitute_teacher_id')
                        ->nullable()
                        ->after('affects_teaching_schedule')
                        ->constrained('teachers')
                        ->nullOnDelete();
                }
                if (! Schema::hasColumn('teacher_leave_requests', 'coverage_notes')) {
                    $table->text('coverage_notes')->nullable()->after('proposed_substitute_teacher_id');
                }
            });
        }

        if (! Schema::hasTable('teacher_substitute_assignments')) {
            Schema::create('teacher_substitute_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('leave_id')->constrained('teacher_leave_requests')->cascadeOnDelete();
                $table->foreignId('original_teacher_id')->constrained('teachers')->cascadeOnDelete();
                $table->foreignId('substitute_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
                $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
                $table->date('date')->index();
                $table->foreignId('schedule_profile_id')->constrained('schedule_profiles')->cascadeOnDelete();
                $table->unsignedTinyInteger('jam_ke');
                $table->string('substitution_type')->default('substitute_teacher');
                $table->enum('status', ['pending', 'assigned', 'completed', 'cancelled'])->default('pending')->index();
                $table->text('notes')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(
                    ['original_teacher_id', 'class_id', 'subject_id', 'date', 'jam_ke', 'schedule_profile_id'],
                    'substitute_unique_session'
                );
                $table->index(['substitute_teacher_id', 'date', 'jam_ke'], 'substitute_lookup_idx');
            });
        }

        if (Schema::hasTable('class_attendance_sessions')) {
            Schema::table('class_attendance_sessions', function (Blueprint $table) {
                if (! Schema::hasColumn('class_attendance_sessions', 'original_teacher_id')) {
                    $table->foreignId('original_teacher_id')
                        ->nullable()
                        ->after('teacher_id')
                        ->constrained('teachers')
                        ->nullOnDelete();
                }
                if (! Schema::hasColumn('class_attendance_sessions', 'executing_teacher_id')) {
                    $table->foreignId('executing_teacher_id')
                        ->nullable()
                        ->after('original_teacher_id')
                        ->constrained('teachers')
                        ->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('class_attendance_sessions')) {
            Schema::table('class_attendance_sessions', function (Blueprint $table) {
                if (Schema::hasColumn('class_attendance_sessions', 'executing_teacher_id')) {
                    $table->dropConstrainedForeignId('executing_teacher_id');
                }
                if (Schema::hasColumn('class_attendance_sessions', 'original_teacher_id')) {
                    $table->dropConstrainedForeignId('original_teacher_id');
                }
            });
        }

        Schema::dropIfExists('teacher_substitute_assignments');

        if (Schema::hasTable('teacher_leave_requests')) {
            Schema::table('teacher_leave_requests', function (Blueprint $table) {
                if (Schema::hasColumn('teacher_leave_requests', 'coverage_notes')) {
                    $table->dropColumn('coverage_notes');
                }
                if (Schema::hasColumn('teacher_leave_requests', 'proposed_substitute_teacher_id')) {
                    $table->dropConstrainedForeignId('proposed_substitute_teacher_id');
                }
                if (Schema::hasColumn('teacher_leave_requests', 'affects_teaching_schedule')) {
                    $table->dropColumn('affects_teaching_schedule');
                }
            });
        }
    }
};

