<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['file_ijazah', 'file_kk', 'file_akta', 'file_pas_photo']);
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('file_ijazah')->nullable();
            $table->string('file_kk')->nullable();
            $table->string('file_akta')->nullable();
            $table->string('file_pas_photo')->nullable();
        });
    }
};
