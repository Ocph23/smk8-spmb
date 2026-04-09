# Dokumen Desain Teknis: Gelombang Pendaftaran (Enrollment Wave)

## Ikhtisar

Fitur Gelombang Pendaftaran memperkenalkan lapisan baru di antara `AcademicYear` dan `Student`
yang memungkinkan satu tahun ajaran dibagi menjadi beberapa periode pendaftaran (gelombang).
Setiap gelombang memiliki siklus hidup independen (`draft → open → closed → announced`),
kuota per jurusan yang dihitung otomatis dari sisa kuota tahun ajaran, dan kode Romawi pada
nomor pendaftaran siswa.

Desain ini memperluas sistem yang sudah ada secara minimal: menambah dua tabel baru, satu
kolom FK pada `students`, satu model baru, satu service baru, satu controller baru, dan
memodifikasi beberapa komponen yang sudah ada.

---

## Arsitektur

```
┌─────────────────────────────────────────────────────────────────┐
│                        HTTP Layer                               │
│  EnrollmentWaveController  StudentController  AdminController   │
│  AnnouncementController    HandleInertiaRequests                │
└────────────────────────┬────────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────────┐
│                      Service Layer                              │
│  EnrollmentWaveService          AcademicYearService (existing)  │
└────────────────────────┬────────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────────┐
│                      Model Layer                                │
│  EnrollmentWave  ←──── Student (+ enrollment_wave_id)           │
│       │                                                         │
│       └──── enrollment_wave_major (pivot)                       │
│                                                                 │
│  AcademicYear ──── EnrollmentWave (hasMany)                     │
│  Major        ──── EnrollmentWave (belongsToMany via pivot)     │
└─────────────────────────────────────────────────────────────────┘
```

### Alur Pendaftaran Siswa (Diperbarui)

```
Siswa submit form
      │
      ▼
AcademicYear active? ──No──► Tolak: "Tidak ada tahun ajaran aktif"
      │ Yes
      ▼
EnrollmentWave open? ──No──► Tolak: "Tidak ada gelombang aktif"
      │ Yes
      ▼
NIK sudah ada di academic_year? ──Yes──► Tolak: "NIK sudah terdaftar"
      │ No
      ▼
Generate SPMB-{year}-{Romawi}-{seq}
      │
      ▼
Simpan Student dengan enrollment_wave_id
```

### Alur Transisi Status Gelombang

```
draft ──[open]──► open ──[close]──► closed ──[announce]──► announced
  │
  └──[delete]──► (dihapus)
```

---

## Komponen dan Antarmuka

### Helper: `RomanNumeralHelper`

```php
// app/Helpers/RomanNumeralHelper.php
class RomanNumeralHelper
{
    public static function toRoman(int $number): string;
    // Mendukung 1–3999, melempar InvalidArgumentException jika di luar range
}
```

### Model: `EnrollmentWave`

Relasi dan method utama:
- `academicYear()` — BelongsTo AcademicYear
- `majors()` — BelongsToMany Major via `enrollment_wave_major` (pivot: `quota`)
- `students()` — HasMany Student
- `isDraft()`, `isOpen()`, `isClosed()`, `isAnnounced()` — helper status
- `canTransitionTo(string $status): bool` — validasi transisi state machine

### Service: `EnrollmentWaveService`

Method utama:
- `getOpenWaveForActiveYear(): ?EnrollmentWave`
- `createWave(AcademicYear $year, array $data): EnrollmentWave`
- `calculateInitialQuotas(AcademicYear $year): array` — hitung sisa kuota per jurusan
- `openWave(EnrollmentWave $wave): void`
- `closeWave(EnrollmentWave $wave): void`
- `announceWave(EnrollmentWave $wave): void`
- `deleteWave(EnrollmentWave $wave): void`
- `updateQuotas(EnrollmentWave $wave, array $quotas): void`
- `getWaveQuota(EnrollmentWave $wave, int $majorId): int`
- `getAcceptedCountInWave(EnrollmentWave $wave, int $majorId): int`
- `generateRegistrationNumber(EnrollmentWave $wave): string`

### Controller: `EnrollmentWaveController`

Route resource + aksi transisi status:
- `index()` — daftar gelombang per tahun ajaran konteks
- `store()` — buat gelombang baru
- `show()` — detail gelombang + statistik kuota
- `update()` — edit nama/deskripsi/tanggal (hanya status draft)
- `destroy()` — hapus gelombang (hanya status draft)
- `open()` — transisi ke open
- `close()` — transisi ke closed
- `announce()` — transisi ke announced
- `updateQuotas()` — edit kuota per jurusan (hanya status draft)

### Perubahan `StudentController`

- `store()`: ganti `getActive()` dengan `getOpenWaveForActiveYear()`, generate nomor pendaftaran format baru, simpan `enrollment_wave_id`
- `generateRegistrationNumber()`: delegasi ke `EnrollmentWaveService`

### Perubahan `AdminController`

- `allocateMajor()`: ganti pengecekan kuota dari level tahun ajaran ke level gelombang menggunakan `EnrollmentWaveService::getWaveQuota()` dan `getAcceptedCountInWave()`

### Perubahan `AnnouncementController`

- `check()`: tambah validasi bahwa gelombang siswa berstatus `announced` sebelum menampilkan hasil

### Perubahan `HandleInertiaRequests`

- Tambah `enrollmentWave.open` — gelombang yang sedang open (untuk form pendaftaran publik)

---

## Model Data

### Tabel Baru: `enrollment_waves`

```sql
CREATE TABLE enrollment_waves (
    id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    academic_year_id  BIGINT UNSIGNED NOT NULL,
    name              VARCHAR(100) NOT NULL,          -- "Gelombang I"
    wave_number       TINYINT UNSIGNED NOT NULL,      -- 1, 2, 3, ...
    status            ENUM('draft','open','closed','announced') NOT NULL DEFAULT 'draft',
    open_date         DATE NULL,
    close_date        DATE NULL,
    description       TEXT NULL,
    created_at        TIMESTAMP NULL,
    updated_at        TIMESTAMP NULL,

    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
    UNIQUE KEY uq_wave_per_year (academic_year_id, wave_number),
    INDEX idx_status (status)
);
```

### Tabel Baru: `enrollment_wave_major`

```sql
CREATE TABLE enrollment_wave_major (
    id                   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    enrollment_wave_id   BIGINT UNSIGNED NOT NULL,
    major_id             BIGINT UNSIGNED NOT NULL,
    quota                SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at           TIMESTAMP NULL,
    updated_at           TIMESTAMP NULL,

    FOREIGN KEY (enrollment_wave_id) REFERENCES enrollment_waves(id) ON DELETE CASCADE,
    FOREIGN KEY (major_id) REFERENCES majors(id) ON DELETE CASCADE,
    UNIQUE KEY uq_wave_major (enrollment_wave_id, major_id)
);
```

### Perubahan Tabel `students`

```sql
ALTER TABLE students
    ADD COLUMN enrollment_wave_id BIGINT UNSIGNED NULL AFTER academic_year_id,
    ADD FOREIGN KEY (enrollment_wave_id) REFERENCES enrollment_waves(id) ON DELETE SET NULL;
```

### Diagram Relasi

```
academic_years
    │ id
    │ status: draft|active|closed
    │
    ├──[hasMany]──► enrollment_waves
    │                   │ id
    │                   │ academic_year_id (FK)
    │                   │ wave_number
    │                   │ status: draft|open|closed|announced
    │                   │
    │                   ├──[belongsToMany via enrollment_wave_major]──► majors
    │                   │       pivot: quota
    │                   │
    │                   └──[hasMany]──► students
    │                                       │ enrollment_wave_id (FK, nullable)
    │                                       │ academic_year_id (FK)
    │                                       │ registration_number: SPMB-2026-I-0001
    │
    └──[belongsToMany via academic_year_major]──► majors
            pivot: quota (Kuota_Total), is_active
```

### Format Nomor Pendaftaran

| Komponen | Sumber | Contoh |
|---|---|---|
| `SPMB` | Literal | `SPMB` |
| `{end_year}` | `academic_years.end_year` | `2026` |
| `{Romawi}` | `RomanNumeralHelper::toRoman(wave_number)` | `II` |
| `{urutan}` | COUNT per gelombang, zero-padded 4 digit | `0001` |

Contoh lengkap: `SPMB-2026-II-0001`

Urutan dihitung per gelombang (bukan per tahun ajaran), sehingga Gelombang I dan Gelombang II
masing-masing dimulai dari `0001`.

---

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions
of a system — essentially, a formal statement about what the system should do. Properties
serve as the bridge between human-readable specifications and machine-verifiable correctness
guarantees.*

### Property 1: Gelombang baru selalu berstatus draft

*For any* data gelombang yang valid (nama, wave_number, tanggal), gelombang yang baru dibuat
SHALL selalu memiliki status `draft`, terlepas dari input lainnya.

**Validates: Requirements 1.2**

---

### Property 2: Maksimal satu gelombang open per tahun ajaran

*For any* tahun ajaran dengan sejumlah gelombang, setelah operasi open berhasil, jumlah
gelombang berstatus `open` dalam tahun ajaran tersebut SHALL selalu tepat satu.

**Validates: Requirements 1.4**

---

### Property 3: Transisi status hanya mengikuti urutan yang valid

*For any* gelombang dengan status S, transisi ke status T SHALL berhasil jika dan hanya jika
(S, T) ∈ {(draft, open), (open, closed), (closed, announced)}. Semua transisi lain SHALL
ditolak.

**Validates: Requirements 1.8, 1.9**

---

### Property 4: Penghapusan hanya diizinkan untuk status draft

*For any* gelombang dengan status selain `draft` (yaitu open, closed, atau announced),
operasi hapus SHALL selalu ditolak dengan pesan kesalahan.

**Validates: Requirements 1.10, 1.11**

---

### Property 5: Kuota awal gelombang = Kuota_Total − siswa diterima sebelumnya

*For any* tahun ajaran dengan Kuota_Total Q per jurusan dan A siswa yang sudah diterima
(is_accepted = true) pada gelombang-gelombang sebelumnya, kuota awal gelombang baru SHALL
selalu sama dengan Q − A untuk setiap jurusan.

**Validates: Requirements 2.3**

---

### Property 6: Alokasi tidak boleh melebihi Kuota_Gelombang

*For any* gelombang dengan Kuota_Gelombang K untuk suatu jurusan, setelah K siswa diterima
pada jurusan tersebut dalam gelombang yang sama, alokasi siswa ke-(K+1) SHALL selalu ditolak
dengan pesan kesalahan yang menyebutkan nama jurusan dan nilai kuota.

**Validates: Requirements 2.6, 2.7**

---

### Property 7: Siswa selalu masuk ke gelombang yang sedang open

*For any* tahun ajaran active dengan tepat satu gelombang berstatus `open`, siswa yang
berhasil mendaftar SHALL selalu memiliki `enrollment_wave_id` yang menunjuk ke gelombang
tersebut.

**Validates: Requirements 3.1, 3.3**

---

### Property 8: Pendaftaran ditolak jika tidak ada gelombang open

*For any* tahun ajaran active tanpa gelombang berstatus `open`, setiap upaya pendaftaran
SHALL selalu ditolak dengan pesan: "Pendaftaran belum dibuka. Tidak ada gelombang pendaftaran
yang aktif saat ini."

**Validates: Requirements 3.2**

---

### Property 9: NIK unik per tahun ajaran (lintas gelombang)

*For any* tahun ajaran active, jika NIK N sudah terdaftar pada gelombang manapun dalam
tahun ajaran tersebut, pendaftaran baru dengan NIK N SHALL selalu ditolak dengan pesan
kesalahan yang sesuai.

**Validates: Requirements 4.1, 4.2**

---

### Property 10: Format nomor pendaftaran selalu valid dan dapat di-parse kembali

*For any* kombinasi end_year Y dan wave_number W (1–10), nomor pendaftaran yang dihasilkan
SHALL selalu mengikuti format `SPMB-{Y}-{toRoman(W)}-{NNNN}` dan dapat di-parse kembali
untuk mengekstrak Y, W, dan urutan NNNN (round-trip property).

**Validates: Requirements 5.1, 5.3, 5.7**

---

### Property 11: Urutan nomor pendaftaran dimulai dari 0001 per gelombang

*For any* gelombang baru (tanpa siswa sebelumnya), siswa pertama yang mendaftar SHALL
mendapatkan urutan `0001`, dan setiap pendaftar berikutnya dalam gelombang yang sama SHALL
mendapatkan urutan yang bertambah satu.

**Validates: Requirements 5.4**

---

### Property 12: Hasil seleksi hanya terlihat untuk gelombang announced

*For any* siswa yang terdaftar pada gelombang dengan status selain `announced`, query
pengumuman dengan nomor pendaftaran siswa tersebut SHALL selalu mengembalikan pesan:
"Hasil seleksi untuk gelombang ini belum diumumkan."

**Validates: Requirements 6.2, 6.5**

---

### Property 13: Daftar gelombang selalu terurut ascending berdasarkan wave_number

*For any* tahun ajaran dengan sejumlah gelombang, query daftar gelombang SHALL selalu
mengembalikan gelombang dalam urutan wave_number ascending, terlepas dari urutan pembuatan.

**Validates: Requirements 7.1**

---

## Penanganan Error

| Kondisi | HTTP Status | Pesan |
|---|---|---|
| Tidak ada tahun ajaran active | 422 | "Pendaftaran belum dibuka. Tidak ada tahun ajaran aktif saat ini." |
| Tidak ada gelombang open | 422 | "Pendaftaran belum dibuka. Tidak ada gelombang pendaftaran yang aktif saat ini." |
| NIK sudah terdaftar di tahun ajaran | 422 | "NIK ini sudah terdaftar pada tahun ajaran yang sedang berjalan. Pendaftaran ulang tidak diizinkan." |
| Kuota gelombang penuh | 422 | "Kuota jurusan {nama} pada gelombang ini sudah penuh ({kuota})." |
| Transisi status tidak valid | 422 | "Transisi status dari {dari} ke {ke} tidak diizinkan." |
| Sudah ada gelombang open | 422 | "Gelombang {nama} sedang open. Tutup gelombang tersebut terlebih dahulu." |
| Hapus gelombang non-draft | 422 | "Hanya gelombang berstatus draft yang dapat dihapus." |
| Edit kuota gelombang non-draft | 422 | "Kuota hanya dapat diubah sebelum gelombang dibuka." |
| Nomor pendaftaran tidak ditemukan | 422 | "Nomor pendaftaran tidak ditemukan." |
| Gelombang belum announced | 422 | "Hasil seleksi untuk gelombang ini belum diumumkan." |

Semua error dikembalikan melalui `back()->withErrors(['error' => '...'])` untuk konsistensi
dengan pola yang sudah ada di codebase.

---

## Strategi Pengujian

### Pendekatan Dual Testing

Fitur ini menggunakan dua pendekatan pengujian yang saling melengkapi:

1. **Unit/Feature Tests** — menguji contoh spesifik, kondisi batas, dan integrasi antar komponen
2. **Property-Based Tests** — menguji properti universal di atas berbagai input yang di-generate

Library PBT yang digunakan: **[eris/eris](https://github.com/giorgiosironi/eris)** (PHP
property-based testing library untuk PHPUnit).

Setiap property test dikonfigurasi dengan minimum **100 iterasi**.

### Unit / Feature Tests

**`EnrollmentWaveServiceTest`**
- Pembuatan gelombang dengan status draft
- Kalkulasi kuota awal (gelombang pertama = kuota total, gelombang berikutnya = sisa)
- Transisi status valid dan invalid
- Penolakan hapus gelombang non-draft
- Penolakan edit kuota gelombang non-draft

**`StudentControllerTest`**
- Pendaftaran berhasil → enrollment_wave_id terisi, nomor pendaftaran format baru
- Pendaftaran ditolak saat tidak ada gelombang open
- Pendaftaran ditolak saat NIK duplikat dalam tahun ajaran

**`AdminControllerTest`**
- Alokasi berhasil saat kuota tersedia
- Alokasi ditolak saat kuota gelombang penuh

**`AnnouncementControllerTest`**
- Pengumuman berhasil untuk gelombang announced
- Pengumuman ditolak untuk gelombang non-announced

**`RomanNumeralHelperTest`**
- Konversi 1–10 ke Romawi (I–X)
- Exception untuk input di luar range

### Property-Based Tests

Setiap test di-tag dengan komentar referensi ke property di dokumen ini.

```php
// Tag format: Feature: enrollment-wave, Property {N}: {deskripsi singkat}
```

**Property 1** — `EnrollmentWavePropertyTest::testNewWaveAlwaysDraft`
```
// Feature: enrollment-wave, Property 1: Gelombang baru selalu berstatus draft
For any: nama (string), wave_number (int 1-10), open_date, close_date
Assert: $wave->status === 'draft'
```

**Property 2** — `EnrollmentWavePropertyTest::testAtMostOneOpenWavePerYear`
```
// Feature: enrollment-wave, Property 2: Maksimal satu gelombang open per tahun ajaran
For any: tahun ajaran dengan N gelombang (N ∈ [2..5])
Assert: count(waves where status=open) <= 1 setelah setiap operasi open
```

**Property 3** — `EnrollmentWavePropertyTest::testOnlyValidStatusTransitions`
```
// Feature: enrollment-wave, Property 3: Transisi status hanya mengikuti urutan valid
For any: pasangan (from_status, to_status)
Assert: berhasil iff (from, to) ∈ valid_transitions
```

**Property 4** — `EnrollmentWavePropertyTest::testDeleteOnlyAllowedForDraft`
```
// Feature: enrollment-wave, Property 4: Penghapusan hanya untuk status draft
For any: gelombang dengan status ∈ {open, closed, announced}
Assert: operasi hapus selalu ditolak
```

**Property 5** — `EnrollmentWavePropertyTest::testInitialQuotaEqualsRemainingQuota`
```
// Feature: enrollment-wave, Property 5: Kuota awal = Kuota_Total - siswa diterima
For any: kuota_total Q ∈ [1..50], accepted_count A ∈ [0..Q]
Assert: initial_quota === Q - A
```

**Property 6** — `EnrollmentWavePropertyTest::testAllocationRejectsWhenQuotaFull`
```
// Feature: enrollment-wave, Property 6: Alokasi ditolak saat kuota penuh
For any: gelombang dengan kuota K ∈ [1..10]
Assert: alokasi ke-(K+1) selalu ditolak
```

**Property 7** — `EnrollmentWavePropertyTest::testStudentAssignedToOpenWave`
```
// Feature: enrollment-wave, Property 7: Siswa masuk ke gelombang open
For any: tahun ajaran active dengan tepat satu gelombang open
Assert: student.enrollment_wave_id === open_wave.id
```

**Property 8** — `EnrollmentWavePropertyTest::testRegistrationRejectedWithNoOpenWave`
```
// Feature: enrollment-wave, Property 8: Pendaftaran ditolak tanpa gelombang open
For any: tahun ajaran active tanpa gelombang open
Assert: response status 422, pesan sesuai
```

**Property 9** — `EnrollmentWavePropertyTest::testNikUniquePerAcademicYear`
```
// Feature: enrollment-wave, Property 9: NIK unik per tahun ajaran
For any: NIK valid (16 digit), tahun ajaran active
Assert: pendaftaran kedua dengan NIK sama selalu ditolak
```

**Property 10** — `RomanNumeralPropertyTest::testRegistrationNumberRoundTrip`
```
// Feature: enrollment-wave, Property 10: Format nomor pendaftaran round-trip
For any: end_year Y ∈ [2020..2030], wave_number W ∈ [1..10], seq ∈ [1..9999]
Assert: parse(generate(Y, W, seq)) === (Y, W, seq)
```

**Property 11** — `EnrollmentWavePropertyTest::testSequenceStartsAtOnePerWave`
```
// Feature: enrollment-wave, Property 11: Urutan dimulai dari 0001 per gelombang
For any: gelombang baru
Assert: siswa pertama mendapat urutan 0001
```

**Property 12** — `AnnouncementPropertyTest::testResultHiddenForNonAnnouncedWave`
```
// Feature: enrollment-wave, Property 12: Hasil tersembunyi untuk gelombang non-announced
For any: siswa pada gelombang dengan status ∈ {draft, open, closed}
Assert: query pengumuman mengembalikan pesan "belum diumumkan"
```

**Property 13** — `EnrollmentWavePropertyTest::testWaveListAlwaysOrderedByWaveNumber`
```
// Feature: enrollment-wave, Property 13: Daftar gelombang terurut ascending
For any: N gelombang dengan wave_number acak ∈ [1..10]
Assert: hasil query selalu terurut wave_number ASC
```

### Konfigurasi Eris

```php
// phpunit.xml atau test class
use Eris\Generator;
use Eris\TestTrait;

class EnrollmentWavePropertyTest extends TestCase
{
    use TestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->limitTo(100); // minimum 100 iterasi per property
    }
}
```

---

## Halaman Vue (Baru dan Dimodifikasi)

### Halaman Baru

**`resources/js/Pages/Admin/EnrollmentWaves/Index.vue`**
- Daftar gelombang dalam tahun ajaran konteks
- Tombol buat gelombang baru (modal)
- Badge status dengan warna berbeda per status
- Statistik: jumlah pendaftar, jumlah diterima per gelombang
- Tombol transisi status (open/close/announce) sesuai status saat ini

**`resources/js/Pages/Admin/EnrollmentWaves/Show.vue`**
- Detail gelombang: nama, status, tanggal buka/tutup, deskripsi
- Tabel kuota per jurusan: Kuota_Gelombang, sudah diterima, sisa
- Form edit kuota (hanya tampil jika status draft)
- Tombol transisi status

### Halaman Dimodifikasi

**`resources/js/Pages/Student/Register.vue`**
- Tampilkan info gelombang aktif (nama, tanggal buka, tanggal tutup) jika ada
- Ganti pesan "Pendaftaran Ditutup" dengan info lebih spesifik (tidak ada gelombang open)

**`resources/js/Pages/Announcement/Result.vue`**
- Tambah tampilan nama gelombang pada info siswa
- Tangani kasus gelombang belum announced (tampilkan pesan yang sesuai)

**`resources/js/Pages/Admin/Students/Index.vue`**
- Tambah filter berdasarkan gelombang
- Tampilkan kolom gelombang pada tabel siswa

**`resources/js/Pages/Admin/Students/Show.vue`**
- Tampilkan info gelombang siswa
- Perbarui tampilan kuota pada form alokasi jurusan (kuota gelombang, bukan kuota tahun ajaran)

---

## Rute Baru

```php
// routes/web.php — dalam middleware admin
Route::prefix('/admin/enrollment-waves')->group(function () {
    Route::get('/',                                [EnrollmentWaveController::class, 'index'])
        ->name('admin.enrollment-waves.index');
    Route::post('/',                               [EnrollmentWaveController::class, 'store'])
        ->name('admin.enrollment-waves.store');
    Route::get('/{wave}',                          [EnrollmentWaveController::class, 'show'])
        ->name('admin.enrollment-waves.show');
    Route::put('/{wave}',                          [EnrollmentWaveController::class, 'update'])
        ->name('admin.enrollment-waves.update');
    Route::delete('/{wave}',                       [EnrollmentWaveController::class, 'destroy'])
        ->name('admin.enrollment-waves.destroy');
    Route::post('/{wave}/open',                    [EnrollmentWaveController::class, 'open'])
        ->name('admin.enrollment-waves.open');
    Route::post('/{wave}/close',                   [EnrollmentWaveController::class, 'close'])
        ->name('admin.enrollment-waves.close');
    Route::post('/{wave}/announce',                [EnrollmentWaveController::class, 'announce'])
        ->name('admin.enrollment-waves.announce');
    Route::put('/{wave}/quotas',                   [EnrollmentWaveController::class, 'updateQuotas'])
        ->name('admin.enrollment-waves.update-quotas');
});
```
