<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('school_name')->nullable()->after('parent_phone');
            $table->string('school_city')->nullable()->after('school_name');
            $table->string('school_province')->nullable()->after('school_city');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['school_name', 'school_city', 'school_province']);
        });
    }
};
