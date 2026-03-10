<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('class_attendance_sessions', function (Blueprint $table) {
            $table->boolean('override_allowed')->default(false)->after('jam_ke');
            $table->text('override_reason')->nullable()->after('override_allowed');
            $table->foreignId('override_allowed_by')->nullable()->after('override_reason')->constrained('users')->nullOnDelete();
            $table->dateTime('override_expires_at')->nullable()->after('override_allowed_by');
            $table->index(['date', 'override_allowed']);
        });
    }

    public function down(): void
    {
        Schema::table('class_attendance_sessions', function (Blueprint $table) {
            $table->dropIndex(['date', 'override_allowed']);
            $table->dropConstrainedForeignId('override_allowed_by');
            $table->dropColumn(['override_allowed', 'override_reason', 'override_expires_at']);
        });
    }
};
