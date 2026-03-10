<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('student_permissions')) {
            return;
        }

        Schema::table('student_permissions', function (Blueprint $table) {
            if (Schema::hasColumn('student_permissions', 'parent_id')) {
                $table->dropForeign(['parent_id']);
                $table->foreignId('parent_id')->nullable()->change();
                $table->foreign('parent_id')->references('id')->on('parents')->nullOnDelete();
            }

            if (! Schema::hasColumn('student_permissions', 'submitter_name')) {
                $table->string('submitter_name')->nullable()->after('parent_id');
            }

            if (! Schema::hasColumn('student_permissions', 'submitter_phone')) {
                $table->string('submitter_phone')->nullable()->after('submitter_name');
            }

            if (! Schema::hasColumn('student_permissions', 'submitter_relationship')) {
                $table->string('submitter_relationship')->nullable()->after('submitter_phone');
            }

            if (! Schema::hasColumn('student_permissions', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('attachment_path');
            }

            if (! Schema::hasColumn('student_permissions', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }

            $table->index('status');
            $table->index('date_from');
            $table->index('date_to');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('student_permissions')) {
            return;
        }

        Schema::table('student_permissions', function (Blueprint $table) {
            if (Schema::hasColumn('student_permissions', 'user_agent')) {
                $table->dropColumn('user_agent');
            }
            if (Schema::hasColumn('student_permissions', 'ip_address')) {
                $table->dropColumn('ip_address');
            }
            if (Schema::hasColumn('student_permissions', 'submitter_relationship')) {
                $table->dropColumn('submitter_relationship');
            }
            if (Schema::hasColumn('student_permissions', 'submitter_phone')) {
                $table->dropColumn('submitter_phone');
            }
            if (Schema::hasColumn('student_permissions', 'submitter_name')) {
                $table->dropColumn('submitter_name');
            }

            if (Schema::hasColumn('student_permissions', 'parent_id')) {
                $table->dropForeign(['parent_id']);
                $table->foreignId('parent_id')->nullable(false)->change();
                $table->foreign('parent_id')->references('id')->on('parents')->cascadeOnDelete();
            }

            $table->dropIndex(['status']);
            $table->dropIndex(['date_from']);
            $table->dropIndex(['date_to']);
        });
    }
};
