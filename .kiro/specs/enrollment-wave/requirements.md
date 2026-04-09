# Dokumen Persyaratan: Gelombang Pendaftaran (Enrollment Wave)

## Pendahuluan

Fitur Gelombang Pendaftaran memungkinkan sistem SPMB SMKN 8 TIK Jayapura membagi periode
pendaftaran dalam satu tahun ajaran menjadi beberapa gelombang (Gelombang I, II, III, dst.).
Setiap gelombang memiliki siklus hidup sendiri (draft → open → closed → announced) dan
menggunakan sisa kuota dari gelombang sebelumnya. Siswa yang tidak lolos pada suatu gelombang
tidak dapat mendaftar ulang di gelombang berikutnya dalam tahun ajaran yang sama.

## Glosarium

- **Sistem**: Aplikasi SPMB SMKN 8 TIK Jayapura berbasis Laravel 11 + Inertia + Vue 3.
- **Admin**: Pengguna dengan role `admin` yang mengelola seluruh konfigurasi sistem.
- **Panitia**: Pengguna dengan role `panitia` yang membantu proses verifikasi dan seleksi.
- **Siswa**: Calon peserta didik yang melakukan pendaftaran melalui portal publik.
- **Tahun_Ajaran**: Entitas `AcademicYear` dengan status `draft`, `active`, atau `closed`.
- **Gelombang**: Periode pendaftaran dalam satu Tahun_Ajaran, direpresentasikan oleh model `EnrollmentWave`.
- **Status_Gelombang**: Nilai enum `draft`, `open`, `closed`, atau `announced` pada Gelombang.
- **Kuota_Total**: Jumlah kursi per jurusan yang ditetapkan di level Tahun_Ajaran (tabel `academic_year_major`).
- **Kuota_Tersisa**: Kuota_Total dikurangi jumlah siswa yang sudah diterima (`is_accepted = true`) pada gelombang-gelombang sebelumnya dalam Tahun_Ajaran yang sama.
- **Kuota_Gelombang**: Kuota yang berlaku untuk suatu Gelombang per jurusan. Nilai awalnya dihitung otomatis sebagai Kuota_Tersisa, namun Admin dapat mengubahnya (override) sebelum Gelombang dibuka. Validasi alokasi menggunakan Kuota_Gelombang, bukan Kuota_Total.
- **Nomor_Pendaftaran**: Identifikasi unik siswa dengan format `SPMB-{tahun}-{romawi}-{urutan}`, contoh: `SPMB-2026-I-0001`.
- **NIK**: Nomor Induk Kependudukan 16 digit milik Siswa, digunakan sebagai identitas unik lintas gelombang.
- **Angka_Romawi**: Representasi urutan gelombang dalam huruf Romawi (I, II, III, IV, V, dst.).

---

## Persyaratan

### Persyaratan 1: Manajemen Gelombang oleh Admin

**User Story:** Sebagai Admin, saya ingin membuat dan mengelola gelombang pendaftaran dalam
satu tahun ajaran, agar proses penerimaan siswa dapat dibagi menjadi beberapa periode.

#### Kriteria Penerimaan

1. THE Sistem SHALL menyimpan setiap Gelombang dengan atribut: `academic_year_id`, `name`
   (contoh: "Gelombang I"), `wave_number` (integer urutan), `status` (enum: `draft`, `open`,
   `closed`, `announced`), `open_date`, `close_date`, dan `description`.
2. WHEN Admin membuat Gelombang baru dalam suatu Tahun_Ajaran, THE Sistem SHALL menetapkan
   status awal Gelombang sebagai `draft`.
3. THE Sistem SHALL mengizinkan lebih dari satu Gelombang dalam satu Tahun_Ajaran.
4. WHEN Admin mengubah status Gelombang menjadi `open`, THE Sistem SHALL memvalidasi bahwa
   tidak ada Gelombang lain berstatus `open` dalam Tahun_Ajaran yang sama.
5. IF terdapat Gelombang lain berstatus `open` dalam Tahun_Ajaran yang sama saat Admin mencoba
   membuka Gelombang baru, THEN THE Sistem SHALL menolak permintaan dan mengembalikan pesan
   kesalahan yang menjelaskan Gelombang mana yang sedang open.
5a. WHEN Admin membuka Gelombang baru dan Gelombang sebelumnya berstatus `closed` (belum
    `announced`), THE Sistem SHALL mengizinkan pembukaan Gelombang baru tersebut. Gelombang
    dapat berstatus `open` meskipun gelombang sebelumnya belum diumumkan.
6. WHEN Admin mengubah status Gelombang dari `open` menjadi `closed`, THE Sistem SHALL
   mencatat waktu penutupan dan memperbarui status Gelombang menjadi `closed`.
7. WHEN Admin mengubah status Gelombang dari `closed` menjadi `announced`, THE Sistem SHALL
   memperbarui status Gelombang menjadi `announced`.
8. THE Sistem SHALL mengizinkan transisi status hanya dalam urutan: `draft` → `open` →
   `closed` → `announced`.
9. IF Admin mencoba melakukan transisi status di luar urutan yang diizinkan, THEN THE Sistem
   SHALL menolak permintaan dan mengembalikan pesan kesalahan yang menjelaskan transisi yang
   valid.
10. WHEN Admin menghapus Gelombang berstatus `draft`, THE Sistem SHALL menghapus data
    Gelombang tersebut dari basis data.
11. IF Admin mencoba menghapus Gelombang dengan status selain `draft`, THEN THE Sistem SHALL
    menolak permintaan dan mengembalikan pesan kesalahan.

---

### Persyaratan 2: Kuota Per Gelombang (Otomatis + Editable)

**User Story:** Sebagai Admin, saya ingin sistem menghitung kuota awal per gelombang secara
otomatis dari sisa kuota gelombang sebelumnya, namun saya tetap bisa mengubahnya jika
diperlukan, agar distribusi kuota fleksibel.

#### Kriteria Penerimaan

1. THE Sistem SHALL menyimpan Kuota_Total per jurusan di level Tahun_Ajaran pada tabel
   `academic_year_major`.
2. THE Sistem SHALL menyimpan Kuota_Gelombang per jurusan pada tabel pivot
   `enrollment_wave_major` dengan kolom `enrollment_wave_id`, `major_id`, dan `quota`.
3. WHEN Admin membuat Gelombang baru, THE Sistem SHALL mengisi nilai awal Kuota_Gelombang
   per jurusan secara otomatis dengan nilai: `Kuota_Total - jumlah siswa is_accepted = true
   pada semua gelombang sebelumnya dalam Tahun_Ajaran yang sama`. Jika ini adalah Gelombang
   pertama, nilai awal sama dengan Kuota_Total.
4. WHEN Admin mengedit Kuota_Gelombang sebelum Gelombang berstatus `open`, THE Sistem SHALL
   menyimpan nilai yang diubah Admin sebagai Kuota_Gelombang yang berlaku.
5. IF Admin mencoba mengedit Kuota_Gelombang pada Gelombang yang sudah berstatus `open`,
   `closed`, atau `announced`, THEN THE Sistem SHALL menolak perubahan dan mengembalikan
   pesan kesalahan.
6. WHEN Panitia mengalokasikan jurusan kepada Siswa dalam suatu Gelombang, THE Sistem SHALL
   memvalidasi bahwa jumlah siswa yang sudah diterima pada jurusan tersebut dalam Gelombang
   yang sama belum melebihi Kuota_Gelombang yang berlaku.
7. IF jumlah siswa yang diterima pada suatu jurusan dalam Gelombang sudah mencapai
   Kuota_Gelombang, THEN THE Sistem SHALL menolak alokasi dan mengembalikan pesan kesalahan
   yang menyebutkan nama jurusan dan nilai Kuota_Gelombang.
8. THE Sistem SHALL menampilkan Kuota_Gelombang, jumlah sudah diterima, dan sisa kuota
   per jurusan pada halaman detail Gelombang.

---

### Persyaratan 3: Pendaftaran Siswa ke Gelombang Aktif

**User Story:** Sebagai Siswa, saya ingin mendaftar dan secara otomatis masuk ke gelombang
yang sedang buka, agar saya tidak perlu memilih gelombang secara manual.

#### Kriteria Penerimaan

1. WHEN Siswa mengirimkan formulir pendaftaran, THE Sistem SHALL mencari Gelombang berstatus
   `open` dalam Tahun_Ajaran yang berstatus `active`.
2. IF tidak ada Gelombang berstatus `open` dalam Tahun_Ajaran yang berstatus `active`, THEN
   THE Sistem SHALL menolak pendaftaran dan mengembalikan pesan: "Pendaftaran belum dibuka.
   Tidak ada gelombang pendaftaran yang aktif saat ini."
3. WHEN Gelombang berstatus `open` ditemukan, THE Sistem SHALL menetapkan `enrollment_wave_id`
   pada data Siswa sesuai Gelombang tersebut.
4. THE Sistem SHALL menampilkan informasi Gelombang yang sedang open (nama gelombang, tanggal
   buka, tanggal tutup) pada halaman formulir pendaftaran publik.
5. WHILE tidak ada Gelombang berstatus `open`, THE Sistem SHALL menampilkan pesan
   "Pendaftaran Ditutup" pada halaman formulir pendaftaran publik.

---

### Persyaratan 4: Validasi Pendaftaran Ulang Lintas Gelombang

**User Story:** Sebagai Admin, saya ingin sistem mencegah siswa yang tidak lolos mendaftar
ulang di gelombang berikutnya dalam tahun ajaran yang sama, agar integritas seleksi terjaga.

#### Kriteria Penerimaan

1. WHEN Siswa mengirimkan formulir pendaftaran, THE Sistem SHALL memeriksa apakah NIK Siswa
   sudah terdaftar dalam Tahun_Ajaran yang berstatus `active`, tanpa memandang gelombang mana
   Siswa tersebut sebelumnya mendaftar.
2. IF NIK Siswa sudah terdaftar dalam Tahun_Ajaran yang berstatus `active`, THEN THE Sistem
   SHALL menolak pendaftaran dan mengembalikan pesan: "NIK ini sudah terdaftar pada tahun
   ajaran yang sedang berjalan. Pendaftaran ulang tidak diizinkan."
3. THE Sistem SHALL melakukan pemeriksaan NIK berdasarkan `academic_year_id` pada tabel
   `students`, bukan berdasarkan `enrollment_wave_id`.
4. WHERE fitur pengumuman hasil seleksi diaktifkan, THE Sistem SHALL menampilkan status
   kelulusan Siswa berdasarkan `enrollment_wave_id` dan `is_accepted` pada halaman pengumuman.

---

### Persyaratan 5: Format Nomor Pendaftaran dengan Kode Gelombang

**User Story:** Sebagai Admin, saya ingin nomor pendaftaran mencantumkan kode gelombang dalam
format Romawi, agar setiap nomor pendaftaran dapat diidentifikasi berasal dari gelombang mana.

#### Kriteria Penerimaan

1. WHEN Siswa berhasil mendaftar, THE Sistem SHALL menghasilkan Nomor_Pendaftaran dengan
   format: `SPMB-{end_year}-{Angka_Romawi}-{urutan 4 digit}`, contoh: `SPMB-2026-I-0001`.
2. THE Sistem SHALL menggunakan `end_year` dari Tahun_Ajaran sebagai komponen tahun pada
   Nomor_Pendaftaran.
3. THE Sistem SHALL menggunakan `wave_number` dari Gelombang untuk menghasilkan Angka_Romawi
   pada Nomor_Pendaftaran.
4. THE Sistem SHALL menghitung urutan 4 digit secara terpisah per Gelombang, sehingga setiap
   Gelombang dimulai dari `0001`.
5. THE Sistem SHALL mengonversi `wave_number` integer ke Angka_Romawi menggunakan pemetaan
   standar (1=I, 2=II, 3=III, 4=IV, 5=V, 6=VI, 7=VII, 8=VIII, 9=IX, 10=X).
6. THE Sistem SHALL menjamin keunikan Nomor_Pendaftaran di seluruh tabel `students`.
7. FOR ALL nilai `wave_number` integer yang valid (1–10), THE Sistem SHALL menghasilkan
   Angka_Romawi yang benar dan Nomor_Pendaftaran yang dapat di-parse kembali untuk mengekstrak
   tahun, kode gelombang, dan urutan (properti round-trip).

---

### Persyaratan 6: Pengumuman Hasil Seleksi per Gelombang

**User Story:** Sebagai Siswa, saya ingin melihat hasil seleksi berdasarkan gelombang tempat
saya mendaftar, agar saya mengetahui status kelulusan saya dengan jelas.

#### Kriteria Penerimaan

1. WHEN Admin mengubah status Gelombang menjadi `announced`, THE Sistem SHALL mengizinkan
   hasil seleksi Gelombang tersebut ditampilkan pada halaman pengumuman publik.
2. WHILE status Gelombang bukan `announced`, THE Sistem SHALL menyembunyikan hasil seleksi
   Gelombang tersebut dari halaman pengumuman publik.
3. WHEN Siswa memasukkan Nomor_Pendaftaran pada halaman pengumuman, THE Sistem SHALL
   menampilkan: nama lengkap, Nomor_Pendaftaran, nama Gelombang, status kelulusan, dan nama
   jurusan yang diterima (jika lolos).
4. IF Nomor_Pendaftaran yang dimasukkan tidak ditemukan dalam basis data, THEN THE Sistem
   SHALL mengembalikan pesan: "Nomor pendaftaran tidak ditemukan."
5. IF status Gelombang dari Nomor_Pendaftaran yang dicari bukan `announced`, THEN THE Sistem
   SHALL mengembalikan pesan: "Hasil seleksi untuk gelombang ini belum diumumkan."

---

### Persyaratan 7: Tampilan Daftar Gelombang untuk Admin

**User Story:** Sebagai Admin, saya ingin melihat daftar semua gelombang dalam suatu tahun
ajaran beserta statistiknya, agar saya dapat memantau progres setiap gelombang.

#### Kriteria Penerimaan

1. WHEN Admin mengakses halaman manajemen gelombang, THE Sistem SHALL menampilkan daftar
   Gelombang dalam Tahun_Ajaran yang sedang aktif sebagai konteks, diurutkan berdasarkan
   `wave_number` secara ascending.
2. THE Sistem SHALL menampilkan informasi berikut untuk setiap Gelombang: nama gelombang,
   status, tanggal buka, tanggal tutup, jumlah pendaftar, dan jumlah diterima.
3. THE Sistem SHALL menampilkan ringkasan Kuota_Tersisa per jurusan untuk Gelombang yang
   berstatus `open` atau `closed`.
4. WHEN Admin beralih konteks Tahun_Ajaran, THE Sistem SHALL memperbarui daftar Gelombang
   sesuai Tahun_Ajaran yang dipilih.
