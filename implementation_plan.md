# Goal Description

Membangun Aplikasi Penerimaan Peserta Didik Baru (PPDB) berbasis web (SPA) untuk SMKN 8 TIK Kota Jayapura menggunakan Laravel, Vue.js 3, Inertia.js, dan Tailwind CSS. Aplikasi akan melayani fungsi pendaftaran, pemilihan jurusan, manajemen jadwal, alokasi jurusan, serta pengumuman kelulusan.

## User Review Required

- Di lingkungan Anda (Windows), kita akan memerlukan server database. Apakah Anda sudah memiliki MySQL services yang berjalan (misalnya melalui XAMPP/Laragon), atau apakah Anda memilih menggunakan **SQLite** (lebih praktis untuk setup awal)?
- Pembuatan proyek baru akan dinamai `ppdb-smkn8` dan akan diletakkan di dalam folder `d:\smk8\spmb` berdasarkan informasi workspace saat ini. (Apakah ini sudah tepat?)

## Proposed Changes

### Setup and Configuration
- Membuat proyek Laravel `composer create-project laravel/laravel ppdb-smkn8`.
- Integrasi *Laravel Breeze* menggunakan Vue dan Inertia.
- Setup layout standar menggunakan Tailwind CSS.

### Database Schema
- `users`: (Bawaan) Admin login / Student login.
- `majors`: Jurusan yang tersedia (contoh: RPL, TKJ, DKV/Multimedia).
- `students`: Berisi biodata (TTL, dll) dan path file uploaan berkas (Ijazah, KK, Akta, Pas Foto), serta status verifikasi dan status lulus (ditempatkan di jurusan mana).
- `student_majors`: Pilihan 1, Pilihan 2, Pilihan 3.
- `schedules`: Timeline acara PPDB.

### Backend (Laravel) Structure
- `StudentController`: Menghandle submit pendaftaran, berkas, dan pemilihan jurusan.
- `AdminController`: Dashboard admin untuk verifikasi dan alokasi.
- `ScheduleController`: Menghandle timeline.
- `AnnouncementController`: Menghandle pencarian nomor pendaftaran.
- Menggunakan `barryvdh/laravel-dompdf` untuk generate PDF bukti pendaftaran.

### Frontend (Vue 3 + Tailwind CSS) Pages
- Home / Landing Page: Menampilkan `schedules` dan input Cek Kelulusan.
- Student Registration Form: Form dinamis dengan fitur multi-step (opsional) atau halaman terpisah untuk biodata, upload berkas, dan pilihan jurusan.
- Admin Dashboard: Tabel reaktif daftar pendaftar dan aksi verifikasi.

## Verification Plan

### Automated/Manual Testing
- Mengakses via browser localhost (`npm run dev` & `php artisan serve`).
- Skenario pendaftaran sebagai siswa.
- Skenario memvalidasi pendaftar dan mengalokasikan jurusan sebagai admin.
- Uji cetak PDF.
