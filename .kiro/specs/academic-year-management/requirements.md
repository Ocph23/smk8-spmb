# Dokumen Persyaratan: Academic Year Management

## Pendahuluan

Fitur ini menambahkan dukungan multi-tahun ajaran pada aplikasi SPMB (Sistem Penerimaan Murid Baru) SMKN 8 TIK Jayapura. Saat ini seluruh data siswa, jurusan, dan jadwal bercampur tanpa pemisahan tahun ajaran. Fitur ini memungkinkan admin mengelola setiap tahun ajaran secara terpisah, sehingga data historis tetap terjaga dan dapat dibandingkan antar tahun.

## Glosarium

- **Sistem**: Aplikasi SPMB SMKN 8 TIK Jayapura berbasis Laravel + Inertia + Vue.
- **Admin**: Pengguna dengan peran administrator yang mengelola proses penerimaan.
- **Siswa**: Calon peserta didik baru yang mendaftar melalui sistem.
- **Tahun_Ajaran**: Entitas yang merepresentasikan satu periode penerimaan siswa baru, contoh "2025/2026". Memiliki status: `draft`, `active`, `closed`.
- **Tahun_Ajaran_Aktif**: Satu-satunya Tahun_Ajaran dengan status `active` pada satu waktu tertentu.
- **Jurusan**: Program keahlian (Major) yang tersedia pada suatu Tahun_Ajaran, beserta kuota penerimaan.
- **Jadwal**: Tahapan kegiatan SPMB (Schedule) yang terikat pada suatu Tahun_Ajaran.
- **Pendaftaran**: Data lengkap seorang Siswa yang terikat pada satu Tahun_Ajaran.
- **Nomor_Pendaftaran**: Identifikasi unik Pendaftaran dengan format `SPMB-{tahun}-{urutan}`, contoh `SPMB-2026-0001`.
- **Laporan**: Ringkasan statistik dan daftar Pendaftaran untuk suatu Tahun_Ajaran.

---

## Persyaratan

### Persyaratan 1: Pengelolaan Tahun Ajaran

**User Story:** Sebagai Admin, saya ingin membuat dan mengelola tahun ajaran, agar setiap periode penerimaan siswa baru memiliki data yang terpisah dan terorganisir.

#### Kriteria Penerimaan

1. THE Sistem SHALL menyediakan entitas Tahun_Ajaran dengan atribut: nama (contoh "2025/2026"), tahun mulai, tahun selesai, status (`draft`, `active`, `closed`), dan deskripsi opsional.
2. WHEN Admin membuat Tahun_Ajaran baru, THE Sistem SHALL menyimpan Tahun_Ajaran dengan status awal `draft`.
3. WHEN Admin mengaktifkan sebuah Tahun_Ajaran, THE Sistem SHALL mengubah status Tahun_Ajaran tersebut menjadi `active` dan mengubah status semua Tahun_Ajaran lain yang sebelumnya `active` menjadi `closed`.
4. THE Sistem SHALL memastikan hanya satu Tahun_Ajaran berstatus `active` pada satu waktu.
5. WHEN Admin menutup Tahun_Ajaran_Aktif, THE Sistem SHALL mengubah statusnya menjadi `closed` dan memastikan tidak ada Tahun_Ajaran_Aktif setelahnya sampai Admin mengaktifkan yang baru.
6. IF Admin mencoba menghapus Tahun_Ajaran yang memiliki data Pendaftaran, THEN THE Sistem SHALL menolak penghapusan dan menampilkan pesan kesalahan yang menjelaskan alasan penolakan.
7. WHEN Admin memperbarui data Tahun_Ajaran, THE Sistem SHALL memvalidasi bahwa tahun mulai lebih kecil dari tahun selesai sebelum menyimpan perubahan.

---

### Persyaratan 2: Isolasi Data Per Tahun Ajaran

**User Story:** Sebagai Admin, saya ingin data siswa, jurusan, dan jadwal setiap tahun ajaran tersimpan terpisah, agar data historis tidak tercampur dan tetap akurat.

#### Kriteria Penerimaan

1. THE Sistem SHALL mengaitkan setiap Pendaftaran dengan tepat satu Tahun_Ajaran melalui foreign key `academic_year_id`.
2. THE Sistem SHALL mengaitkan setiap Jadwal dengan tepat satu Tahun_Ajaran melalui foreign key `academic_year_id`.
3. THE Sistem SHALL mengaitkan konfigurasi kuota Jurusan per Tahun_Ajaran melalui tabel pivot `academic_year_major` yang menyimpan `academic_year_id`, `major_id`, dan `quota`.
4. WHEN Siswa melakukan pendaftaran, THE Sistem SHALL secara otomatis mengaitkan Pendaftaran tersebut dengan Tahun_Ajaran_Aktif.
5. IF tidak ada Tahun_Ajaran_Aktif saat Siswa mencoba mendaftar, THEN THE Sistem SHALL menampilkan pesan bahwa pendaftaran belum dibuka dan mencegah proses pendaftaran berlanjut.
6. THE Sistem SHALL memastikan Nomor_Pendaftaran unik dalam lingkup satu Tahun_Ajaran, dengan format `SPMB-{tahun}-{urutan 4 digit}`.
7. WHEN Admin melihat daftar Siswa, THE Sistem SHALL menampilkan hanya Pendaftaran yang terkait dengan Tahun_Ajaran yang dipilih Admin.

---

### Persyaratan 3: Konfigurasi Jurusan Per Tahun Ajaran

**User Story:** Sebagai Admin, saya ingin mengatur jurusan dan kuota yang tersedia untuk setiap tahun ajaran, agar fleksibilitas pengelolaan program keahlian terjaga dari tahun ke tahun.

#### Kriteria Penerimaan

1. WHEN Admin membuat Tahun_Ajaran baru, THE Sistem SHALL menyalin konfigurasi Jurusan beserta kuota dari Tahun_Ajaran terakhir sebagai nilai awal, jika Tahun_Ajaran sebelumnya tersedia.
2. THE Admin SHALL dapat menambah, mengubah kuota, atau menonaktifkan Jurusan pada suatu Tahun_Ajaran tanpa memengaruhi konfigurasi Jurusan di Tahun_Ajaran lain.
3. WHEN Admin mengalokasikan Siswa ke Jurusan, THE Sistem SHALL memvalidasi bahwa jumlah Siswa yang diterima pada Jurusan tersebut dalam Tahun_Ajaran yang sama belum melebihi kuota yang dikonfigurasi untuk Tahun_Ajaran tersebut.
4. IF kuota Jurusan pada Tahun_Ajaran_Aktif sudah penuh, THEN THE Sistem SHALL menolak alokasi tambahan dan menampilkan pesan yang menyebutkan nama Jurusan dan batas kuota.

---

### Persyaratan 4: Navigasi dan Konteks Tahun Ajaran di Antarmuka Admin

**User Story:** Sebagai Admin, saya ingin dapat berpindah antar tahun ajaran di antarmuka, agar saya bisa melihat dan mengelola data tahun ajaran mana pun dengan mudah.

#### Kriteria Penerimaan

1. THE Sistem SHALL menampilkan indikator Tahun_Ajaran yang sedang dilihat (konteks aktif) secara konsisten di seluruh halaman admin.
2. WHEN Admin memilih Tahun_Ajaran dari selektor, THE Sistem SHALL memperbarui seluruh data yang ditampilkan (siswa, jadwal, laporan) sesuai Tahun_Ajaran yang dipilih.
3. THE Sistem SHALL menampilkan daftar semua Tahun_Ajaran yang tersedia beserta statusnya di halaman manajemen Tahun_Ajaran.
4. WHILE Admin berada dalam konteks Tahun_Ajaran berstatus `closed`, THE Sistem SHALL menampilkan data dalam mode hanya-baca dan menonaktifkan aksi yang bersifat mengubah data Pendaftaran.
5. THE Sistem SHALL menyimpan preferensi konteks Tahun_Ajaran yang terakhir dipilih Admin dalam sesi pengguna, sehingga tidak hilang saat Admin berpindah halaman.

---

### Persyaratan 5: Laporan Per Tahun Ajaran

**User Story:** Sebagai Admin, saya ingin melihat laporan dan statistik per tahun ajaran, agar saya dapat menganalisis tren penerimaan dari tahun ke tahun.

#### Kriteria Penerimaan

1. WHEN Admin membuka halaman laporan, THE Sistem SHALL menampilkan statistik Pendaftaran yang difilter berdasarkan Tahun_Ajaran yang dipilih.
2. THE Sistem SHALL menyediakan laporan perbandingan antar Tahun_Ajaran yang menampilkan: total pendaftar, total diterima, dan distribusi per Jurusan untuk setiap Tahun_Ajaran.
3. WHEN Admin mengekspor laporan ke PDF atau CSV, THE Sistem SHALL menyertakan nama Tahun_Ajaran pada header dokumen dan nama file ekspor.
4. THE Sistem SHALL menampilkan grafik tren jumlah pendaftar dan penerimaan per Jurusan yang dapat dibandingkan lintas Tahun_Ajaran.
5. WHEN Admin memfilter laporan berdasarkan Tahun_Ajaran, THE Sistem SHALL memperbarui semua statistik dan grafik secara konsisten sesuai filter yang dipilih.

---

### Persyaratan 6: Pengalaman Pendaftaran Siswa

**User Story:** Sebagai Siswa, saya ingin mendaftar pada tahun ajaran yang sedang berjalan dan melihat status pendaftaran saya, agar saya tahu apakah saya diterima pada tahun ajaran yang saya ikuti.

#### Kriteria Penerimaan

1. WHEN Siswa mengakses halaman pendaftaran, THE Sistem SHALL menampilkan informasi Tahun_Ajaran_Aktif beserta jadwal tahapan yang berlaku.
2. WHILE tidak ada Tahun_Ajaran_Aktif, THE Sistem SHALL menampilkan halaman informasi bahwa pendaftaran belum dibuka dan menyembunyikan formulir pendaftaran.
3. WHEN Siswa login ke dashboard, THE Sistem SHALL menampilkan Nomor_Pendaftaran, status verifikasi, dan hasil penerimaan yang terkait dengan Tahun_Ajaran pada saat Siswa mendaftar.
4. THE Sistem SHALL memastikan Siswa hanya dapat melihat data Pendaftaran miliknya sendiri, tanpa dapat mengakses data Pendaftaran Siswa lain atau Tahun_Ajaran lain.
5. IF Siswa yang sama (berdasarkan NIK) mencoba mendaftar kembali pada Tahun_Ajaran_Aktif yang sama, THEN THE Sistem SHALL menolak pendaftaran duplikat dan menampilkan pesan yang menginformasikan bahwa NIK tersebut sudah terdaftar pada tahun ajaran ini.

---

### Persyaratan 7: Migrasi Data Historis

**User Story:** Sebagai Admin, saya ingin data siswa yang sudah ada sebelum fitur ini diterapkan tetap dapat diakses, agar tidak ada data yang hilang saat sistem diperbarui.

#### Kriteria Penerimaan

1. WHEN migrasi database dijalankan, THE Sistem SHALL membuat satu Tahun_Ajaran default dengan nama yang diturunkan dari tahun pada Nomor_Pendaftaran yang sudah ada (contoh: jika ada `SPMB-2026-0001`, maka dibuat Tahun_Ajaran "2025/2026").
2. WHEN migrasi database dijalankan, THE Sistem SHALL mengaitkan semua Pendaftaran yang sudah ada ke Tahun_Ajaran default tersebut.
3. WHEN migrasi database dijalankan, THE Sistem SHALL mengaitkan semua Jadwal yang sudah ada ke Tahun_Ajaran default tersebut.
4. THE Sistem SHALL memastikan migrasi data historis dapat dijalankan ulang (idempoten) tanpa menyebabkan duplikasi data atau error.
