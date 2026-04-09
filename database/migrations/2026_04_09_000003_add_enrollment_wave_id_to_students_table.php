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
            $table->unsignedBigInteger('enrollment_wave_id')->nullable()->after('academic_year_id');

            $table->foreign('enrollment_wave_id')
                ->references('id')
                ->on('enrollment_waves')
                ->onDelete('set null');

            $table->index('enrollment_wave_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['enrollment_wave_id']);
            $table->dropIndex(['enrollment_wave_id']);
            $table->dropColumn('enrollment_wave_id');
        });
    }
};
