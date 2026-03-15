<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('national_holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('title');
            $table->boolean('is_national_holiday')->default(true);
            $table->string('source')->default('nager');
            $table->json('source_payload')->nullable();
            $table->timestamps();

            $table->unique(['date', 'title']);
            $table->index('date');
            $table->index('is_national_holiday');
        });

        Schema::create('school_calendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('event_type', 50);
            $table->string('color', 20)->nullable();
            $table->boolean('is_school_day')->default(true);
            $table->boolean('disable_teacher_attendance')->default(false);
            $table->boolean('disable_student_attendance')->default(false);
            $table->boolean('disable_journal')->default(false);
            $table->boolean('disable_substitute_generation')->default(false);
            $table->boolean('disable_kpi_penalty')->default(false);
            $table->boolean('show_on_dashboard')->default(true);
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['start_date', 'end_date']);
            $table->index('event_type');
            $table->index('active');
            $table->index('show_on_dashboard');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_calendar_events');
        Schema::dropIfExists('national_holidays');
    }
};
