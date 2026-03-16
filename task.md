# SPMB SMKN 8 TIK Kota Jayapura - Task List

- [x] Planning & Setup
    - [x] Initialize Laravel project
    - [x] Install Laravel Breeze (Inertia + Vue)
    - [x] Configure database (MySQL)
- [x] Database System Design
    - [x] Setup models and migrations (`majors`, `students`, `student_majors`, `schedules`)
- [x] User Roles & Authentication
    - [x] Admin & Student roles implementation
    - [x] Basic Admin Dashboard
- [x] Student Features
    - [x] Biodata and Document Upload Form
    - [x] Major Selection Form (Dynamic Options)
    - [x] Generate Registration Proof PDF
    - [x] Reactive Announcement Checking
- [x] Admin Features
    - [x] Verify Documents
    - [x] Manage Schedules
    - [x] Allocate Majors (Seleksi)
- [x] Final Verification
    - [x] End-to-end testing

## Summary

Aplikasi SPMB untuk SMKN 8 TIK Kota Jayapura telah selesai dibuat dengan fitur:

### Public Pages

- **Home** (`/`) - Landing page dengan jadwal SPMB dan info jurusan
- **Pendaftaran** (`/daftar`) - Form pendaftaran multi-step (biodata, pilihan jurusan, upload berkas)
- **Sertifikat** (`/pendaftaran/{nomor}`) - Halaman konfirmasi pendaftaran dengan opsi cetak PDF
- **Pengumuman** (`/pengumuman`) - Cek hasil seleksi dengan nomor pendaftaran dan NIK

### Admin Pages (requires login)

- **Dashboard** (`/dashboard`) - Statistik dan overview pendaftar
- **Kelola Pendaftar** (`/admin/students`) - Verifikasi dokumen dan alokasi jurusan
- **Kelola Jadwal** (`/admin/schedules`) - CRUD jadwal PPDB

### Default Credentials

- Email: `admin@smkn8jayapura.sch.id`
- Password: `admin123`

### Cara Menjalankan

```bash
# Terminal 1 - Laravel Server
php artisan serve

# Terminal 2 - Vite Dev Server
npm run dev
```

Akses aplikasi di: http://localhost:8000
