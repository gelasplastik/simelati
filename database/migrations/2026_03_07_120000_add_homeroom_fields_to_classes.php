<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('name');
            $table->foreignId('homeroom_teacher_id')->nullable()->after('is_active')->constrained('teachers')->nullOnDelete();
            $table->index(['is_active', 'homeroom_teacher_id']);
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'homeroom_teacher_id']);
            $table->dropConstrainedForeignId('homeroom_teacher_id');
            $table->dropColumn('is_active');
        });
    }
};
