<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('settings') && ! Schema::hasColumn('settings', 'attendance_journal_open_enabled')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->boolean('attendance_journal_open_enabled')
                    ->default(false)
                    ->after('journal_lock_enabled');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'attendance_journal_open_enabled')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('attendance_journal_open_enabled');
            });
        }
    }
};
