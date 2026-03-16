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
            // Make most fields nullable for initial account registration
            $table->string('full_name')->nullable()->change();
            $table->string('nik', 16)->nullable()->change();
            $table->string('nisn', 10)->nullable()->change();
            $table->string('place_of_birth')->nullable()->change();
            $table->date('date_of_birth')->nullable()->change();
            $table->enum('gender', ['male', 'female'])->nullable()->change();
            $table->string('religion')->nullable()->change();
            $table->text('address')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->string('parent_name')->nullable()->change();
            $table->string('mother_name')->nullable()->change();
            $table->string('parent_phone')->nullable()->change();
            
            // Keep email and password as required (for login)
            // Keep registration_number, verification_status as is
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Revert to not nullable
            $table->string('full_name')->nullable(false)->change();
            $table->string('nik', 16)->nullable(false)->change();
            $table->string('place_of_birth')->nullable(false)->change();
            $table->date('date_of_birth')->nullable(false)->change();
            $table->enum('gender', ['male', 'female'])->nullable(false)->change();
            $table->string('phone')->nullable(false)->change();
            $table->string('parent_name')->nullable(false)->change();
            $table->string('parent_phone')->nullable(false)->change();
        });
    }
};
