<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('duty_teacher_assignments')) {
            return;
        }

        Schema::table('duty_teacher_assignments', function (Blueprint $table) {
            if (! Schema::hasColumn('duty_teacher_assignments', 'day_of_week')) {
                $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'])
                    ->nullable()
                    ->after('date')
                    ->index();
            }
        });

        $assignments = DB::table('duty_teacher_assignments')
            ->whereNotNull('date')
            ->whereNull('day_of_week')
            ->select('id', 'date')
            ->get();

        foreach ($assignments as $row) {
            $day = strtolower((new DateTimeImmutable($row->date))->format('l'));
            if (in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'], true)) {
                DB::table('duty_teacher_assignments')->where('id', $row->id)->update(['day_of_week' => $day]);
            }
        }

        Schema::table('duty_teacher_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('duty_teacher_assignments', 'date')) {
                $table->date('date')->nullable()->change();
            }
        });

        try {
            Schema::table('duty_teacher_assignments', function (Blueprint $table) {
                $table->dropUnique('duty_teacher_assignments_date_teacher_id_unique');
            });
        } catch (Throwable) {
            // no-op
        }

        try {
            Schema::table('duty_teacher_assignments', function (Blueprint $table) {
                $table->unique(['day_of_week', 'teacher_id'], 'duty_teacher_assignments_day_teacher_unique');
            });
        } catch (Throwable) {
            // no-op
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('duty_teacher_assignments')) {
            return;
        }

        try {
            Schema::table('duty_teacher_assignments', function (Blueprint $table) {
                $table->dropUnique('duty_teacher_assignments_day_teacher_unique');
            });
        } catch (Throwable) {
            // no-op
        }

        Schema::table('duty_teacher_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('duty_teacher_assignments', 'day_of_week')) {
                $table->dropColumn('day_of_week');
            }
        });
    }
};

