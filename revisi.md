from pathlib import Path

content = """# PROMPT REVISI SISTEM PENGAJUAN CUTI

Saya sedang mengembangkan aplikasi **Sistem Informasi Pengajuan Cuti Pegawai** berbasis **Laravel**. Saya ingin melakukan revisi pada alur, validasi, perhitungan hari cuti, nomor surat, serta formulir hasil pengajuan.

> **PENTING:** Jangan mengubah fitur yang sudah berjalan dan jangan merusak struktur aplikasi yang ada. Sebelum melakukan perubahan, pahami terlebih dahulu struktur project, database, model, controller, route, middleware, view/Blade, dan logic pengajuan cuti yang sudah tersedia.

## 1. Nomor Surat

Format:

`[NOMOR INPUT ADMIN]/KPA/SKET.KP4.3/[BULAN]/[TAHUN]`

Contoh:

`444/KPA/SKET.KP4.3/IX/2026`

Ketentuan:
- Nomor awal, misalnya `444`, diinput manual oleh **Admin**.
- `KPA/SKET.KP4.3` bersifat tetap.
- Bulan otomatis mengikuti bulan saat pengajuan dibuat.
- Tahun otomatis mengikuti tahun saat pengajuan dibuat.
- Pegawai tidak boleh menginput atau mengubah nomor surat.
- Admin mengisi nomor surat saat melakukan verifikasi.
- Nomor surat disimpan di database dan ditampilkan pada formulir/cetak surat.
- Gunakan angka Romawi untuk bulan: I, II, III, IV, V, VI, VII, VIII, IX, X, XI, XII.

## 2. Tanggal Pengajuan

Tanggal pengajuan otomatis menggunakan tanggal ketika pegawai membuat pengajuan.

Contoh:
- Pengajuan dibuat `04 September 2026`
- Tanggal Pengajuan = `04 September 2026`

Pegawai tidak perlu menginput tanggal pengajuan secara manual. Gunakan tanggal server/database sebagai sumber utama.

## 3. Masa/Tahun Kerja

Field **Masa/Tahun Kerja** hanya boleh diisi atau diubah oleh **Admin**.

Pegawai tidak boleh menentukan atau mengubah nilai tersebut saat mengajukan cuti.

Alur:
`Pegawai mengajukan → Admin memeriksa → Admin mengisi masa/tahun kerja → Admin memverifikasi`

Pastikan authorization diterapkan agar pegawai tidak dapat mengubahnya melalui request/API secara manual.

## 4. Alur Pengajuan

Alur utama:

**PEGAWAI → ADMIN → ATASAN/KEPALA BIDANG → KETUA**

### Tahap 1 — Pegawai
Pegawai mengisi:
- Jenis cuti
- Tanggal mulai
- Tanggal selesai
- Alasan/keterangan
- Data lain yang memang sudah tersedia

Setelah klik **Ajukan Cuti**, status:

`menunggu_verifikasi_admin`

Pengajuan belum boleh masuk ke Atasan.

### Tahap 2 — Admin
Admin melihat pengajuan `menunggu_verifikasi_admin`.

Admin wajib:
1. Memeriksa pengajuan.
2. Mengisi nomor surat.
3. Mengisi masa/tahun kerja.
4. Melakukan verifikasi.

Jika berhasil:
`menunggu_persetujuan_atasan`

Jika belum diverifikasi, pengajuan tidak boleh diproses Atasan.

### Tahap 3 — Atasan/Kepala Bidang

Atasan hanya dapat memproses pengajuan yang sudah diverifikasi Admin.

Pilihan:
- Setujui
- Tolak

Jika ditolak:
`ditolak`

Proses berhenti dan tidak masuk Ketua.

Jika disetujui:
`menunggu_persetujuan_ketua`

### Tahap 4 — Ketua

Ketua memproses pengajuan yang sudah disetujui Atasan.

Pilihan:
- Setujui
- Tolak

Jika ditolak:
`ditolak`

Jika disetujui:
`disetujui`

## 5. Checkbox pada Formulir

**PENTING:** Checkbox bukan input manual.

Pada formulir/cetak surat terdapat kotak keputusan, misalnya:

`☐ DISETUJUI`  
`☐ DITOLAK`

Checkbox harus menjadi **indikator otomatis berdasarkan status persetujuan yang tersimpan di database**.

Jika status = disetujui:

`☑ DISETUJUI`  
`☐ DITOLAK`

Jika status = ditolak:

`☐ DISETUJUI`  
`☑ DITOLAK`

Jangan membuat checkbox yang dapat diklik/diedit pada formulir hasil cetak.

Gunakan simbol `☑` dan `☐`, atau mekanisme render yang setara, berdasarkan status database.

## 6. Checkbox Setiap Tahap Persetujuan

Formulir harus dapat menunjukkan keputusan setiap pejabat.

### Persetujuan Atasan

Jika disetujui:

`☑ DISETUJUI`  
`☐ DITOLAK`

Jika ditolak:

`☐ DISETUJUI`  
`☑ DITOLAK`

Tampilkan:
- Nama Atasan
- NIP jika tersedia
- Jabatan
- Tanggal persetujuan

### Persetujuan Ketua

Jika disetujui:

`☑ DISETUJUI`  
`☐ DITOLAK`

Jika ditolak:

`☐ DISETUJUI`  
`☑ DITOLAK`

Tampilkan:
- Nama Ketua
- NIP jika tersedia
- Jabatan
- Tanggal persetujuan

Jika Atasan menolak, Ketua tidak boleh memproses pengajuan tersebut.

## 7. Perhitungan Hari Cuti

Sistem hanya menghitung **hari kerja**.

Tidak dihitung:
- Sabtu
- Minggu
- Hari libur nasional
- Cuti bersama jika terdaftar sebagai hari libur sistem

Contoh:

Jika periode Senin–Minggu dan tidak ada hari libur:
- Senin = 1
- Selasa = 1
- Rabu = 1
- Kamis = 1
- Jumat = 1
- Sabtu = 0
- Minggu = 0

Total = **5 hari kerja**

Perhitungan harus dilakukan di backend, bukan hanya JavaScript frontend.

## 8. Sistem Hari Libur

Jika project sudah memiliki tabel/mekanisme hari libur, gunakan yang sudah ada.

Jika belum tersedia, buat mekanisme yang memungkinkan Admin mengelola:
- Tanggal libur
- Nama hari libur
- Keterangan
- Status aktif

Tanggal yang terdaftar sebagai hari libur tidak boleh dihitung sebagai hari cuti.

## 9. Batas Pengajuan Cuti

Pegawai tidak boleh mengajukan cuti terlalu jauh ke depan.

Batas:
**Maksimal 30 hari kalender dari tanggal pengajuan.**

Contoh:
- Tanggal pengajuan: `04 September 2026`
- Maksimal tanggal yang dapat dipilih: `04 Oktober 2026`

Aturan backend:

```text
tanggal_mulai >= tanggal_pengajuan
tanggal_mulai <= tanggal_pengajuan + 30 hari
