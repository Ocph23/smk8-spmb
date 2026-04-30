<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $announcements = [
            [
                'title' => 'Pendaftaran Gelombang 1 dibuka',
                'category' => 'important',
                'content' => 'Calon peserta didik dapat mulai mendaftar dan melengkapi berkas pada gelombang pertama.',
                'link_text' => 'Cek Kelulusan',
                'link_url' => route('announcement.index'),
                'is_pinned' => true,
                'is_active' => true,
                'publish_at' => now()->subDay(),
            ],
            [
                'title' => 'Verifikasi berkas maksimal 3 hari kerja',
                'category' => 'info',
                'content' => 'Panitia akan memeriksa dokumen yang diunggah berdasarkan urutan masuk dan kelengkapan file.',
                'link_text' => 'Lihat Jadwal',
                'link_url' => url('/#jadwal'),
                'is_pinned' => false,
                'is_active' => true,
                'publish_at' => now()->subHours(6),
            ],
            [
                'title' => 'Pengumuman hasil seleksi menyusul',
                'category' => 'result',
                'content' => 'Hasil seleksi akan diumumkan melalui halaman pengumuman setelah proses penilaian selesai.',
                'link_text' => 'Buka Pengumuman',
                'link_url' => route('announcement.index'),
                'is_pinned' => false,
                'is_active' => true,
                'publish_at' => now()->subHours(2),
            ],
        ];

        foreach ($announcements as $announcement) {
            Announcement::updateOrCreate(
                ['title' => $announcement['title']],
                $announcement
            );
        }
    }
}
