# Implementation Plan: Panitia Role

## Overview

Menambahkan role `panitia` ke sistem SPMB SMKN 8 secara additive — tanpa mengubah logika admin yang sudah ada. Implementasi mencakup: migrasi database, middleware baru, controller CRUD panitia, penyesuaian route, dan penyesuaian UI kondisional di frontend Vue.

## Tasks

- [x] 1. Setup database dan model
  - Buat migration baru untuk menambah nilai `panitia` ke enum kolom `role` di tabel `users`
  - Tambahkan method `isPanitia()` dan `isAdminOrPanitia()` ke model `app/Models/User.php`
  - _Requirements: 2.3, 9.2_

- [x] 2. Buat middleware `EnsureUserIsPanitia`
  - [x] 2.1 Buat file `app/Http/Middleware/EnsureUserIsPanitia.php`
    - Izinkan akses jika `role === 'admin'` atau `role === 'panitia'`, tolak dengan 403 jika tidak
    - Redirect ke login jika tidak terautentikasi
    - _Requirements: 2.3, 2.4, 2.5_

  - [ ]* 2.2 Tulis property test untuk middleware EnsureUserIsPanitia
    - **Property 3: Middleware mengizinkan admin dan panitia, menolak yang lain**
    - **Validates: Requirements 2.3, 2.4, 2.5**

  - [x] 2.3 Daftarkan middleware di `bootstrap/app.php` dengan alias `panitia`
    - _Requirements: 2.3_

- [x] 3. Buat `PanitiaController` untuk manajemen akun panitia
  - [x] 3.1 Buat file `app/Http/Controllers/PanitiaController.php`
    - Implementasikan method `index()`: ambil semua user dengan `role = 'panitia'`, render ke Inertia
    - Implementasikan method `store(Request)`: validasi nama/email/password, simpan user baru dengan `role = 'panitia'`
    - Implementasikan method `update(Request, User)`: validasi dan update nama/email
    - Implementasikan method `destroy(User)`: hapus akun panitia
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.7_

  - [ ]* 3.2 Tulis property test: akun panitia selalu tersimpan dengan role yang benar
    - **Property 1: Role panitia selalu benar saat store**
    - **Validates: Requirements 1.2**

  - [ ]* 3.3 Tulis property test: daftar panitia mencakup semua akun yang ada
    - **Property 2: Daftar mencakup semua akun**
    - **Validates: Requirements 1.7**

- [x] 4. Tambahkan route untuk panitia management dan sesuaikan route yang ada
  - [x] 4.1 Tambahkan route CRUD panitia di `routes/web.php` dalam group middleware `['auth', 'verified', 'admin']`
    - `GET /admin/panitia` → `PanitiaController@index` (name: `admin.panitia`)
    - `POST /admin/panitia` → `PanitiaController@store` (name: `admin.panitia.store`)
    - `PUT /admin/panitia/{user}` → `PanitiaController@update` (name: `admin.panitia.update`)
    - `DELETE /admin/panitia/{user}` → `PanitiaController@destroy` (name: `admin.panitia.destroy`)
    - _Requirements: 1.1, 1.6_

  - [x] 4.2 Pindahkan route yang dapat diakses panitia ke group middleware `['auth', 'verified', 'panitia']`
    - Route yang dipindah: `GET /dashboard`, `GET /admin/students`, `GET /admin/students/{student}`, `POST /admin/students/{student}/verify`, `DELETE /admin/students/{student}`, semua route `/admin/inbox`, `GET /admin/reports`
    - Route yang tetap di group `admin`: `POST /admin/students/{student}/allocate`, `GET /admin/reports/pdf`, `GET /admin/reports/csv`, semua route konfigurasi sistem
    - _Requirements: 3.1, 4.1, 5.1, 6.1, 7.1, 8.1–8.6_

  - [ ]* 4.3 Tulis property test: panitia mendapat 403 di semua route eksklusif admin
    - **Property 7: Panitia 403 di admin-only routes**
    - **Validates: Requirements 7.4, 8.1, 8.2, 8.3, 8.4, 8.5, 8.6**

- [x] 5. Checkpoint — Pastikan semua test backend lolos
  - Pastikan semua test lolos, tanyakan ke user jika ada pertanyaan.

- [x] 6. Buat halaman Vue manajemen akun panitia
  - [x] 6.1 Buat `resources/js/Pages/Admin/Panitia/Index.vue`
    - Tampilkan tabel daftar akun panitia (nama, email, tanggal dibuat)
    - Form inline atau modal untuk create akun baru (nama, email, password, konfirmasi password)
    - Aksi edit (nama, email) dan hapus per baris
    - Gunakan pola yang sama dengan halaman admin lain yang sudah ada (misal `Admin/Majors`)
    - _Requirements: 1.1, 1.2, 1.4, 1.5, 1.7_

- [x] 7. Sesuaikan komponen navigasi untuk tampilan kondisional
  - [x] 7.1 Modifikasi komponen sidebar/navbar yang ada di `resources/js`
    - Sembunyikan menu eksklusif admin (Tahun Ajaran, Jurusan, Jadwal, Template Dokumen, Berkas Pendaftaran, Manajemen Panitia) jika `$page.props.auth.user.role !== 'admin'`
    - Tambahkan link "Manajemen Panitia" ke menu yang hanya tampil untuk admin
    - _Requirements: 8.7, 9.1, 9.4_

  - [ ]* 7.2 Tulis unit test frontend: menu admin-only tersembunyi untuk panitia
    - Gunakan Vitest + Vue Test Utils
    - Mock `$page.props.auth.user.role = 'panitia'`, assert menu admin-only tidak ter-render
    - _Requirements: 8.7, 9.1_

- [x] 8. Sesuaikan halaman laporan untuk menyembunyikan tombol ekspor
  - [x] 8.1 Modifikasi `resources/js/Pages/Admin/Reports/Index.vue`
    - Bungkus tombol "Export PDF" dan "Export CSV" dengan `v-if="$page.props.auth.user.role === 'admin'"`
    - _Requirements: 7.3_

  - [ ]* 8.2 Tulis unit test frontend: tombol ekspor tersembunyi untuk panitia
    - Mock role panitia, assert tombol ekspor tidak ter-render
    - _Requirements: 7.3_

- [x] 9. Pastikan shared props menyertakan field role
  - Verifikasi bahwa `HandleInertiaRequests` sudah meneruskan `auth.user` (termasuk `role`) ke frontend
  - Jika `role` belum di-expose, tambahkan secara eksplisit ke array `auth` di `share()`
  - _Requirements: 9.2_

  - [ ]* 9.1 Tulis property test: shared props selalu menyertakan role pengguna
    - **Property 8: Shared props menyertakan role**
    - **Validates: Requirements 9.2**

- [x] 10. Tambahkan seeder akun panitia contoh (opsional untuk development)
  - [x] 10.1 Modifikasi `database/seeders/AdminUserSeeder.php` atau buat `PanitiaUserSeeder.php`
    - Tambahkan satu akun panitia contoh untuk keperluan development/testing
    - _Requirements: 1.2_

- [x] 11. Checkpoint akhir — Pastikan semua test lolos
  - Pastikan semua test lolos, tanyakan ke user jika ada pertanyaan.

## Notes

- Tasks bertanda `*` bersifat opsional dan dapat dilewati untuk MVP yang lebih cepat
- Property tests menggunakan library **eris/eris** dengan minimum 100 iterasi per property
- Setiap task mereferensikan requirement spesifik untuk traceability
- Implementasi bersifat additive — tidak ada perubahan breaking pada logika admin yang sudah ada
- Route group yang ada dipecah menjadi dua: `admin`-only dan `panitia`-accessible
