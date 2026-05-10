# Rencana Implementasi: Academic Year Management

## Ikhtisar

Implementasi fitur multi-tahun ajaran pada aplikasi SPMB SMKN 8 TIK Jayapura menggunakan Laravel 11, Inertia.js, Vue 3, dan Tailwind CSS. Setiap langkah dibangun secara inkremental di atas langkah sebelumnya, dimulai dari lapisan database hingga antarmuka pengguna.

## Tasks

- [x] 1. Buat migrasi database untuk skema tahun ajaran
  - Buat file migrasi `create_academic_years_table` dengan kolom: `id`, `name`, `start_year`, `end_year`, `status` (enum: draft/active/closed), `description`, timestamps
  - Tambahkan index pada kolom `status`
  - Buat file migrasi `create_academic_year_major_table` (pivot) dengan kolom: `academic_year_id`, `major_id`, `quota`, `is_active`, timestamps, dan unique key `(academic_year_id, major_id)`
  - Buat file migrasi `add_academic_year_id_to_students_table`: tambah kolom `academic_year_id` nullable dengan foreign key ke `academic_years`, tambah index
  - Buat file migrasi `alter_students_nik_unique`: hapus unique constraint global `students_nik_unique`, tambah unique key `uq_nik_per_year (nik, academic_year_id)`
  - Buat file migrasi `add_academic_year_id_to_schedules_table`: tambah kolom `academic_year_id` nullable dengan foreign key ke `academic_years` (ON DELETE CASCADE), tambah index
  - _Requirements: 1.1, 2.1, 2.2, 2.3, 2.6_


- [x] 2. Buat model `AcademicYear` dan perbarui model yang ada
  - [x] 2.1 Buat `app/Models/AcademicYear.php` dengan `$fillable`, `$casts`, relasi `students()`, `schedules()`, `majors()` (BelongsToMany via pivot), helper `isActive()`, `isDraft()`, `isClosed()`, dan static method `getActive()`
    - _Requirements: 1.1, 2.1, 2.2, 2.3_
  - [x] 2.2 Perbarui `app/Models/Student.php`: tambah `academic_year_id` ke `$fillable` dan relasi `academicYear(): BelongsTo`
    - _Requirements: 2.1_
  - [x] 2.3 Perbarui `app/Models/Schedule.php`: tambah `academic_year_id` ke `$fillable` dan relasi `academicYear(): BelongsTo`
    - _Requirements: 2.2_
  - [x] 2.4 Perbarui `app/Models/Major.php`: tambah relasi `academicYears(): BelongsToMany` via pivot `academic_year_major`, dan helper `quotaForYear(int $academicYearId): int`
    - _Requirements: 2.3, 3.2_

- [x] 3. Buat `AcademicYearService`
  - Buat `app/Services/AcademicYearService.php` dengan method:
    - `getActive(): ?AcademicYear` — ambil tahun ajaran berstatus active
    - `getAll(): Collection` — ambil semua tahun ajaran diurutkan terbaru
    - `resolveContext(Request $request): ?AcademicYear` — baca `academic_year_context` dari session, fallback ke active
    - `create(array $data): AcademicYear` — buat tahun ajaran baru dengan status `draft`, salin konfigurasi jurusan dari tahun ajaran terakhir jika ada
    - `activate(AcademicYear $year): void` — ubah status ke `active`, ubah semua yang sebelumnya `active` menjadi `closed` dalam satu transaksi DB
    - `close(AcademicYear $year): void` — ubah status ke `closed`
    - `copyMajorConfig(AcademicYear $source, AcademicYear $target): void` — salin semua pivot `academic_year_major` dari source ke target
    - `getQuotaForMajor(AcademicYear $year, int $majorId): int` — ambil kuota dari pivot, fallback ke `majors.quota`
    - `getAcceptedCountForMajor(AcademicYear $year, int $majorId): int` — hitung siswa diterima pada jurusan di tahun ajaran tertentu
  - _Requirements: 1.2, 1.3, 1.4, 1.5, 3.1, 3.3_


- [x] 4. Buat `HistoricalDataMigrationSeeder`
  - Buat `database/seeders/HistoricalDataMigrationSeeder.php` yang idempoten:
    - Jika `AcademicYear::exists()` sudah true, langsung return tanpa melakukan apa pun
    - Tentukan tahun dari `registration_number` student pertama yang ada (format `SPMB-{tahun}-xxxx`), fallback ke tahun sekarang
    - Buat satu `AcademicYear` dengan status `closed` dan deskripsi "Tahun ajaran historis (migrasi otomatis)"
    - Salin kuota dari `majors.quota` ke pivot `academic_year_major` untuk semua jurusan
    - Update semua `students` dengan `academic_year_id = null` ke id tahun ajaran yang baru dibuat
    - Update semua `schedules` dengan `academic_year_id = null` ke id tahun ajaran yang baru dibuat
  - Daftarkan seeder di `DatabaseSeeder.php`
  - _Requirements: 7.1, 7.2, 7.3, 7.4_

- [x] 5. Buat `AcademicYearController` dan routes
  - Buat `app/Http/Controllers/AcademicYearController.php` dengan method:
    - `index()` — render halaman `Admin/AcademicYears/Index` dengan data semua tahun ajaran dan daftar jurusan
    - `store(Request $request)` — validasi input, panggil `AcademicYearService::create()`, redirect back
    - `update(Request $request, AcademicYear $academicYear)` — validasi `end_year > start_year`, update data, redirect back
    - `activate(AcademicYear $academicYear)` — panggil `AcademicYearService::activate()`, redirect back
    - `close(AcademicYear $academicYear)` — panggil `AcademicYearService::close()`, redirect back
    - `destroy(AcademicYear $academicYear)` — tolak jika ada student terkait, hapus jika tidak ada, redirect back
    - `setContext(Request $request)` — simpan `academic_year_context` ke session, redirect back
    - `majorConfig(AcademicYear $academicYear)` — render halaman `Admin/AcademicYears/MajorConfig` dengan data pivot jurusan
    - `updateMajorConfig(Request $request, AcademicYear $academicYear)` — sync pivot `academic_year_major` dengan kuota baru
  - Tambahkan routes di `routes/web.php` dalam grup middleware admin:
    - `GET /admin/academic-years` → `index`
    - `POST /admin/academic-years` → `store`
    - `PUT /admin/academic-years/{academicYear}` → `update`
    - `POST /admin/academic-years/{academicYear}/activate` → `activate`
    - `POST /admin/academic-years/{academicYear}/close` → `close`
    - `DELETE /admin/academic-years/{academicYear}` → `destroy`
    - `POST /admin/academic-years/set-context` → `setContext`
    - `GET /admin/academic-years/{academicYear}/majors` → `majorConfig`
    - `PUT /admin/academic-years/{academicYear}/majors` → `updateMajorConfig`
  - _Requirements: 1.1, 1.2, 1.3, 1.5, 1.6, 1.7, 3.2, 4.3, 4.5_


- [x] 6. Perbarui `HandleInertiaRequests` untuk share data tahun ajaran
  - Inject `AcademicYearService` ke `HandleInertiaRequests`
  - Tambahkan key `academicYear` ke array `share()` berisi:
    - `current`: hasil `resolveContext($request)` — konteks admin dari session
    - `active`: hasil `getActive()` — tahun ajaran aktif (untuk halaman publik/siswa)
    - `all`: hasil `getAll()` — daftar semua tahun ajaran (untuk selektor admin)
  - _Requirements: 4.1, 4.2, 4.5_

- [x] 7. Perbarui controller yang ada untuk mendukung konteks tahun ajaran
  - [x] 7.1 Perbarui `AdminController`:
    - Inject `AcademicYearService`, ambil konteks tahun ajaran dari session di setiap method
    - `dashboard()`: filter `Student::count()` dan statistik berdasarkan `academic_year_id` konteks
    - `students()`: tambah filter `academic_year_id` pada query, kirim `currentAcademicYear` ke view
    - `allocateMajor()`: ganti pengecekan kuota dari `$major->quota` ke `AcademicYearService::getQuotaForMajor()` dan `getAcceptedCountForMajor()` berdasarkan `academic_year_id` student
    - _Requirements: 2.7, 3.3, 3.4, 4.2_
  - [x] 7.2 Perbarui `ScheduleController`:
    - `index()` (publik): ambil jadwal dari tahun ajaran aktif saja (`academic_year_id = active`)
    - `adminIndex()`: filter jadwal berdasarkan konteks session admin
    - `store()`: tambah `academic_year_id` dari konteks session ke data yang disimpan
    - Validasi `academic_year_id` pada `store()` dan `update()`
    - _Requirements: 2.2, 2.4_
  - [x] 7.3 Perbarui `StudentController`:
    - `create()`: cek tahun ajaran aktif; jika tidak ada, kirim flag `registrationClosed: true` ke view
    - `store()`: ambil tahun ajaran aktif, return error 422 jika tidak ada; sertakan `academic_year_id` saat membuat/update student; generate `registration_number` dengan format `SPMB-{tahun}-{urutan}` scoped per tahun ajaran
    - Validasi duplikat NIK scoped per `academic_year_id` (bukan global)
    - _Requirements: 2.4, 2.5, 2.6, 6.1, 6.2, 6.5_
  - [x] 7.4 Perbarui `AdminReportController`:
    - Inject `AcademicYearService`, ambil konteks tahun ajaran dari session
    - `index()`: filter semua query berdasarkan `academic_year_id` konteks
    - `generatePdf()`: sertakan nama tahun ajaran di header PDF dan nama file
    - `exportCsv()`: sertakan nama tahun ajaran di nama file CSV
    - `getData()`: filter berdasarkan `academic_year_id` konteks
    - _Requirements: 5.1, 5.3, 5.5_
  - [x] 7.5 Perbarui `MajorController`:
    - `index()`: kirim data kuota per tahun ajaran aktif (dari pivot) ke view, bukan dari `majors.quota`
    - _Requirements: 3.2_


- [x] 8. Checkpoint — Pastikan semua tests pass
  - Pastikan semua tests pass, tanyakan kepada user jika ada pertanyaan.

- [x] 9. Buat halaman Vue baru untuk manajemen tahun ajaran
  - [x] 9.1 Buat komponen `resources/js/Components/AcademicYearSelector.vue`:
    - Dropdown yang menampilkan daftar tahun ajaran dari `$page.props.academicYear.all`
    - Tampilkan nama dan badge status (draft/active/closed) untuk setiap opsi
    - Saat dipilih, kirim POST ke `/admin/academic-years/set-context` via Inertia
    - Highlight tahun ajaran yang sedang aktif sebagai konteks (`academicYear.current`)
    - _Requirements: 4.1, 4.2, 4.5_
  - [x] 9.2 Buat halaman `resources/js/Pages/Admin/AcademicYears/Index.vue`:
    - Tabel daftar semua tahun ajaran dengan kolom: nama, tahun, status, jumlah siswa, aksi
    - Form inline untuk membuat tahun ajaran baru (nama, start_year, end_year, deskripsi)
    - Form edit per baris untuk memperbarui data tahun ajaran
    - Tombol Aktifkan (hanya untuk status draft/closed), Tutup (hanya untuk status active), Hapus (hanya jika tidak ada siswa)
    - Tampilkan pesan error dari server (kuota penuh, tidak bisa hapus, dll.)
    - _Requirements: 1.1, 1.2, 1.3, 1.5, 1.6, 1.7, 4.3_
  - [x] 9.3 Buat halaman `resources/js/Pages/Admin/AcademicYears/MajorConfig.vue`:
    - Tampilkan daftar jurusan dengan input kuota yang dapat diedit per baris
    - Toggle aktif/nonaktif per jurusan untuk tahun ajaran ini
    - Tombol simpan yang mengirim semua perubahan sekaligus via PUT
    - Tampilkan nama tahun ajaran di header halaman
    - _Requirements: 3.2, 4.3_

- [x] 10. Perbarui halaman Vue yang ada
  - [x] 10.1 Perbarui `resources/js/Layouts/AdminLayout.vue`:
    - Import dan tambahkan komponen `AcademicYearSelector` di navbar admin
    - Tampilkan nama tahun ajaran konteks saat ini secara konsisten
    - _Requirements: 4.1_
  - [x] 10.2 Perbarui `resources/js/Pages/Admin/Students/Index.vue`:
    - Tampilkan badge nama tahun ajaran aktif di header halaman
    - Nonaktifkan tombol verifikasi, alokasi, dan hapus jika konteks tahun ajaran berstatus `closed`
    - Tampilkan banner "Mode Hanya-Baca" jika konteks `closed`
    - _Requirements: 2.7, 4.4_
  - [x] 10.3 Perbarui `resources/js/Pages/Admin/Schedules/Index.vue`:
    - Tampilkan jadwal yang sudah difilter berdasarkan konteks tahun ajaran
    - Nonaktifkan form tambah/edit/hapus jika konteks `closed`
    - _Requirements: 2.2, 4.4_
  - [x] 10.4 Perbarui `resources/js/Pages/Admin/Reports/Index.vue`:
    - Tampilkan nama tahun ajaran di header halaman laporan
    - Tambahkan informasi tahun ajaran pada label tombol ekspor PDF dan CSV
    - _Requirements: 5.1, 5.3, 5.5_
  - [x] 10.5 Perbarui `resources/js/Pages/Home.vue`:
    - Tampilkan informasi tahun ajaran aktif (nama, jadwal) di halaman publik
    - Sembunyikan form pendaftaran dan tampilkan pesan "Pendaftaran belum dibuka" jika tidak ada tahun ajaran aktif
    - _Requirements: 6.1, 6.2_
  - [x] 10.6 Perbarui `resources/js/Pages/Dashboard.vue` (dashboard admin):
    - Tampilkan statistik yang sudah difilter berdasarkan konteks tahun ajaran
    - Tampilkan nama tahun ajaran konteks di header statistik
    - _Requirements: 4.2_


- [x] 11. Tulis property-based tests (Eris) untuk correctness properties
  - Pastikan Eris sudah terpasang: `composer require --dev giorgiosironi/eris`
  - Buat file `tests/Feature/AcademicYear/AcademicYearPropertyTest.php`
  - [x]* 11.1 Tulis property test untuk Property 1: Status awal selalu draft
    - **Property 1: Status awal selalu draft**
    - Gunakan `forAll(Generator\string())` untuk nama acak; verifikasi `status === 'draft'` setelah `AcademicYearService::create()`
    - **Validates: Requirements 1.2**
  - [x]* 11.2 Tulis property test untuk Property 2: Invariant satu tahun ajaran aktif
    - **Property 2: Invariant satu tahun ajaran aktif**
    - Gunakan `forAll(Generator\choose(1, 10))` untuk jumlah tahun ajaran; aktifkan salah satu secara acak; verifikasi `AcademicYear::where('status','active')->count() === 1`
    - **Validates: Requirements 1.3, 1.4**
  - [x]* 11.3 Tulis property test untuk Property 3: Penutupan tahun ajaran aktif
    - **Property 3: Penutupan tahun ajaran aktif**
    - Buat tahun ajaran aktif, jalankan `close()`, verifikasi status berubah ke `closed` dan tidak ada tahun ajaran lain yang berubah status
    - **Validates: Requirements 1.5**
  - [x]* 11.4 Tulis property test untuk Property 4: Penolakan penghapusan tahun ajaran berdata
    - **Property 4: Penolakan penghapusan tahun ajaran berdata**
    - Gunakan `forAll(Generator\choose(1, 5))` untuk jumlah siswa; verifikasi `destroy()` melempar exception dan record tetap ada
    - **Validates: Requirements 1.6**
  - [x]* 11.5 Tulis property test untuk Property 5: Validasi rentang tahun
    - **Property 5: Validasi rentang tahun**
    - Gunakan `forAll(Generator\choose(2000, 2100), Generator\choose(2000, 2100))` untuk pasangan tahun; verifikasi berhasil hanya jika `end_year > start_year`
    - **Validates: Requirements 1.7**
  - [x]* 11.6 Tulis property test untuk Property 6: Isolasi data entitas ke tahun ajaran
    - **Property 6: Isolasi data entitas ke tahun ajaran**
    - Buat student dan schedule melalui service; verifikasi `academic_year_id` tidak null dan merujuk ke tahun ajaran valid
    - **Validates: Requirements 2.1, 2.2**
  - [x]* 11.7 Tulis property test untuk Property 7: Pendaftaran otomatis terikat ke tahun ajaran aktif
    - **Property 7: Pendaftaran otomatis terikat ke tahun ajaran aktif**
    - Gunakan `forAll(Generator\string())` untuk data siswa acak; verifikasi `academic_year_id` student sama dengan id tahun ajaran aktif
    - **Validates: Requirements 2.4**
  - [x]* 11.8 Tulis property test untuk Property 8: Penolakan pendaftaran tanpa tahun ajaran aktif
    - **Property 8: Penolakan pendaftaran tanpa tahun ajaran aktif**
    - Pastikan tidak ada tahun ajaran aktif; verifikasi pendaftaran ditolak dan jumlah student tidak bertambah
    - **Validates: Requirements 2.5**
  - [x]* 11.9 Tulis property test untuk Property 9: Format dan keunikan nomor pendaftaran per tahun ajaran
    - **Property 9: Format dan keunikan nomor pendaftaran per tahun ajaran**
    - Gunakan `forAll(Generator\choose(1, 50))` untuk jumlah pendaftaran; verifikasi setiap nomor unik dan cocok dengan pola `SPMB-{tahun}-\d{4}`
    - **Validates: Requirements 2.6**
  - [x]* 11.10 Tulis property test untuk Property 10: Filter data siswa berdasarkan konteks tahun ajaran
    - **Property 10: Filter data siswa berdasarkan konteks tahun ajaran**
    - Buat siswa di beberapa tahun ajaran berbeda; query dengan konteks tertentu; verifikasi semua hasil memiliki `academic_year_id` yang sama
    - **Validates: Requirements 2.7, 4.2**
  - [x]* 11.11 Tulis property test untuk Property 11: Penyalinan konfigurasi jurusan ke tahun ajaran baru
    - **Property 11: Penyalinan konfigurasi jurusan ke tahun ajaran baru**
    - Gunakan `forAll(Generator\choose(1, 8))` untuk jumlah jurusan; buat tahun ajaran baru; verifikasi pivot berisi semua jurusan dengan kuota yang sama
    - **Validates: Requirements 3.1**
  - [x]* 11.12 Tulis property test untuk Property 12: Isolasi konfigurasi kuota antar tahun ajaran
    - **Property 12: Isolasi konfigurasi kuota antar tahun ajaran**
    - Ubah kuota jurusan di tahun ajaran A; verifikasi kuota jurusan yang sama di tahun ajaran B tidak berubah
    - **Validates: Requirements 3.2**
  - [x]* 11.13 Tulis property test untuk Property 13: Validasi kuota saat alokasi siswa
    - **Property 13: Validasi kuota saat alokasi siswa**
    - Gunakan `forAll(Generator\choose(1, 20))` untuk kuota; isi kuota penuh; verifikasi alokasi ke-n+1 ditolak
    - **Validates: Requirements 3.3, 3.4**
  - [x]* 11.14 Tulis property test untuk Property 14: Konteks tahun ajaran tersimpan di session
    - **Property 14: Konteks tahun ajaran tersimpan di session**
    - Gunakan `forAll(Generator\choose(1, 5))` untuk jumlah tahun ajaran; pilih salah satu; verifikasi session `academic_year_context` diperbarui dan konsisten di request berikutnya
    - **Validates: Requirements 4.5**
  - [x]* 11.15 Tulis property test untuk Property 15: Statistik laporan konsisten dengan filter tahun ajaran
    - **Property 15: Statistik laporan konsisten dengan filter tahun ajaran**
    - Buat data di beberapa tahun ajaran; ambil statistik dengan filter; verifikasi total = penjumlahan sub-kategori dan hanya mencakup data tahun ajaran yang difilter
    - **Validates: Requirements 5.1, 5.5**
  - [x]* 11.16 Tulis property test untuk Property 16: Nama tahun ajaran pada ekspor
    - **Property 16: Nama tahun ajaran pada ekspor**
    - Gunakan `forAll(Generator\string())` untuk nama tahun ajaran; generate ekspor; verifikasi nama tahun ajaran ada di nama file dan header
    - **Validates: Requirements 5.3**
  - [x]* 11.17 Tulis property test untuk Property 17: Isolasi data siswa (keamanan akses)
    - **Property 17: Isolasi data siswa (keamanan akses)**
    - Buat beberapa siswa; autentikasi sebagai siswa A; verifikasi endpoint hanya mengembalikan data siswa A meskipun id/registration_number siswa lain diberikan
    - **Validates: Requirements 6.4**
  - [x]* 11.18 Tulis property test untuk Property 18: Penolakan pendaftaran duplikat NIK per tahun ajaran
    - **Property 18: Penolakan pendaftaran duplikat NIK per tahun ajaran**
    - Gunakan `forAll(Generator\string())` untuk NIK; daftarkan dua kali dengan NIK yang sama di tahun ajaran aktif yang sama; verifikasi ditolak dan jumlah record tidak bertambah
    - **Validates: Requirements 6.5**
  - [x]* 11.19 Tulis property test untuk Property 19: Idempoten migrasi data historis
    - **Property 19: Idempoten migrasi data historis**
    - Jalankan `HistoricalDataMigrationSeeder` dua kali; verifikasi jumlah `AcademicYear`, `Student`, dan `Schedule` tidak berubah setelah eksekusi kedua
    - **Validates: Requirements 7.4**
  - [x]* 11.20 Tulis property test untuk Property 20: Kelengkapan migrasi data historis
    - **Property 20: Kelengkapan migrasi data historis**
    - Gunakan `forAll(Generator\choose(1, 20))` untuk jumlah student dan schedule; jalankan seeder; verifikasi tidak ada record dengan `academic_year_id = null`
    - **Validates: Requirements 7.2, 7.3**


- [x] 12. Tulis feature/unit tests (PHPUnit)
  - Buat file `tests/Feature/AcademicYear/AcademicYearFeatureTest.php`
  - [x]* 12.1 Tulis unit test untuk `AcademicYearService`
    - Test pembuatan tahun ajaran dengan data valid dan invalid
    - Test aktivasi: verifikasi hanya satu yang active, yang lain menjadi closed
    - Test penutupan: verifikasi status berubah ke closed
    - Test `copyMajorConfig`: verifikasi pivot tersalin dengan benar
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 3.1_
  - [x]* 12.2 Tulis feature test untuk halaman pendaftaran siswa
    - Test halaman pendaftaran menampilkan info tahun ajaran aktif (Requirements 6.1)
    - Test halaman pendaftaran menampilkan pesan "belum dibuka" jika tidak ada tahun ajaran aktif (Requirements 6.2)
    - Test dashboard siswa menampilkan nomor pendaftaran dan status yang benar (Requirements 6.3)
    - _Requirements: 6.1, 6.2, 6.3_
  - [x]* 12.3 Tulis feature test untuk `HandleInertiaRequests`
    - Test shared data `academicYear.current`, `academicYear.active`, dan `academicYear.all` tersedia di setiap halaman admin
    - Test `academicYear.active` tersedia di halaman publik
    - _Requirements: 4.1, 4.5_
  - [x]* 12.4 Tulis feature test untuk migrasi data historis
    - Buat fixture: beberapa student dan schedule tanpa `academic_year_id`
    - Jalankan `HistoricalDataMigrationSeeder`
    - Verifikasi satu `AcademicYear` dibuat dengan nama yang benar (Requirements 7.1)
    - Verifikasi semua student dan schedule terkait ke tahun ajaran tersebut (Requirements 7.2, 7.3)
    - _Requirements: 7.1, 7.2, 7.3_

- [x] 13. Checkpoint akhir — Pastikan semua tests pass
  - Pastikan semua tests pass, tanyakan kepada user jika ada pertanyaan.

## Catatan

- Task bertanda `*` bersifat opsional dan dapat dilewati untuk implementasi MVP yang lebih cepat
- Setiap task merujuk ke persyaratan spesifik untuk keterlacakan
- Checkpoint memastikan validasi inkremental di setiap tahap
- Property tests memvalidasi kebenaran universal di seluruh ruang input
- Unit/feature tests memvalidasi contoh spesifik, edge case, dan alur autentikasi
