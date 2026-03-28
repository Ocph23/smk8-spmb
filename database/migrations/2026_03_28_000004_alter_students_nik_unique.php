<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            try {
                $table->dropUnique('students_nik_unique');
            } catch (\Exception $e) {
                // Index mungkin sudah tidak ada atau memiliki nama berbeda
            }

            $table->unique(['nik', 'academic_year_id'], 'uq_nik_per_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique('uq_nik_per_year');
            $table->unique('nik', 'students_nik_unique');
        });
    }
};
