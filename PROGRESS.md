# PROGRESS — Sistem Informasi Cuti PA Makassar

> Terakhir diperbarui: 2026-09-03

---

## Status Keseluruhan

| Tahap | Deskripsi | Status |
|---|---|---|
| Tahap 1 | Setup Dasar (Laravel, DB, Auth, Role, Migration) | ✅ Selesai |
| Tahap 2 | Data Master (Pegawai, Jabatan, Unit Kerja, Jenis Cuti, Saldo) | ✅ Selesai |
| Tahap 3 | Pengajuan Cuti (Form, Validasi H-3, FIFO, Upload) | ✅ Selesai |
| Tahap 4 | Persetujuan (2 Level: Atasan → Ketua) | ✅ Selesai |
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
| 2.2 | CRUD Jenis Cuti (Superadmin) | ✅ Selesai | Diselesaikan di Tahap 4 |
| 2.3 | Model + Migration: `Jabatan` | ✅ Selesai | |
| 2.4 | CRUD Jabatan (Admin/Superadmin) | ✅ Selesai | |
| 2.5 | Model + Migration: `UnitKerja` | ✅ Selesai | |
| 2.6 | CRUD Unit Kerja (Admin/Superadmin) | ✅ Selesai | |
| 2.7 | Model + Migration: `Pegawai` | ✅ Selesai | |
| 2.8 | CRUD Pegawai (Admin/Superadmin) | ✅ Selesai | termasuk assign user + atasan langsung |
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
| 3.1 | Model + Migration: `Cuti` | ✅ Selesai | |
| 3.2 | Model + Migration: `CutiSaldoDetail` | ✅ Selesai | FIFO tracking |
| 3.3 | Model + Migration: `DokumenCuti` | ✅ Selesai | lampiran |
| 3.4 | Form pengajuan cuti | ✅ Selesai | |
| 3.5 | Hitung otomatis jumlah hari dari tanggal | ✅ Selesai | JS + service |
| 3.6 | Validasi H-3 (cuti tahunan) | ✅ Selesai | di CutiService |
| 3.7 | Validasi saldo mencukupi | ✅ Selesai | di CutiService |
| 3.8 | Logika FIFO penggunaan saldo | ✅ Selesai | saldo terlama dulu |
| 3.9 | Upload lampiran (jika diperlukan) | ✅ Selesai | storage/public |
| 3.10 | Generate nomor pengajuan otomatis | ✅ Selesai | CUT/YYYY/MM/XXXX |
| 3.11 | Halaman riwayat cuti pegawai | ✅ Selesai | |
| 3.12 | Filter riwayat (tahun, jenis, status) | ✅ Selesai | |
| 3.13 | Halaman detail pengajuan | ✅ Selesai | |
| 3.14 | Pembatalan pengajuan oleh pegawai | ✅ Selesai | saldo dikembalikan |

---

## Tahap 4 — Persetujuan

| # | Task | Status | Keterangan |
|---|---|---|---|
| 4.1 | Model + Migration: `PersetujuanCuti` | ✅ Selesai | kolom `level` (atasan/ketua) ditambahkan |
| 4.2 | Alur persetujuan 2 level | ✅ Selesai | Atasan Langsung → Ketua |
| 4.3 | Field atasan langsung di data pegawai | ✅ Selesai | `atasan_langsung_id` FK nullable |
| 4.4 | Config jabatan approver | ✅ Selesai | `config/approver.php` |
| 4.5 | Aksi: setujui oleh atasan langsung | ✅ Selesai | teruskan ke Ketua |
| 4.6 | Aksi: tolak oleh atasan langsung | ✅ Selesai | wajib isi catatan |
| 4.7 | Aksi: setujui final oleh Ketua | ✅ Selesai | saldo dikurangi saat ini |
| 4.8 | Aksi: tolak oleh Ketua | ✅ Selesai | saldo tidak berkurang |
| 4.9 | Validasi jabatan approver | ✅ Selesai | cek jabatan + relasi atasan langsung |
| 4.10 | Dashboard Admin dengan data real | ✅ Selesai | statistik, pengajuan terbaru, rekap status |
| 4.11 | CRUD Jenis Cuti (Superadmin) | ✅ Selesai | task 2.2 yang tertunda |
| 4.12 | Manajemen Pengguna (Admin/Superadmin) | ✅ Selesai | daftar, tambah, edit, toggle status |
| 4.13 | Sidebar dinamis untuk approver | ✅ Selesai | menu muncul otomatis untuk Panitera/Sekretaris/Ketua |
| 4.14 | Notifikasi in-app ke pegawai | ⏳ Belum | opsional |

---

## Tahap 5 — Dokumen & Laporan

| # | Task | Status | Keterangan |
|---|---|---|---|
| 5.1 | Install DomPDF | ✅ Selesai | `barryvdh/laravel-dompdf` sudah di vendor |
| 5.2 | Template PDF formulir cuti | ⏳ Belum | sesuai format resmi PA Makassar |
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
| 6.7 | Test: approval atasan → lanjut ke Ketua | ⏳ Belum | |
| 6.8 | Test: approval Ketua → saldo berkurang | ⏳ Belum | |
| 6.9 | Test: penolakan atasan/ketua → saldo tidak berkurang | ⏳ Belum | |
| 6.10 | Test: pembatalan → saldo dikembalikan | ⏳ Belum | |
| 6.11 | Test: validasi jabatan approver | ⏳ Belum | |
| 6.12 | Test: generate PDF | ⏳ Belum | |
| 6.13 | Test: akses berdasarkan role (middleware) | ⏳ Belum | |
| 6.14 | Test: responsive mobile | ⏳ Belum | |

---

## Catatan Teknis

### Stack
- **Framework:** Laravel 12
- **PHP:** 8.2.28
- **Database:** MySQL 8.0 (`cuti_pa_makassar`)
- **Frontend:** Blade + Tailwind CSS (via Vite)
- **Auth:** Laravel Breeze (Blade stack)
- **PDF:** barryvdh/laravel-dompdf (sudah terinstall)

### Aturan Bisnis Penting
- Cuti tahunan: **12 hari/tahun**
- H-3: pengajuan minimal 3 hari sebelum mulai (hanya Cuti Tahunan, kode CT)
- Carry over: maksimal **6 hari** ke tahun berikutnya
- FIFO: saldo tahun terlama dikonsumsi duluan
- Saldo dikurangi **setelah persetujuan final Ketua**
- Saldo dikembalikan jika pengajuan **dibatalkan setelah disetujui**

### Alur Persetujuan Cuti
```
Pegawai ajukan cuti
        ↓
Status: menunggu_atasan
        ↓
Atasan Langsung (Panitera / Sekretaris)
  → Setujui → status: menunggu_ketua
  → Tolak   → status: ditolak (selesai)
        ↓
Status: menunggu_ketua
        ↓
Ketua
  → Setujui → status: disetujui + saldo dikurangi
  → Tolak   → status: ditolak (selesai)
```
- Jika pegawai tidak punya atasan langsung → langsung ke `menunggu_ketua`
- Jabatan approver dikonfigurasi di `config/approver.php`

### Status Cuti
| Status | Keterangan |
|---|---|
| `menunggu_atasan` | Menunggu persetujuan Panitera/Sekretaris |
| `menunggu_ketua` | Menunggu persetujuan final Ketua |
| `disetujui` | Disetujui Ketua, saldo sudah dikurangi |
| `ditolak` | Ditolak (oleh atasan atau Ketua) |
| `dibatalkan` | Dibatalkan oleh pegawai |

### Role
| Role | Slug | ID | Keterangan |
|---|---|---|---|
| Pegawai | `pegawai` | 1 | Semua pegawai, jabatan menentukan hak approve |
| Admin | `admin` | 2 | Kelola data, monitor persetujuan |
| Superadmin | `superadmin` | 3 | Akses penuh termasuk pengaturan sistem |

### Jabatan Approver (config/approver.php)
| Jabatan | Level | Keterangan |
|---|---|---|
| Panitera | Atasan (Level 1) | Approver tahap pertama |
| Sekretaris | Atasan (Level 1) | Approver tahap pertama |
| Ketua | Ketua (Level 2/Final) | Approver final, saldo dikurangi setelah ini |

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
| 2026-09-02 | Tahap 3: Migration + Model cuti, cuti_saldo_detail, dokumen_cuti, persetujuan_cuti |
| 2026-09-02 | Tahap 3: CutiService — H-3, FIFO, nomor pengajuan, batalkan, kurangi/kembalikan saldo |
| 2026-09-02 | Tahap 3: CutiController — create, store, index, show, destroy, download |
| 2026-09-02 | Tahap 3: Views cuti — create, index, show |
| 2026-09-02 | Tahap 3: Routes cuti pegawai ditambahkan |
| 2026-09-02 | **Tahap 3 selesai ✅** |
| 2026-09-03 | Tahap 4: DashboardController + data real (statistik, saldo, pengajuan terbaru) |
| 2026-09-03 | Tahap 4: CRUD Jenis Cuti (Superadmin) |
| 2026-09-03 | Tahap 4: Manajemen Pengguna — daftar, tambah, edit, toggle status |
| 2026-09-03 | Tahap 4: Update sidebar + routes (DashboardController, PenggunaController, JenisCutiController) |
| 2026-09-03 | Tahap 4: Refactor alur persetujuan → 2 level (Atasan Langsung + Ketua) |
| 2026-09-03 | Tahap 4: Migration `atasan_langsung_id` di pegawai + `level` di persetujuan_cuti |
| 2026-09-03 | Tahap 4: Update enum status cuti: menunggu_atasan, menunggu_ketua, disetujui, ditolak, dibatalkan |
| 2026-09-03 | Tahap 4: config/approver.php — Panitera & Sekretaris (atasan), Ketua (final) |
| 2026-09-03 | Tahap 4: PersetujuanCutiController — 4 method: approveAtasan, rejectAtasan, approveKetua, rejectKetua |
| 2026-09-03 | Tahap 4: Views persetujuan/show — progress bar + panel tindakan per level |
| 2026-09-03 | Tahap 4: Views persetujuan/index — filter + kolom atasan langsung |
| 2026-09-03 | Tahap 4: Sidebar — menu "Pengajuan Masuk" muncul otomatis untuk Panitera/Sekretaris/Ketua |
| 2026-09-03 | Tahap 4: UserFactory diperbaiki (bug FK role_id) |
| 2026-09-03 | **Tahap 4 selesai ✅** |

---

## Legenda Status
| Ikon | Arti |
|---|---|
| ✅ | Selesai |
| 🔄 | Sedang dikerjakan |
| ⏳ | Belum mulai |
| ❌ | Bermasalah / Dibatalkan |
| ⚠️ | Perlu perhatian khusus |
