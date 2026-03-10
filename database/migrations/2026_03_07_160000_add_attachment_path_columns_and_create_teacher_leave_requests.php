<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('teacher_leave_requests')) {
            Schema::create('teacher_leave_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
                $table->date('date_from');
                $table->date('date_to');
                $table->text('reason');
                $table->string('attachment_path')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('reviewed_at')->nullable();
                $table->text('review_notes')->nullable();
                $table->timestamps();

                $table->index(['teacher_id', 'status']);
                $table->index(['date_from', 'date_to']);
            });
        }

        if (Schema::hasTable('student_permissions') && ! Schema::hasColumn('student_permissions', 'attachment_path')) {
            Schema::table('student_permissions', function (Blueprint $table) {
                $table->string('attachment_path')->nullable()->after('attachment');
            });

            if (Schema::hasColumn('student_permissions', 'attachment')) {
                DB::table('student_permissions')
                    ->whereNotNull('attachment')
                    ->update(['attachment_path' => DB::raw('attachment')]);
            }
        }

        if (Schema::hasTable('teaching_journals') && ! Schema::hasColumn('teaching_journals', 'attachment_path')) {
            Schema::table('teaching_journals', function (Blueprint $table) {
                $table->string('attachment_path')->nullable()->after('mastery_notes');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('teaching_journals') && Schema::hasColumn('teaching_journals', 'attachment_path')) {
            Schema::table('teaching_journals', function (Blueprint $table) {
                $table->dropColumn('attachment_path');
            });
        }

        if (Schema::hasTable('student_permissions') && Schema::hasColumn('student_permissions', 'attachment_path')) {
            Schema::table('student_permissions', function (Blueprint $table) {
                $table->dropColumn('attachment_path');
            });
        }

        Schema::dropIfExists('teacher_leave_requests');
    }
};
