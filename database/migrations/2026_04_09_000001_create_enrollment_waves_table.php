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
        Schema::create('enrollment_waves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->unsignedTinyInteger('wave_number');
            $table->enum('status', ['draft', 'open', 'closed', 'announced'])->default('draft');
            $table->date('open_date')->nullable();
            $table->date('close_date')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['academic_year_id', 'wave_number'], 'uq_wave_per_year');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_waves');
    }
};
