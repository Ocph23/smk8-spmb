# Requirements Document

## Introduction

Fitur ini menambahkan role baru bernama `panitia` pada sistem SPMB SMKN 8. Role `panitia` diperuntukkan bagi staf panitia penerimaan siswa baru yang membutuhkan akses operasional harian — seperti melihat data pendaftar, memverifikasi berkas, mengedit/menghapus data siswa, dan mengirim pesan inbox — namun tidak memiliki akses ke fungsi administratif seperti konfigurasi sistem, manajemen akun, alokasi jurusan, atau ekspor laporan.

Sistem saat ini memiliki dua role: `admin` dan `student`. Model `User` sudah memiliki kolom `role` (string). Middleware `EnsureUserIsAdmin` mengecek `role === 'admin'`. Semua route admin diproteksi dengan middleware `auth`, `verified`, `admin`.

## Glossary

- **System**: Aplikasi SPMB SMKN 8 berbasis Laravel + Inertia.js/Vue 3
- **Admin**: Pengguna dengan role `admin`, memiliki akses penuh ke seluruh fitur sistem
- **Panitia**: Pengguna dengan role `panitia`, memiliki akses operasional terbatas pada data pendaftar
- **Student**: Pendaftar siswa baru yang memiliki akun terpisah (guard `student`)
- **EnsureUserIsAdmin**: Middleware yang memvalidasi bahwa pengguna memiliki role `admin`
- **EnsureUserIsPanitia**: Middleware baru yang memvalidasi bahwa pengguna memiliki role `admin` atau `panitia`
- **Inbox**: Pesan yang dikirim dari sisi admin/panitia ke siswa
- **RegistrationDocument**: Konfigurasi jenis berkas yang harus diunggah siswa
- **StudentDocument**: Berkas yang diunggah oleh siswa
- **Verification_Status**: Status verifikasi berkas siswa (`pending`, `verified`, `rejected`)

---

## Requirements

### Requirement 1: Manajemen Akun Panitia oleh Admin

**User Story:** Sebagai admin, saya ingin dapat membuat, mengedit, dan menghapus akun panitia, agar staf panitia dapat mengakses sistem dengan hak akses yang sesuai.

#### Acceptance Criteria

1. THE System SHALL menyediakan halaman manajemen akun panitia yang hanya dapat diakses oleh pengguna dengan role `admin`.
2. WHEN admin membuat akun panitia baru dengan nama, email, dan password yang valid, THE System SHALL menyimpan akun tersebut dengan role `panitia` dan menampilkan konfirmasi keberhasilan.
3. WHEN admin membuat akun panitia dengan email yang sudah terdaftar, THE System SHALL menolak permintaan dan menampilkan pesan error yang menjelaskan bahwa email sudah digunakan.
4. WHEN admin memperbarui data akun panitia (nama atau email), THE System SHALL menyimpan perubahan dan menampilkan konfirmasi keberhasilan.
5. WHEN admin menghapus akun panitia, THE System SHALL menghapus akun tersebut secara permanen dan menampilkan konfirmasi keberhasilan.
6. IF pengguna dengan role `panitia` mengakses halaman manajemen akun panitia, THEN THE System SHALL menolak akses dengan respons HTTP 403.
7. THE System SHALL menampilkan daftar semua akun panitia yang ada, termasuk nama, email, dan tanggal pembuatan akun.

---

### Requirement 2: Autentikasi dan Otorisasi Role Panitia

**User Story:** Sebagai panitia, saya ingin dapat login ke sistem menggunakan akun yang telah dibuat admin, agar saya dapat mengakses fitur operasional yang sesuai dengan hak akses saya.

#### Acceptance Criteria

1. WHEN panitia melakukan login dengan email dan password yang valid, THE System SHALL mengautentikasi panitia dan mengarahkan ke dashboard panitia.
2. WHEN panitia melakukan login dengan email atau password yang tidak valid, THE System SHALL menolak login dan menampilkan pesan error autentikasi.
3. THE System SHALL menyediakan middleware `EnsureUserIsPanitia` yang mengizinkan akses hanya untuk pengguna dengan role `admin` atau `panitia`.
4. IF pengguna yang tidak terautentikasi mengakses route yang dilindungi middleware `EnsureUserIsPanitia`, THEN THE System SHALL mengarahkan pengguna ke halaman login.
5. IF pengguna dengan role `student` mengakses route yang dilindungi middleware `EnsureUserIsPanitia`, THEN THE System SHALL menolak akses dengan respons HTTP 403.
6. WHEN panitia melakukan logout, THE System SHALL mengakhiri sesi dan mengarahkan ke halaman login.

---

### Requirement 3: Akses Dashboard dan Daftar Pendaftar

**User Story:** Sebagai panitia, saya ingin dapat melihat dashboard dan daftar pendaftar harian, agar saya dapat memantau perkembangan penerimaan siswa baru.

#### Acceptance Criteria

1. WHEN panitia mengakses dashboard, THE System SHALL menampilkan statistik pendaftar yang mencakup total pendaftar, jumlah pending verifikasi, jumlah terverifikasi, dan jumlah diterima.
2. THE System SHALL menampilkan daftar pendaftar terbaru pada dashboard panitia.
3. WHEN panitia mengakses halaman daftar siswa, THE System SHALL menampilkan daftar pendaftar dengan informasi nama, nomor pendaftaran, status verifikasi, dan pilihan jurusan.
4. WHEN panitia menggunakan filter pencarian berdasarkan nama, nomor pendaftaran, atau NIK, THE System SHALL menampilkan hasil yang sesuai dengan kriteria pencarian.
5. WHEN panitia menggunakan filter berdasarkan status verifikasi atau jurusan, THE System SHALL menampilkan daftar pendaftar yang sesuai dengan filter yang dipilih.
6. WHEN panitia mengakses halaman detail siswa, THE System SHALL menampilkan seluruh data pendaftar termasuk data pribadi, pilihan jurusan, status verifikasi, dan daftar berkas yang diunggah.

---

### Requirement 4: Verifikasi Berkas Siswa

**User Story:** Sebagai panitia, saya ingin dapat memverifikasi berkas yang diunggah siswa, agar proses seleksi dapat berjalan dengan benar.

#### Acceptance Criteria

1. WHEN panitia mengubah status verifikasi siswa menjadi `verified` atau `rejected` dengan catatan opsional, THE System SHALL menyimpan perubahan status dan catatan verifikasi tersebut.
2. WHEN panitia menyimpan status verifikasi, THE System SHALL menampilkan konfirmasi keberhasilan dan memperbarui tampilan status pada halaman detail siswa.
3. IF panitia mengirimkan status verifikasi dengan nilai selain `verified` atau `rejected`, THEN THE System SHALL menolak permintaan dan menampilkan pesan validasi.
4. THE System SHALL menampilkan berkas-berkas yang diunggah siswa beserta status verifikasi masing-masing berkas pada halaman detail siswa.

---

### Requirement 5: Edit dan Hapus Data Siswa

**User Story:** Sebagai panitia, saya ingin dapat mengedit dan menghapus data siswa, agar data pendaftar dapat dijaga keakuratannya.

#### Acceptance Criteria

1. WHEN panitia mengedit data siswa dengan data yang valid, THE System SHALL menyimpan perubahan dan menampilkan konfirmasi keberhasilan.
2. IF panitia mengirimkan data edit siswa yang tidak valid (misalnya NIK duplikat atau format tidak sesuai), THEN THE System SHALL menolak permintaan dan menampilkan pesan validasi yang spesifik.
3. WHEN panitia menghapus data siswa, THE System SHALL menghapus data siswa secara permanen dan mengarahkan kembali ke halaman daftar siswa dengan konfirmasi keberhasilan.
4. THE System SHALL memastikan bahwa operasi edit dan hapus data siswa oleh panitia menggunakan aturan validasi yang sama dengan operasi yang dilakukan oleh admin.

---

### Requirement 6: Kirim Pesan Inbox ke Siswa

**User Story:** Sebagai panitia, saya ingin dapat mengirim pesan inbox ke siswa, agar saya dapat berkomunikasi terkait proses pendaftaran.

#### Acceptance Criteria

1. WHEN panitia mengirim pesan ke satu siswa tertentu dengan subjek dan isi pesan yang valid, THE System SHALL menyimpan pesan dan menampilkan konfirmasi keberhasilan beserta jumlah penerima.
2. WHEN panitia mengirim pesan ke semua siswa atau ke kelompok siswa berdasarkan filter, THE System SHALL mengirim pesan ke seluruh siswa yang memenuhi kriteria dan menampilkan konfirmasi jumlah penerima.
3. IF panitia mengirim pesan tanpa mengisi subjek atau isi pesan, THEN THE System SHALL menolak permintaan dan menampilkan pesan validasi.
4. THE System SHALL mencatat `sender_id` pada setiap pesan yang dikirim panitia, sehingga pengirim pesan dapat diidentifikasi.
5. WHEN panitia mengakses halaman inbox, THE System SHALL menampilkan daftar pesan yang telah dikirim beserta informasi penerima, subjek, dan tanggal pengiriman.
6. WHEN panitia mengakses detail pesan, THE System SHALL menampilkan isi lengkap pesan beserta informasi siswa penerima.
7. WHEN panitia menghapus pesan dari inbox, THE System SHALL menghapus pesan tersebut dan menampilkan konfirmasi keberhasilan.

---

### Requirement 7: Akses Laporan (Read-Only, Tanpa Ekspor)

**User Story:** Sebagai panitia, saya ingin dapat melihat laporan statistik pendaftaran, agar saya dapat memantau perkembangan penerimaan siswa secara keseluruhan.

#### Acceptance Criteria

1. WHEN panitia mengakses halaman laporan, THE System SHALL menampilkan statistik pendaftaran yang mencakup total pendaftar, distribusi gender, status verifikasi, dan statistik per jurusan.
2. WHEN panitia menggunakan filter pada halaman laporan (berdasarkan jurusan, status verifikasi, atau status penerimaan), THE System SHALL memperbarui tampilan statistik sesuai filter yang dipilih.
3. THE System SHALL menyembunyikan atau menonaktifkan tombol ekspor PDF dan CSV pada halaman laporan ketika diakses oleh pengguna dengan role `panitia`.
4. IF pengguna dengan role `panitia` mengakses endpoint ekspor laporan (`/admin/reports/pdf` atau `/admin/reports/csv`), THEN THE System SHALL menolak akses dengan respons HTTP 403.

---

### Requirement 8: Pembatasan Akses ke Fitur Eksklusif Admin

**User Story:** Sebagai admin, saya ingin memastikan bahwa panitia tidak dapat mengakses fitur konfigurasi sistem dan manajemen akun, agar integritas sistem tetap terjaga.

#### Acceptance Criteria

1. IF pengguna dengan role `panitia` mengakses route manajemen tahun ajaran (`/admin/academic-years`), THEN THE System SHALL menolak akses dengan respons HTTP 403.
2. IF pengguna dengan role `panitia` mengakses route manajemen jurusan (`/admin/majors`), THEN THE System SHALL menolak akses dengan respons HTTP 403.
3. IF pengguna dengan role `panitia` mengakses route manajemen jadwal (`/admin/schedules`), THEN THE System SHALL menolak akses dengan respons HTTP 403.
4. IF pengguna dengan role `panitia` mengakses route manajemen template dokumen (`/admin/documents`), THEN THE System SHALL menolak akses dengan respons HTTP 403.
5. IF pengguna dengan role `panitia` mengakses route konfigurasi berkas pendaftaran (`/admin/registration-documents`), THEN THE System SHALL menolak akses dengan respons HTTP 403.
6. IF pengguna dengan role `panitia` mengakses route alokasi jurusan siswa (`/admin/students/{student}/allocate`), THEN THE System SHALL menolak akses dengan respons HTTP 403.
7. THE System SHALL menyembunyikan menu navigasi untuk fitur-fitur eksklusif admin pada antarmuka panitia, sehingga panitia tidak melihat tautan ke fitur yang tidak dapat diakses.

---

### Requirement 9: Navigasi dan Antarmuka Panitia

**User Story:** Sebagai panitia, saya ingin antarmuka yang sesuai dengan hak akses saya, agar saya tidak kebingungan dengan fitur yang tidak relevan.

#### Acceptance Criteria

1. THE System SHALL menampilkan menu navigasi yang hanya memuat item yang dapat diakses oleh panitia: Dashboard, Data Siswa, Inbox, dan Laporan.
2. THE System SHALL menyampaikan informasi role pengguna yang sedang login (`admin` atau `panitia`) ke frontend melalui data Inertia shared props, sehingga komponen Vue dapat menyesuaikan tampilan secara kondisional.
3. WHEN panitia berhasil login, THE System SHALL mengarahkan panitia ke halaman dashboard yang sama dengan admin (`/dashboard`).
4. THE System SHALL menampilkan identitas pengguna yang sedang login (nama dan role) pada elemen navigasi.
