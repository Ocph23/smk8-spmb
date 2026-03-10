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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('registration_number')->unique(); // Nomor pendaftaran
            $table->string('full_name');
            $table->string('nik', 16)->unique();
            $table->string('nisn', 10)->nullable();
            $table->string('place_of_birth');
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female']);
            $table->string('religion')->nullable();
            $table->text('address');
            $table->string('phone');
            $table->string('email');
            $table->string('parent_name');
            $table->string('parent_phone');
            // File uploads
            $table->string('file_ijazah')->nullable();
            $table->string('file_kk')->nullable();
            $table->string('file_akta')->nullable();
            $table->string('file_pas_photo')->nullable();
            // Status
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('verification_note')->nullable();
            $table->boolean('is_accepted')->default(false); // Status kelulusan
            $table->foreignId('accepted_major_id')->nullable()->constrained('majors')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
