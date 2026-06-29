<?php

use App\Models\EnrollmentWave;
use App\Models\Student;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollment_waves', function (Blueprint $table) {
            $table->unsignedInteger('registration_sequence')->default(0)->after('wave_number');
        });

        EnrollmentWave::query()
            ->select(['id'])
            ->chunkById(100, function ($waves) {
                foreach ($waves as $wave) {
                    $maxSequence = Student::where('enrollment_wave_id', $wave->id)
                        ->where('registration_number', 'like', 'SPMB-%')
                        ->pluck('registration_number')
                        ->map(function (string $registrationNumber) {
                            $parts = explode('-', $registrationNumber);
                            $sequence = end($parts);

                            return ctype_digit($sequence) ? (int) $sequence : 0;
                        })
                        ->max() ?? 0;

                    EnrollmentWave::whereKey($wave->id)->update([
                        'registration_sequence' => $maxSequence,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('enrollment_waves', function (Blueprint $table) {
            $table->dropColumn('registration_sequence');
        });
    }
};
