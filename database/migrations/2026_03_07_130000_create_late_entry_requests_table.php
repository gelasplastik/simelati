<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('late_entry_requests')) {
            return;
        }

        Schema::create('late_entry_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->date('date');
            $table->unsignedTinyInteger('jam_ke');
            $table->enum('request_type', ['attendance', 'journal', 'both'])->default('both');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->index('teacher_id');
            $table->index('status');
            $table->index('date');
            $table->index(['teacher_id', 'date', 'class_id', 'subject_id', 'jam_ke'], 'ler_teacher_session_idx');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('late_entry_requests')) {
            Schema::drop('late_entry_requests');
        }
    }
};
