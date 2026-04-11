<?php

namespace Database\Seeders;

use App\Models\Major;
use Illuminate\Database\Seeder;

class MajorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $majors = [
            [
                'name' => 'Rekayasa Perangkat Lunak',
                'code' => 'RPL',
                'description' => 'Kompetensi keahlian yang mempelajari pengembangan perangkat lunak (software) untuk aplikasi desktop, web, dan mobile.',
                'quota' => 36,
            ],
            [
                'name' => 'Teknik Komputer dan Jaringan',
                'code' => 'TKJ',
                'description' => 'Kompetensi keahlian yang mempelajari perakitan komputer, instalasi jaringan, administrasi server, dan troubleshooting.',
                'quota' => 72,
            ],
            [
                'name' => 'Desain Komunikasi Visual',
                'code' => 'DKV',
                'description' => 'Kompetensi keahlian yang mempelajari desain grafis, fotografi, videografi, dan animasi untuk keperluan komunikasi visual.',
                'quota' => 36,
            ],
            [
                'name' => 'Teknik Kimia Industri',
                'code' => 'TKI',
                'description' => 'Kompetensi keahlian yang mempelajari kimia untuk keperluan industri.',
                'quota' => 36,
            ],
        ];

        foreach ($majors as $major) {
            Major::create($major);
        }
    }
}
