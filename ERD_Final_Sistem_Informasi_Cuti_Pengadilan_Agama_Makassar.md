# PRD — Sistem Informasi Pengajuan Cuti
## Pengadilan Agama Makassar

### 1. Informasi Produk
- **Nama:** Sistem Informasi Pengajuan Cuti
- **Instansi:** Pengadilan Agama Makassar
- **Platform:** Web
- **Backend:** Laravel
- **Database:** MySQL
- **Target perangkat:** Desktop dan Mobile
- **Bahasa:** Indonesia

### 2. Tujuan Sistem
Sistem dibuat untuk mempermudah pegawai mengajukan cuti secara online serta membantu admin mengelola pengajuan, saldo cuti, persetujuan, dan laporan.

Prinsip utama: **sederhana, mudah dipahami, dan tidak terlalu banyak fitur yang tidak diperlukan.**

Tujuan:
1. Pengajuan cuti online.
2. Melihat saldo cuti.
3. Melihat status dan riwayat pengajuan.
4. Admin memproses pengajuan.
5. Perhitungan saldo otomatis.
6. Penerapan aturan H-3 dan carry-over.
7. Pembuatan dokumen cuti PDF.

### 3. Pengguna dan Hak Akses

#### Pegawai
- Login
- Dashboard
- Melihat saldo cuti
- Mengajukan cuti
- Melihat riwayat dan detail pengajuan
- Melihat status
- Mengunduh dokumen
- Mengubah profil tertentu

#### Admin
- Dashboard
- Mengelola pegawai
- Melihat dan memproses pengajuan
- Menyetujui/menolak pengajuan
- Mengelola saldo cuti
- Melihat laporan
- Mencetak/mengunduh dokumen

#### Superadmin
Seluruh hak Admin, ditambah:
- Mengelola akun pengguna
- Mengatur role
- Mengelola jenis cuti
- Mengatur sistem

### 4. Fitur Utama

#### Login
- Username/email
- Password

#### Dashboard Pegawai
Menampilkan:
- Nama pegawai
- Sisa cuti tahun berjalan
- Sisa cuti tahun sebelumnya
- Jumlah pengajuan
- Status pengajuan terakhir
- Tombol **Ajukan Cuti**

#### Pengajuan Cuti
Field:
- Jenis cuti
- Tanggal mulai
- Tanggal selesai
- Jumlah hari
- Alasan
- Alamat selama cuti
- Nomor telepon
- Lampiran jika diperlukan

Jumlah hari dihitung otomatis berdasarkan tanggal.

### 5. Aturan Bisnis

#### Cuti Tahunan
Setiap pegawai memperoleh **12 hari cuti tahunan setiap tahun**.

#### H-3
Cuti tahunan harus diajukan minimal **H-3** sebelum tanggal mulai.

Contoh: jika cuti dimulai tanggal 20, pengajuan paling lambat tanggal 17.

#### Carry Over
Sisa cuti dapat dibawa ke tahun berikutnya dengan maksimal **6 hari**.

Contoh: sisa 8 hari → yang dibawa hanya 6 hari.

#### Masa Berlaku
Saldo cuti dapat digunakan sampai dengan **dua tahun sebelumnya** sesuai kebijakan sistem. Saldo yang telah melewati masa berlaku tidak dapat digunakan.

#### FIFO
Saldo terlama digunakan terlebih dahulu.

Contoh:
- Saldo 2025 = 4 hari
- Saldo 2026 = 6 hari
- Pengajuan = 5 hari

Maka sistem menggunakan 4 hari dari 2025 dan 1 hari dari 2026.

#### Pengurangan Saldo
Saldo baru dikurangi setelah persetujuan final.
- Ditolak → saldo tidak berkurang.
- Disetujui → saldo berkurang.
- Dibatalkan → saldo dikembalikan jika sebelumnya telah dikurangi.

### 6. Status Pengajuan
1. Diajukan
2. Diproses
3. Menunggu Persetujuan
4. Disetujui
5. Ditolak
6. Dibatalkan

### 7. Riwayat Cuti
Informasi:
- Nomor pengajuan
- Jenis cuti
- Tanggal mulai
- Tanggal selesai
- Jumlah hari
- Status
- Tanggal pengajuan

Filter:
- Tahun
- Jenis cuti
- Status

### 8. Detail Pengajuan
Menampilkan:
- Nomor pengajuan
- Identitas pegawai
- Jabatan
- Unit kerja
- Jenis cuti
- Tanggal dan jumlah hari
- Alasan
- Alamat selama cuti
- Nomor telepon
- Lampiran
- Status
- Catatan
- Riwayat persetujuan

### 9. Data Pegawai
Field utama:
- NIP
- Nama
- Email
- Nomor telepon
- Jabatan
- Unit kerja
- Status
- Username
- Role

Admin dapat menambah, mengubah, menonaktifkan, dan melihat pegawai.

### 10. Jenis Cuti
Jenis yang disediakan:
1. Cuti Tahunan
2. Cuti Besar
3. Cuti Sakit
4. Cuti Melahirkan
5. Cuti Karena Alasan Penting
6. Cuti di Luar Tanggungan Negara

Setiap jenis dapat memiliki aturan:
- Membutuhkan lampiran
- Batas hari
- Mengurangi saldo
- Jenis persetujuan

### 11. Saldo Cuti
Saldo disimpan per pegawai dan tahun.

| Tahun | Hak | Carry Over | Terpakai | Sisa |
|---|---:|---:|---:|---:|
| 2026 | 12 | 4 | 5 | 11 |
| 2027 | 12 | 6 | 2 | 16 |

Rumus:
`Sisa = Hak + Carry Over - Terpakai`

### 12. Persetujuan
Admin dapat:
- Menyetujui
- Menolak
- Memberi catatan

Data persetujuan:
- Pengajuan
- User
- Status
- Catatan
- Tanggal

### 13. Dokumen PDF
Sistem menghasilkan **Formulir Permintaan dan Pemberian Cuti** dalam format PDF.

Minimal berisi:
- Identitas pegawai
- NIP
- Jabatan
- Unit kerja
- Jenis dan lama cuti
- Tanggal cuti
- Alasan
- Alamat selama cuti
- Persetujuan
- Tanggal
- Tanda tangan

### 14. Laporan
#### Laporan Pengajuan
Filter:
- Tahun
- Bulan
- Jenis cuti
- Status
- Pegawai

#### Laporan Saldo
Menampilkan:
- Pegawai
- Hak cuti
- Carry over
- Terpakai
- Sisa

Output:
- Tampilan sistem
- Cetak
- PDF

### 15. Struktur Database

#### `users`
`id, role_id, pegawai_id, name, email, password, status, timestamps`

#### `roles`
`id, name, timestamps`

#### `pegawai`
`id, nip, nama, email, no_telepon, jabatan_id, unit_kerja_id, status, timestamps`

#### `jabatan`
`id, nama_jabatan, timestamps`

#### `unit_kerja`
`id, nama_unit, timestamps`

#### `jenis_cuti`
`id, nama, kode, membutuhkan_lampiran, mengurangi_saldo, status, timestamps`

#### `saldo_cuti`
`id, pegawai_id, tahun, hak_cuti, carry_over, terpakai, sisa, timestamps`

Unique: `pegawai_id + tahun`

#### `cuti`
`id, nomor_pengajuan, pegawai_id, jenis_cuti_id, tanggal_mulai, tanggal_selesai, jumlah_hari, alasan, alamat_cuti, no_telepon, status, catatan, tanggal_pengajuan, timestamps`

#### `cuti_saldo_detail`
`id, cuti_id, saldo_cuti_id, jumlah_digunakan, timestamps`

Digunakan untuk mencatat saldo tahun yang dikonsumsi dengan FIFO.

#### `persetujuan_cuti`
`id, cuti_id, user_id, status, catatan, tanggal_persetujuan, timestamps`

#### `dokumen_cuti`
`id, cuti_id, nama_file, file_path, tipe_file, timestamps`

#### `activity_logs`
`id, user_id, aktivitas, keterangan, ip_address, timestamps`

### 16. Model Laravel
```text
User
Role
Pegawai
Jabatan
UnitKerja
JenisCuti
SaldoCuti
Cuti
CutiSaldoDetail
PersetujuanCuti
DokumenCuti
ActivityLog
```

### 17. Controller Laravel
```text
DashboardController
CutiController
SaldoCutiController
PegawaiController
JenisCutiController
PersetujuanCutiController
LaporanController
DokumenCutiController
```

### 18. Struktur Menu

#### Pegawai
```text
Dashboard
├── Ajukan Cuti
├── Riwayat Cuti
└── Profil
```

#### Admin
```text
Dashboard
├── Pegawai
├── Pengajuan Cuti
├── Saldo Cuti
└── Laporan
```

#### Superadmin
```text
Dashboard
├── Pegawai
├── Pengajuan Cuti
├── Saldo Cuti
├── Jenis Cuti
├── Laporan
├── Pengguna
└── Pengaturan
```

### 19. Validasi
Sebelum menyimpan:
- Jenis cuti wajib dipilih.
- Tanggal wajib diisi.
- Tanggal selesai tidak boleh sebelum tanggal mulai.
- Jumlah hari valid.
- Alasan wajib diisi.
- Cuti tahunan memenuhi H-3.
- Saldo mencukupi.
- Saldo lama digunakan terlebih dahulu.
- Lampiran wajib jika jenis cuti memerlukannya.

### 20. Alur Pengajuan
```text
Pegawai Login
      ↓
Dashboard
      ↓
Ajukan Cuti
      ↓
Isi Form
      ↓
Validasi Sistem
      ↓
Status: Diajukan
      ↓
Admin Memproses
      ↓
Persetujuan
   ↙       ↘
Setuju     Tolak
  ↓          ↓
Kurangi     Tidak
Saldo       Kurangi Saldo
  ↓
Dokumen PDF
```

### 21. UX/UI
Prinsip:
- Sederhana
- Bersih
- Formal
- Mudah digunakan
- Responsive
- Mobile friendly
- Tidak terlalu banyak grafik
- Informasi penting mudah ditemukan

Warna yang disarankan:
- Putih
- Navy/blue
- Gold sebagai aksen seperlunya
- Abu-abu untuk informasi sekunder

Komponen utama:
- Card saldo
- Card pengajuan
- Status pengajuan
- Tombol Ajukan Cuti
- Form sederhana
- Date picker
- Dropdown
- Upload dokumen

### 22. Keamanan
- Laravel Authentication
- Password hashing
- Role & permission
- CSRF protection
- Validasi input
- Validasi upload
- Session management
- Activity log
- Pembatasan akses berdasarkan role

### 23. MVP

#### Pegawai
- Login
- Dashboard
- Ajukan cuti
- Saldo cuti
- Riwayat
- Detail
- Profil

#### Admin
- Dashboard
- Pegawai
- Pengajuan
- Persetujuan
- Saldo
- Laporan
- PDF

#### Sistem
- H-3
- 12 hari/tahun
- Carry over maksimal 6 hari
- FIFO
- Status pengajuan
- Pengurangan saldo setelah persetujuan
- PDF

### 24. Tahapan Pengembangan

#### Tahap 1 — Dasar
- Instalasi Laravel
- Konfigurasi MySQL
- Authentication
- Role
- Database

#### Tahap 2 — Data Master
- Pegawai
- Jabatan
- Unit kerja
- Jenis cuti
- Saldo

#### Tahap 3 — Pengajuan
- Form
- Validasi H-3
- Validasi saldo
- Upload
- Riwayat

#### Tahap 4 — Persetujuan
- Dashboard admin
- Detail
- Approve
- Reject
- Catatan

#### Tahap 5 — Dokumen dan Laporan
- PDF
- Laporan cuti
- Laporan saldo
- Filter

#### Tahap 6 — Pengujian
- Login
- Pengajuan
- H-3
- Saldo
- FIFO
- Carry over
- Approval
- PDF
- Role access

### 25. Kesimpulan
Sistem Informasi Pengajuan Cuti Pengadilan Agama Makassar merupakan aplikasi web sederhana berbasis **Laravel dan MySQL**.

Fokus utama:
1. Pengajuan cuti online.
2. Saldo cuti otomatis.
3. Aturan H-3.
4. Hak cuti tahunan 12 hari.
5. Carry over maksimal 6 hari.
6. FIFO saldo.
7. Persetujuan sederhana.
8. Riwayat cuti.
9. Dokumen PDF.
10. Laporan admin.

Prioritas tahap awal adalah **mudah digunakan pegawai, mudah dikelola admin, dan aturan cuti berjalan otomatis serta konsisten**.
