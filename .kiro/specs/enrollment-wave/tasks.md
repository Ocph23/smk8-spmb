# Rencana Implementasi: Gelombang Pendaftaran (Enrollment Wave)

## Ikhtisar

Implementasi fitur Gelombang Pendaftaran secara bertahap: mulai dari lapisan database, helper,
model, service, controller backend, middleware, pembaruan komponen yang ada, halaman Vue baru,
pembaruan halaman Vue yang ada, hingga pengujian property-based dan unit/feature tests.

## Tasks

- [x] 1. Migrasi database
  - Buat file migrasi `create_enrollment_waves_table` dengan kolom: `id`, `academic_year_id` (FK → academic_years, CASCADE), `name` (varchar 100), `wave_number` (tinyint unsigned), `status` (enum: draft, open, closed, announced, default draft), `open_date` (date nullable), `close_date` (date nullable), `description` (text nullable), `created_at`, `updated_at`; tambahkan unique key `uq_wave_per_year (academic_year_id, wave_number)` dan index pada `status`
  - Buat file migrasi `create_enrollment_wave_major_table` dengan kolom: `id`, `enrollment_wave_id` (FK → enrollment_waves, CASCADE), `major_id` (FK → majors, CASCADE), `quota` (smallint unsigned, default 0), `created_at`, `updated_at`; tambahkan unique key `uq_wave_major (enrollment_wave_id, major_id)`
  - Buat file migrasi `add_enrollment_wave_id_to_students_table` untuk menambah kolom `enrollment_wave_id` (bigint unsigned nullable) setelah `academic_year_id` dengan FK → enrollment_waves ON DELETE SET NULL
  - _Persyaratan: 1.1, 2.2, 3.3_

- [x] 2. Helper RomanNumeralHelper
  - [x] 2.1 Buat file `app/Helpers/RomanNumeralHelper.php` dengan class `RomanNumeralHelper` dan method static `toRoman(int $number): string`
    - Implementasikan konversi integer ke angka Romawi menggunakan tabel pemetaan standar (1–3999)
    - Lempar `InvalidArgumentException` jika input di luar range 1–3999
    - _Persyaratan: 5.3, 5.5, 5.7_
  - [ ]* 2.2 Tulis unit test untuk RomanNumeralHelper (`tests/Unit/RomanNumeralHelperTest.php`)
    - Uji konversi 1–10 (I–X) dan beberapa nilai lain (4=IV, 9=IX, 40=XL)
    - Uji exception untuk input 0 dan nilai negatif
    - _Persyaratan: 5.5_

- [-] 3. Model EnrollmentWave dan pembaruan relasi model yang ada
  - [x] 3.1 Buat `app/Models/EnrollmentWave.php` dengan `$fillable`, `$casts`, relasi `academicYear()` (BelongsTo), `majors()` (BelongsToMany via `enrollment_wave_major`, pivot: `quota`), `students()` (HasMany), dan method helper: `isDraft()`, `isOpen()`, `isClosed()`, `isAnnounced()`, `canTransitionTo(string $status): bool`
    - `canTransitionTo` hanya mengizinkan: draft→open, open→closed, closed→announced
    - _Persyaratan: 1.1, 1.8_
  - [x] 3.2 Perbarui `app/Models/Student.php`: tambah `enrollment_wave_id` ke `$fillable` dan relasi `enrollmentWave()` (BelongsTo EnrollmentWave)
    - _Persyaratan: 3.3_
  - [x] 3.3 Perbarui `app/Models/AcademicYear.php`: tambah relasi `enrollmentWaves()` (HasMany EnrollmentWave) dengan default order `wave_number ASC`
    - _Persyaratan: 7.1_
  - [x] 3.4 Perbarui `app/Models/Major.php`: tambah relasi `enrollmentWaves()` (BelongsToMany EnrollmentWave via `enrollment_wave_major`, pivot: `quota`)
    - _Persyaratan: 2.2_

- [x] 4. EnrollmentWaveService
  - [x] 4.1 Buat `app/Services/EnrollmentWaveService.php` dengan method:
    - `getOpenWaveForActiveYear(): ?EnrollmentWave` — cari gelombang status `open` dalam tahun ajaran `active`
    - `createWave(AcademicYear $year, array $data): EnrollmentWave` — buat gelombang baru dengan status `draft`, hitung `wave_number` otomatis (max + 1), isi kuota awal via `calculateInitialQuotas`
    - `calculateInitialQuotas(AcademicYear $year): array` — hitung `Kuota_Total - jumlah is_accepted=true pada gelombang sebelumnya` per jurusan
    - `openWave(EnrollmentWave $wave): void` — validasi tidak ada gelombang lain yang open, lakukan transisi ke `open`
    - `closeWave(EnrollmentWave $wave): void` — transisi ke `closed`
    - `announceWave(EnrollmentWave $wave): void` — transisi ke `announced`
    - `deleteWave(EnrollmentWave $wave): void` — validasi status draft, hapus
    - `updateQuotas(EnrollmentWave $wave, array $quotas): void` — validasi status draft, simpan kuota baru ke pivot
    - `getWaveQuota(EnrollmentWave $wave, int $majorId): int` — ambil kuota dari pivot `enrollment_wave_major`
    - `getAcceptedCountInWave(EnrollmentWave $wave, int $majorId): int` — hitung siswa `is_accepted=true` pada gelombang tersebut
    - `generateRegistrationNumber(EnrollmentWave $wave): string` — format `SPMB-{end_year}-{Romawi}-{urutan 4 digit}`, urutan dihitung per gelombang
    - _Persyaratan: 1.2, 1.4, 1.5, 1.6, 1.7, 1.8, 1.9, 1.10, 1.11, 2.3, 2.4, 2.5, 3.1, 3.2, 5.1, 5.2, 5.3, 5.4_

- [x] 5. EnrollmentWaveController dan routes
  - [x] 5.1 Buat `app/Http/Controllers/EnrollmentWaveController.php` dengan method:
    - `index()` — ambil gelombang berdasarkan konteks tahun ajaran, urutkan `wave_number ASC`, render `Admin/EnrollmentWaves/Index`
    - `store()` — validasi input (name, open_date, close_date, description), panggil `EnrollmentWaveService::createWave()`
    - `show(EnrollmentWave $wave)` — load relasi `majors`, `students`, hitung statistik kuota per jurusan, render `Admin/EnrollmentWaves/Show`
    - `update(Request $request, EnrollmentWave $wave)` — validasi status draft, update name/description/tanggal
    - `destroy(EnrollmentWave $wave)` — panggil `EnrollmentWaveService::deleteWave()`
    - `open(EnrollmentWave $wave)` — panggil `EnrollmentWaveService::openWave()`
    - `close(EnrollmentWave $wave)` — panggil `EnrollmentWaveService::closeWave()`
    - `announce(EnrollmentWave $wave)` — panggil `EnrollmentWaveService::announceWave()`
    - `updateQuotas(Request $request, EnrollmentWave $wave)` — validasi input kuota, panggil `EnrollmentWaveService::updateQuotas()`
    - _Persyaratan: 1.1–1.11, 2.3–2.8, 7.1–7.4_
  - [x] 5.2 Tambah routes di `routes/web.php` dalam middleware `admin`: prefix `/admin/enrollment-waves` dengan semua route sesuai desain (index, store, show, update, destroy, open, close, announce, update-quotas)
    - _Persyaratan: 1.1, 7.1_

- [x] 6. Perbarui HandleInertiaRequests
  - Tambah key `enrollmentWave.open` pada method `share()` di `app/Http/Middleware/HandleInertiaRequests.php` yang memanggil `EnrollmentWaveService::getOpenWaveForActiveYear()` (lazy evaluation dengan closure)
  - Inject `EnrollmentWaveService` ke constructor middleware
  - _Persyaratan: 3.4, 3.5_

- [x] 7. Perbarui StudentController
  - [x] 7.1 Perbarui method `store()` di `app/Http/Controllers/StudentController.php`:
    - Ganti `$this->academicYearService->getActive()` dengan `$this->enrollmentWaveService->getOpenWaveForActiveYear()`
    - Jika tidak ada gelombang open, kembalikan error 422 dengan pesan: "Pendaftaran belum dibuka. Tidak ada gelombang pendaftaran yang aktif saat ini."
    - Simpan `enrollment_wave_id` pada data siswa
    - Delegasikan pembuatan nomor pendaftaran ke `EnrollmentWaveService::generateRegistrationNumber($wave)`
    - Inject `EnrollmentWaveService` ke constructor `StudentController`
        - _Persyaratan: 3.1, 3.2, 3.3, 4.1, 4.2, 5.1, 5.2, 5.3, 5.4_
    - [x] 7.2 Perbarui method `create()` di `StudentController`: teruskan data gelombang open ke view (`enrollmentWave` prop) agar halaman Register dapat menampilkan info gelombang
        - _Persyaratan: 3.4, 3.5_

- [x] 8. Perbarui AdminController
  - Perbarui method `allocateMajor()` di `app/Http/Controllers/AdminController.php`:
    - Ganti pengecekan kuota dari level tahun ajaran ke level gelombang menggunakan `EnrollmentWaveService::getWaveQuota($wave, $majorId)` dan `getAcceptedCountInWave($wave, $majorId)`
    - Load relasi `enrollmentWave` pada student
    - Jika student tidak memiliki `enrollment_wave_id`, fallback ke perilaku lama (kuota tahun ajaran)
    - Inject `EnrollmentWaveService` ke constructor `AdminController`
    - _Persyaratan: 2.6, 2.7_

- [x] 9. Perbarui AnnouncementController
  - Perbarui method `check()` di `app/Http/Controllers/AnnouncementController.php`:
    - Setelah menemukan student, load relasi `enrollmentWave`
    - Jika `enrollmentWave` ada dan statusnya bukan `announced`, kembalikan error: "Hasil seleksi untuk gelombang ini belum diumumkan."
    - Sertakan data `enrollmentWave` pada response ke view `Announcement/Result`
    - _Persyaratan: 6.1, 6.2, 6.4, 6.5_

- [x] 10. Checkpoint backend — pastikan semua test backend lulus
  - Jalankan `php artisan migrate` untuk memastikan migrasi berjalan tanpa error
  - Pastikan semua tests lulus, tanyakan kepada pengguna jika ada pertanyaan

- [x] 11. Halaman Vue baru: Admin/EnrollmentWaves/Index dan Show
  - [x] 11.1 Buat `resources/js/Pages/Admin/EnrollmentWaves/Index.vue`:
    - Tampilkan daftar gelombang dalam tahun ajaran konteks, diurutkan `wave_number ASC`
    - Badge status dengan warna berbeda: draft (abu), open (hijau), closed (kuning), announced (biru)
    - Statistik per gelombang: jumlah pendaftar, jumlah diterima
    - Tombol buat gelombang baru (modal inline atau form)
    - Tombol transisi status (Buka/Tutup/Umumkan) sesuai status saat ini
    - Tombol hapus (hanya untuk status draft)
    - _Persyaratan: 7.1, 7.2, 7.3, 7.4_
  - [x] 11.2 Buat `resources/js/Pages/Admin/EnrollmentWaves/Show.vue`:
    - Detail gelombang: nama, status, tanggal buka/tutup, deskripsi
    - Tabel kuota per jurusan: Kuota_Gelombang, sudah diterima, sisa kuota
    - Form edit kuota (hanya tampil jika status `draft`)
    - Tombol transisi status (Buka/Tutup/Umumkan)
    - _Persyaratan: 2.8, 7.2, 7.3_

- [x] 12. Perbarui halaman Vue yang ada
  - [x] 12.1 Perbarui `resources/js/Pages/Student/Register.vue`:
    - Tambah prop `enrollmentWave` (nullable)
    - Tampilkan banner info gelombang aktif (nama, tanggal buka, tanggal tutup) jika `enrollmentWave` ada
    - Ganti pesan "Pendaftaran Ditutup" dengan pesan spesifik: "Tidak ada gelombang pendaftaran yang aktif saat ini" jika tidak ada gelombang open
    - _Persyaratan: 3.4, 3.5_
  - [x] 12.2 Perbarui `resources/js/Pages/Announcement/Result.vue`:
    - Tambah tampilan nama gelombang pada info siswa
    - Tangani kasus gelombang belum announced (tampilkan pesan yang sesuai dari server)
    - _Persyaratan: 6.3, 6.5_
  - [x] 12.3 Perbarui `resources/js/Pages/Admin/Students/Index.vue`:
    - Tambah filter berdasarkan gelombang (dropdown pilih gelombang dari tahun ajaran konteks)
    - Tambah kolom "Gelombang" pada tabel siswa
    - Teruskan prop `waves` dari `AdminController::students()`
    - _Persyaratan: 7.4_
  - [x] 12.4 Perbarui `resources/js/Pages/Admin/Students/Show.vue`:
    - Tampilkan info gelombang siswa (nama gelombang, status gelombang)
    - Perbarui tampilan kuota pada form alokasi jurusan: tampilkan Kuota_Gelombang dan sisa kuota gelombang (bukan kuota tahun ajaran)
    - Teruskan prop `waveQuotas` dari `AdminController::showStudent()`
    - _Persyaratan: 2.8_

- [x] 13. Tambah menu Gelombang di AdminLayout
  - Tambah item menu "Gelombang" ke array `menuItems` di `resources/js/Layouts/AdminLayout.vue` dengan `href: 'admin.enrollment-waves.index'`, `adminOnly: true`, dan icon yang sesuai (contoh: icon waves/layers)
  - _Persyaratan: 7.1_

- [ ] 14. Property-based tests dengan Eris
  - [ ]* 14.1 Buat `tests/Feature/EnrollmentWave/EnrollmentWavePropertyTest.php` — Property 1: Gelombang baru selalu berstatus draft
    - **Property 1: Gelombang baru selalu berstatus draft**
    - **Validates: Persyaratan 1.2**
    - For any: nama (string), wave_number (int 1–10) → assert `$wave->status === 'draft'`
  - [ ]* 14.2 Tulis property test — Property 2: Maksimal satu gelombang open per tahun ajaran
    - **Property 2: Maksimal satu gelombang open per tahun ajaran**
    - **Validates: Persyaratan 1.4**
    - For any: N gelombang (N ∈ [2..5]) → assert `count(waves where status=open) <= 1`
  - [ ]* 14.3 Tulis property test — Property 3: Transisi status hanya mengikuti urutan valid
    - **Property 3: Transisi status hanya mengikuti urutan valid**
    - **Validates: Persyaratan 1.8, 1.9**
    - For any: pasangan (from_status, to_status) → assert berhasil iff (from, to) ∈ {(draft,open),(open,closed),(closed,announced)}
  - [ ]* 14.4 Tulis property test — Property 4: Penghapusan hanya untuk status draft
    - **Property 4: Penghapusan hanya diizinkan untuk status draft**
    - **Validates: Persyaratan 1.10, 1.11**
    - For any: gelombang dengan status ∈ {open, closed, announced} → assert operasi hapus selalu ditolak
  - [ ]* 14.5 Tulis property test — Property 5: Kuota awal = Kuota_Total − siswa diterima
    - **Property 5: Kuota awal gelombang = Kuota_Total − siswa diterima sebelumnya**
    - **Validates: Persyaratan 2.3**
    - For any: kuota_total Q ∈ [1..50], accepted_count A ∈ [0..Q] → assert `initial_quota === Q - A`
  - [ ]* 14.6 Tulis property test — Property 6: Alokasi ditolak saat kuota penuh
    - **Property 6: Alokasi tidak boleh melebihi Kuota_Gelombang**
    - **Validates: Persyaratan 2.6, 2.7**
    - For any: gelombang dengan kuota K ∈ [1..10] → assert alokasi ke-(K+1) selalu ditolak
  - [ ]* 14.7 Tulis property test — Property 7: Siswa masuk ke gelombang open
    - **Property 7: Siswa selalu masuk ke gelombang yang sedang open**
    - **Validates: Persyaratan 3.1, 3.3**
    - For any: tahun ajaran active dengan tepat satu gelombang open → assert `student.enrollment_wave_id === open_wave.id`
  - [ ]* 14.8 Tulis property test — Property 8: Pendaftaran ditolak tanpa gelombang open
    - **Property 8: Pendaftaran ditolak jika tidak ada gelombang open**
    - **Validates: Persyaratan 3.2**
    - For any: tahun ajaran active tanpa gelombang open → assert response 422 dengan pesan sesuai
  - [ ]* 14.9 Tulis property test — Property 9: NIK unik per tahun ajaran
    - **Property 9: NIK unik per tahun ajaran (lintas gelombang)**
    - **Validates: Persyaratan 4.1, 4.2**
    - For any: NIK valid (16 digit), tahun ajaran active → assert pendaftaran kedua dengan NIK sama selalu ditolak
  - [ ]* 14.10 Buat `tests/Unit/RomanNumeralPropertyTest.php` — Property 10: Format nomor pendaftaran round-trip
    - **Property 10: Format nomor pendaftaran selalu valid dan dapat di-parse kembali**
    - **Validates: Persyaratan 5.1, 5.3, 5.7**
    - For any: end_year Y ∈ [2020..2030], wave_number W ∈ [1..10], seq ∈ [1..9999] → assert `parse(generate(Y, W, seq)) === (Y, W, seq)`
  - [ ]* 14.11 Tulis property test — Property 11: Urutan nomor pendaftaran dimulai dari 0001 per gelombang
    - **Property 11: Urutan nomor pendaftaran dimulai dari 0001 per gelombang**
    - **Validates: Persyaratan 5.4**
    - For any: gelombang baru → assert siswa pertama mendapat urutan `0001`
  - [ ]* 14.12 Buat `tests/Feature/EnrollmentWave/AnnouncementPropertyTest.php` — Property 12: Hasil tersembunyi untuk gelombang non-announced
    - **Property 12: Hasil seleksi hanya terlihat untuk gelombang announced**
    - **Validates: Persyaratan 6.2, 6.5**
    - For any: siswa pada gelombang dengan status ∈ {draft, open, closed} → assert query pengumuman mengembalikan pesan "belum diumumkan"
  - [ ]* 14.13 Tulis property test — Property 13: Daftar gelombang selalu terurut ascending
    - **Property 13: Daftar gelombang selalu terurut ascending berdasarkan wave_number**
    - **Validates: Persyaratan 7.1**
    - For any: N gelombang dengan wave_number acak ∈ [1..10] → assert hasil query selalu terurut `wave_number ASC`

- [ ] 15. Feature/unit tests dengan PHPUnit
  - [ ]* 15.1 Buat `tests/Feature/EnrollmentWave/EnrollmentWaveServiceTest.php`:
    - Uji pembuatan gelombang dengan status draft
    - Uji kalkulasi kuota awal (gelombang pertama = kuota total, gelombang berikutnya = sisa)
    - Uji transisi status valid (draft→open, open→closed, closed→announced)
    - Uji penolakan transisi status tidak valid
    - Uji penolakan hapus gelombang non-draft
    - Uji penolakan edit kuota gelombang non-draft
    - _Persyaratan: 1.2, 1.8, 1.9, 1.10, 1.11, 2.3, 2.5_
  - [ ]* 15.2 Buat `tests/Feature/EnrollmentWave/StudentRegistrationTest.php`:
    - Uji pendaftaran berhasil → `enrollment_wave_id` terisi, nomor pendaftaran format `SPMB-{year}-{Romawi}-{seq}`
    - Uji pendaftaran ditolak saat tidak ada gelombang open (pesan sesuai)
    - Uji pendaftaran ditolak saat NIK duplikat dalam tahun ajaran yang sama
    - _Persyaratan: 3.1, 3.2, 3.3, 4.1, 4.2, 5.1_
  - [ ]* 15.3 Buat `tests/Feature/EnrollmentWave/AdminAllocationTest.php`:
    - Uji alokasi berhasil saat kuota gelombang tersedia
    - Uji alokasi ditolak saat kuota gelombang penuh (pesan menyebut nama jurusan dan nilai kuota)
    - _Persyaratan: 2.6, 2.7_
  - [ ]* 15.4 Buat `tests/Feature/EnrollmentWave/AnnouncementTest.php`:
    - Uji pengumuman berhasil untuk gelombang berstatus `announced`
    - Uji pengumuman ditolak untuk gelombang berstatus `draft`, `open`, dan `closed`
    - Uji pesan error saat nomor pendaftaran tidak ditemukan
    - _Persyaratan: 6.1, 6.2, 6.4, 6.5_

- [x] 16. Checkpoint akhir — pastikan semua tests lulus
  - Pastikan semua tests lulus, tanyakan kepada pengguna jika ada pertanyaan

## Catatan

- Task bertanda `*` bersifat opsional dan dapat dilewati untuk MVP yang lebih cepat
- Setiap task mereferensikan persyaratan spesifik untuk keterlacakan
- Checkpoint memastikan validasi inkremental di setiap tahap
- Property tests memvalidasi properti kebenaran universal (13 properties)
- Unit/feature tests memvalidasi contoh spesifik dan kondisi batas
- Konfigurasi Eris: gunakan `$this->limitTo(100)` di `setUp()` untuk minimum 100 iterasi per property
