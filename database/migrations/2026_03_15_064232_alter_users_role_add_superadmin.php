<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin','superadmin','teacher','parent') NOT NULL DEFAULT 'teacher'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE users SET role = 'admin' WHERE role = 'superadmin'");
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin','teacher','parent') NOT NULL DEFAULT 'teacher'");
        }
    }
};
