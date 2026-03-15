<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('duty_teacher_assignments') || ! Schema::hasColumn('duty_teacher_assignments', 'day_of_week')) {
            return;
        }

        DB::statement("ALTER TABLE duty_teacher_assignments MODIFY day_of_week ENUM('monday','tuesday','wednesday','thursday','friday','saturday') NULL");
    }

    public function down(): void
    {
        if (! Schema::hasTable('duty_teacher_assignments') || ! Schema::hasColumn('duty_teacher_assignments', 'day_of_week')) {
            return;
        }

        DB::statement("ALTER TABLE duty_teacher_assignments MODIFY day_of_week ENUM('monday','tuesday','wednesday','thursday','friday') NULL");
    }
};
