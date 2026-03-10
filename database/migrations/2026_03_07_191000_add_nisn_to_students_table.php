<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                if (! Schema::hasColumn('students', 'nisn')) {
                    $table->string('nisn')->nullable()->after('nis');
                }
            });

            DB::table('students')
                ->whereNull('nisn')
                ->whereNotNull('nis')
                ->update(['nisn' => DB::raw('nis')]);

            Schema::table('students', function (Blueprint $table) {
                if (Schema::hasColumn('students', 'nis')) {
                    $table->string('nis')->nullable()->change();
                }

                $hasIndex = collect(DB::select("SHOW INDEX FROM students WHERE Key_name = 'students_nisn_unique'"))->isNotEmpty();
                if (! $hasIndex) {
                    $table->unique('nisn');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                if (Schema::hasColumn('students', 'nisn')) {
                    $table->dropUnique('students_nisn_unique');
                    $table->dropColumn('nisn');
                }

                if (Schema::hasColumn('students', 'nis')) {
                    $table->string('nis')->nullable(false)->change();
                }
            });
        }
    }
};
