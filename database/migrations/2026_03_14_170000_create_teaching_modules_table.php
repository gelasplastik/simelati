<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('teaching_modules')) {
            return;
        }

        Schema::create('teaching_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->string('academic_year', 20);
            $table->enum('semester', ['ganjil', 'genap']);
            $table->string('title');
            $table->string('file_path');
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('submitted')->index();
            $table->text('teacher_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['teacher_id', 'subject_id', 'class_id', 'academic_year', 'semester'], 'teaching_modules_unique_scope');
            $table->index(['teacher_id', 'status']);
            $table->index(['academic_year', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_modules');
    }
};
