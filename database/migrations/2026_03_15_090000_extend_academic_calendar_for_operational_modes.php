<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('national_holidays', function (Blueprint $table) {
            if (! Schema::hasColumn('national_holidays', 'entry_type')) {
                $table->string('entry_type', 30)->default('national_holiday')->after('title');
            }

            if (! Schema::hasColumn('national_holidays', 'is_collective_leave')) {
                $table->boolean('is_collective_leave')->default(false)->after('is_national_holiday');
            }
        });

        Schema::table('school_calendar_events', function (Blueprint $table) {
            if (! Schema::hasColumn('school_calendar_events', 'operational_mode')) {
                $table->string('operational_mode', 30)->default('custom')->after('event_type');
            }
        });

        Schema::table('national_holidays', function (Blueprint $table) {
            try {
                $table->dropUnique('national_holidays_date_title_unique');
            } catch (\Throwable) {
                // Ignore if already dropped.
            }

            $table->unique(['date', 'title', 'entry_type'], 'national_holidays_date_title_entry_type_unique');
            $table->index('entry_type');
            $table->index('is_collective_leave');
        });

        Schema::table('school_calendar_events', function (Blueprint $table) {
            $table->index('operational_mode');
        });
    }

    public function down(): void
    {
        Schema::table('school_calendar_events', function (Blueprint $table) {
            try {
                $table->dropIndex(['operational_mode']);
            } catch (\Throwable) {
                // Ignore.
            }

            if (Schema::hasColumn('school_calendar_events', 'operational_mode')) {
                $table->dropColumn('operational_mode');
            }
        });

        Schema::table('national_holidays', function (Blueprint $table) {
            try {
                $table->dropUnique('national_holidays_date_title_entry_type_unique');
            } catch (\Throwable) {
                // Ignore.
            }

            try {
                $table->dropIndex(['entry_type']);
                $table->dropIndex(['is_collective_leave']);
            } catch (\Throwable) {
                // Ignore.
            }

            if (Schema::hasColumn('national_holidays', 'entry_type')) {
                $table->dropColumn('entry_type');
            }
            if (Schema::hasColumn('national_holidays', 'is_collective_leave')) {
                $table->dropColumn('is_collective_leave');
            }

            $table->unique(['date', 'title']);
        });
    }
};
