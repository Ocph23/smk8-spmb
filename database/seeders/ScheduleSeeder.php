<?php

namespace Database\Seeders;

use App\Models\Schedule;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schedules = [
            [
                'title' => 'Pendaftaran Gelombang 1',
                'description' => 'Pendaftaran Peserta Didik Baru gelombang pertama untuk tahun ajaran 2026/2027.',
                'start_date' => '2026-05-01',
                'end_date' => '2026-06-15',
                'status' => 'active',
            ],
            [
                'title' => 'Pendaftaran Gelombang 2',
                'description' => 'Pendaftaran Peserta Didik Baru gelombang kedua (jika kuota masih tersedia).',
                'start_date' => '2026-06-16',
                'end_date' => '2026-07-15',
                'status' => 'inactive',
            ],
            [
                'title' => 'Pengumuman Hasil Seleksi',
                'description' => 'Pengumuman hasil seleksi SPMB berdasarkan nilai dan kuota jurusan.',
                'start_date' => '2026-07-20',
                'end_date' => '2026-07-25',
                'status' => 'inactive',
            ],
            [
                'title' => 'Daftar Ulang',
                'description' => 'Proses daftar ulang bagi peserta yang dinyatakan lulus seleksi.',
                'start_date' => '2026-07-26',
                'end_date' => '2026-08-05',
                'status' => 'inactive',
            ],
        ];

        foreach ($schedules as $schedule) {
            Schedule::create($schedule);
        }
    }
}
