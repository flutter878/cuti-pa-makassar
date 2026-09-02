# PROGRESS — Sistem Informasi Cuti PA Makassar

> Terakhir diperbarui: 2026-09-02

---

## Status Keseluruhan

| Tahap | Deskripsi | Status |
|---|---|---|
| Tahap 1 | Setup Dasar (Laravel, DB, Auth, Role, Migration) | ✅ Selesai |
| Tahap 2 | Data Master (Pegawai, Jabatan, Unit Kerja, Jenis Cuti, Saldo) | ✅ Selesai |
| Tahap 3 | Pengajuan Cuti (Form, Validasi H-3, FIFO, Upload) | ⏳ Belum Mulai |
| Tahap 4 | Persetujuan (Dashboard Admin, Approve, Reject) | ⏳ Belum Mulai |
| Tahap 5 | Dokumen & Laporan (PDF, Filter, Export) | ⏳ Belum Mulai |
| Tahap 6 | Pengujian Menyeluruh | ⏳ Belum Mulai |

---

## Tahap 1 — Setup Dasar

| # | Task | Status | Keterangan |
|---|---|---|---|
| 1.1 | Instalasi Laravel | ✅ Selesai | Laravel 12 (PHP 8.2) |
| 1.2 | Konfigurasi `.env` (DB, timezone, app name) | ✅ Selesai | DB: `cuti_pa_makassar`, TZ: Asia/Makassar |
| 1.3 | Buat database MySQL | ✅ Selesai | `cuti_pa_makassar` charset utf8mb4 |
| 1.4 | Install Laravel Breeze (auth) + Tailwind CSS | ✅ Selesai | Breeze Blade stack |
| 1.5 | Buat migration: `roles` | ✅ Selesai | |
| 1.6 | Buat migration: `users` (tambah `role_id`, `status`) | ✅ Selesai | |
| 1.7 | Buat migration: `jabatan` | ✅ Selesai | |
| 1.8 | Buat migration: `unit_kerja` | ✅ Selesai | |
| 1.9 | Buat migration: `pegawai` | ✅ Selesai | |
| 1.10 | Jalankan semua migration | ✅ Selesai | |
| 1.11 | Buat Seeder: `roles` (pegawai, admin, superadmin) | ✅ Selesai | |
| 1.12 | Buat Seeder: user superadmin awal | ✅ Selesai | superadmin@pa-makassar.go.id |
| 1.13 | Buat Middleware: `RoleMiddleware` | ✅ Selesai | |
| 1.14 | Daftarkan middleware di `bootstrap/app.php` | ✅ Selesai | alias: `role` |
| 1.15 | Buat layout utama Blade dengan sidebar per role | ✅ Selesai | |
| 1.16 | Sesuaikan redirect login berdasarkan role | ✅ Selesai | cek status aktif/nonaktif |

---

## Tahap 2 — Data Master

| # | Task | Status | Keterangan |
|---|---|---|---|
| 2.1 | Model + Migration: `JenisCuti` | ✅ Selesai | |
| 2.2 | CRUD Jenis Cuti (Superadmin) | ⏳ Belum | Ditunda ke Tahap 3 |
| 2.3 | Model + Migration: `Jabatan` | ✅ Selesai | |
| 2.4 | CRUD Jabatan (Admin/Superadmin) | ✅ Selesai | |
| 2.5 | Model + Migration: `UnitKerja` | ✅ Selesai | |
| 2.6 | CRUD Unit Kerja (Admin/Superadmin) | ✅ Selesai | |
| 2.7 | Model + Migration: `Pegawai` | ✅ Selesai | |
| 2.8 | CRUD Pegawai (Admin/Superadmin) | ✅ Selesai | termasuk assign user |
| 2.9 | Model + Migration: `SaldoCuti` | ✅ Selesai | virtual col `sisa` |
| 2.10 | Inisialisasi saldo cuti pegawai baru (12 hari/tahun) | ✅ Selesai | auto saat tambah pegawai |
| 2.11 | Seeder: data awal jabatan & unit kerja | ✅ Selesai | 16 jabatan, 7 unit kerja |
| 2.12 | Seeder: jenis cuti (6 jenis) | ✅ Selesai | |
| 2.13 | Halaman daftar pegawai (tabel + filter) | ✅ Selesai | |
| 2.14 | Halaman detail pegawai | ✅ Selesai | |
| 2.15 | Halaman saldo cuti per pegawai | ✅ Selesai | index + edit |

---

## Tahap 3 — Pengajuan Cuti

| # | Task | Status | Keterangan |
|---|---|---|---|
| 3.1 | Model + Migration: `Cuti` | ⏳ Belum | |
| 3.2 | Model + Migration: `CutiSaldoDetail` | ⏳ Belum | FIFO tracking |
| 3.3 | Model + Migration: `DokumenCuti` | ⏳ Belum | lampiran |
| 3.4 | Form pengajuan cuti | ⏳ Belum | |
| 3.5 | Hitung otomatis jumlah hari dari tanggal | ⏳ Belum | |
| 3.6 | Validasi H-3 (cuti tahunan) | ⏳ Belum | |
| 3.7 | Validasi saldo mencukupi | ⏳ Belum | |
| 3.8 | Logika FIFO penggunaan saldo | ⏳ Belum | saldo terlama dulu |
| 3.9 | Upload lampiran (jika diperlukan) | ⏳ Belum | |
| 3.10 | Generate nomor pengajuan otomatis | ⏳ Belum | |
| 3.11 | Halaman riwayat cuti pegawai | ⏳ Belum | |
| 3.12 | Filter riwayat (tahun, jenis, status) | ⏳ Belum | |
| 3.13 | Halaman detail pengajuan | ⏳ Belum | |
| 3.14 | Pembatalan pengajuan oleh pegawai | ⏳ Belum | saldo dikembalikan |

---

## Tahap 4 — Persetujuan

| # | Task | Status | Keterangan |
|---|---|---|---|
| 4.1 | Model + Migration: `PersetujuanCuti` | ⏳ Belum | |
| 4.2 | Dashboard admin: daftar pengajuan masuk | ⏳ Belum | |
| 4.3 | Halaman detail pengajuan (view admin) | ⏳ Belum | |
| 4.4 | Aksi: setujui pengajuan | ⏳ Belum | kurangi saldo |
| 4.5 | Aksi: tolak pengajuan | ⏳ Belum | saldo tidak berkurang |
| 4.6 | Tambah catatan pada persetujuan | ⏳ Belum | |
| 4.7 | Update status pengajuan (flow 6 status) | ⏳ Belum | |
| 4.8 | Notifikasi status ke pegawai (in-app) | ⏳ Belum | opsional |

---

## Tahap 5 — Dokumen & Laporan

| # | Task | Status | Keterangan |
|---|---|---|---|
| 5.1 | Install DomPDF / Spatie/Browsershot | ⏳ Belum | untuk generate PDF |
| 5.2 | Template PDF formulir cuti | ⏳ Belum | sesuai format resmi |
| 5.3 | Generate & download PDF per pengajuan | ⏳ Belum | |
| 5.4 | Halaman laporan pengajuan (admin) | ⏳ Belum | |
| 5.5 | Filter laporan (tahun, bulan, jenis, status, pegawai) | ⏳ Belum | |
| 5.6 | Halaman laporan saldo cuti (admin) | ⏳ Belum | |
| 5.7 | Export laporan ke PDF | ⏳ Belum | |
| 5.8 | Print laporan dari browser | ⏳ Belum | |

---

## Tahap 6 — Pengujian

| # | Task | Status | Keterangan |
|---|---|---|---|
| 6.1 | Test: login semua role | ⏳ Belum | |
| 6.2 | Test: pengajuan cuti tahunan (normal) | ⏳ Belum | |
| 6.3 | Test: validasi H-3 | ⏳ Belum | |
| 6.4 | Test: saldo tidak cukup | ⏳ Belum | |
| 6.5 | Test: logika FIFO | ⏳ Belum | |
| 6.6 | Test: carry over maksimal 6 hari | ⏳ Belum | |
| 6.7 | Test: approval → saldo berkurang | ⏳ Belum | |
| 6.8 | Test: penolakan → saldo tidak berkurang | ⏳ Belum | |
| 6.9 | Test: pembatalan → saldo dikembalikan | ⏳ Belum | |
| 6.10 | Test: generate PDF | ⏳ Belum | |
| 6.11 | Test: akses berdasarkan role (middleware) | ⏳ Belum | |
| 6.12 | Test: responsive mobile | ⏳ Belum | |

---

## Catatan Teknis

### Stack
- **Framework:** Laravel 12
- **PHP:** 8.2.28
- **Database:** MySQL 8.0 (`cuti_pa_makassar`)
- **Frontend:** Blade + Tailwind CSS (via Vite)
- **Auth:** Laravel Breeze (Blade stack)

### Aturan Bisnis Penting
- Cuti tahunan: **12 hari/tahun**
- H-3: pengajuan minimal 3 hari sebelum mulai
- Carry over: maksimal **6 hari** ke tahun berikutnya
- FIFO: saldo tahun terlama dikonsumsi duluan
- Saldo dikurangi **setelah persetujuan final**
- Saldo dikembalikan jika pengajuan **dibatalkan setelah disetujui**

### Role
| Role | Slug | ID |
|---|---|---|
| Pegawai | `pegawai` | 1 |
| Admin | `admin` | 2 |
| Superadmin | `superadmin` | 3 |

### Akun Default Seeder
| Field | Value |
|---|---|
| Name | Superadmin |
| Email | superadmin@pa-makassar.go.id |
| Password | password (bcrypt) |
| Role | superadmin |

---

## Log Perubahan

| Tanggal | Aktivitas |
|---|---|
| 2026-09-02 | Inisialisasi proyek, install Laravel 12, konfigurasi .env, buat database MySQL |
| 2026-09-02 | Install Laravel Breeze + Tailwind CSS (Blade stack) |
| 2026-09-02 | Buat semua migration Tahap 1: roles, jabatan, unit_kerja, pegawai, users |
| 2026-09-02 | Buat Models: Role, Jabatan, UnitKerja, Pegawai, User (dengan helper role) |
| 2026-09-02 | Buat Seeder: RoleSeeder (3 role), SuperadminSeeder |
| 2026-09-02 | Buat RoleMiddleware, daftarkan di bootstrap/app.php sebagai alias `role` |
| 2026-09-02 | Buat layout utama (sidebar per role) + komponen sidebar-link |
| 2026-09-02 | Update Dashboard view sesuai role + validasi status aktif saat login |
| 2026-09-02 | **Tahap 1 selesai ✅** |
| 2026-09-02 | Tahap 2: Migration + Model jenis_cuti, saldo_cuti |
| 2026-09-02 | Tahap 2: Seeder JenisCuti (6), Jabatan (16), UnitKerja (7) |
| 2026-09-02 | Tahap 2: CRUD Jabatan, UnitKerja, Pegawai, SaldoCuti |
| 2026-09-02 | Tahap 2: Views semua modul data master |
| 2026-09-02 | Tahap 2: Routes web.php diperbarui (43 routes) |
| 2026-09-02 | **Tahap 2 selesai ✅** |

---

## Legenda Status
| Ikon | Arti |
|---|---|
| ✅ | Selesai |
| 🔄 | Sedang dikerjakan |
| ⏳ | Belum mulai |
| ❌ | Bermasalah / Dibatalkan |
| ⚠️ | Perlu perhatian khusus |
