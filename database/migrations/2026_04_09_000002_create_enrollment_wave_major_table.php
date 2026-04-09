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
        Schema::create('enrollment_wave_major', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_wave_id')->constrained()->onDelete('cascade');
            $table->foreignId('major_id')->constrained()->onDelete('cascade');
            $table->unsignedSmallInteger('quota')->default(0);
            $table->timestamps();

            $table->unique(['enrollment_wave_id', 'major_id'], 'uq_wave_major');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_wave_major');
    }
};
