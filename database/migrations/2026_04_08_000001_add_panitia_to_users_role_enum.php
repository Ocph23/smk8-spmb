<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * SQLite does not support ENUM or ALTER COLUMN — the role column is stored
     * as a plain string in SQLite, so no DDL change is needed for that driver.
     * On MySQL/MariaDB we extend the ENUM to include 'panitia'.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'student', 'panitia') NOT NULL DEFAULT 'student'");
        }
        // SQLite stores enum as string — no DDL change required.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'student') NOT NULL DEFAULT 'student'");
        }
    }
};
