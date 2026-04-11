<?php

namespace Database\Seeders;

use App\Models\RegistrationDocument;
use Illuminate\Database\Seeder;

class RegistrationDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $documents = [
            [
                'name'           => 'Ijazah',
                'label'          => 'Ijazah/SKL',
                'description'    => 'Ijazah terakhir atau Surat Keterangan Lulus (SKL)',
                'field_name'     => 'file_ijazah',
                'accepted_types' => 'pdf,jpg,jpeg,png',
                'max_size'       => 2048,
                'is_required'    => false,
                'order'          => 1,
                'is_active'      => true,
            ],
            [
                'name'           => 'Kartu Keluarga',
                'label'          => 'Kartu Keluarga (KK)',
                'description'    => 'Kartu Keluarga dari orang tua/wali',
                'field_name'     => 'file_kk',
                'accepted_types' => 'pdf,jpg,jpeg,png',
                'max_size'       => 2048,
                'is_required'    => true,
                'order'          => 2,
                'is_active'      => true,
            ],
            [
                'name'           => 'Akta Kelahiran',
                'label'          => 'Akta Kelahiran',
                'description'    => 'Akta kelahiran siswa',
                'field_name'     => 'file_akta',
                'accepted_types' => 'pdf,jpg,jpeg,png',
                'max_size'       => 2048,
                'is_required'    => true,
                'order'          => 3,
                'is_active'      => true,
            ],
            [
                'name'           => 'Raport SMP',
                'label'          => 'Raport SMP',
                'description'    => 'Semester 1-5',
                'field_name'     => 'file_raport',
                'accepted_types' => 'pdf',
                'max_size'       => 2048,
                'is_required'    => true,
                'order'          => 3,
                'is_active'      => true,
            ],
            [
                'name'           => 'Pas Foto',
                'label'          => 'Pas Foto (3x4)',
                'description'    => 'Pas foto terbaru ',
                'field_name'     => 'file_pas_photo',
                'accepted_types' => 'jpg,jpeg,png',
                'max_size'       => 1024,
                'is_required'    => true,
                'order'          => 4,
                'is_active'      => true,
            ],
        ];

        foreach ($documents as $doc) {
            RegistrationDocument::create($doc);
        }
    }
}