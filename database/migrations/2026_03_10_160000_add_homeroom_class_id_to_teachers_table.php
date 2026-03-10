<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('teachers', 'homeroom_class_id')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->foreignId('homeroom_class_id')->nullable()->after('employee_code')->constrained('classes')->nullOnDelete();
                $table->index('homeroom_class_id');
            });

            DB::statement('
                UPDATE teachers t
                JOIN classes c ON c.homeroom_teacher_id = t.id
                SET t.homeroom_class_id = c.id
            ');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('teachers', 'homeroom_class_id')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->dropForeign(['homeroom_class_id']);
                $table->dropIndex(['homeroom_class_id']);
                $table->dropColumn('homeroom_class_id');
            });
        }
    }
};
