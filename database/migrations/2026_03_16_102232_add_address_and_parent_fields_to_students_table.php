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
            // Make old address column nullable
            $table->text('address')->nullable()->change();
            
            // Address fields (separated)
            $table->string('street')->nullable()->after('address');
            $table->string('rt')->nullable()->after('street');
            $table->string('rw')->nullable()->after('rt');
            $table->string('dusun')->nullable()->after('rw');
            $table->string('district')->nullable()->after('dusun');
            $table->string('postal_code', 10)->nullable()->after('district');
            
            // Parent fields
            $table->string('mother_name')->nullable()->after('parent_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'street',
                'rt',
                'rw',
                'dusun',
                'district',
                'postal_code',
                'mother_name',
            ]);
        });
    }
};
