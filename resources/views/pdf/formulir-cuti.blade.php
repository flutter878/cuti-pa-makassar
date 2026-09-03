<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Cuti — {{ $cuti->nomor_pengajuan }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; color: #000; }

        /* Header */
        .kop { text-align: center; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 14px; }
        .kop .instansi { font-size: 13pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .kop .alamat { font-size: 9pt; margin-top: 2px; }

        /* Judul */
        .judul { text-align: center; margin-bottom: 16px; }
        .judul h2 { font-size: 13pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; text-decoration: underline; }
        .judul .nomor { font-size: 10pt; margin-top: 3px; }

        /* Tabel data */
        .tabel-data { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .tabel-data td { padding: 4px 6px; vertical-align: top; font-size: 10.5pt; }
        .tabel-data td.label { width: 38%; }
        .tabel-data td.titik { width: 3%; text-align: center; }
        .tabel-data td.nilai { width: 59%; }

        /* Section header */
        .section-header { font-weight: bold; font-size: 10.5pt; margin: 10px 0 5px 0; text-decoration: underline; }

        /* Tabel persetujuan */
        .tabel-persetujuan { width: 100%; border-collapse: collapse; margin-top: 18px; }
        .tabel-persetujuan th, .tabel-persetujuan td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: center;
            font-size: 10pt;
        }
        .tabel-persetujuan th { background: #f0f0f0; font-weight: bold; }
        .tanda-tangan { height: 60px; }

        /* Status badge */
        .status-box { display: inline-block; border: 1.5px solid #000; padding: 2px 10px; font-weight: bold; font-size: 10.5pt; }

        /* Footer info */
        .footer-info { margin-top: 14px; font-size: 9pt; border-top: 1px solid #ccc; padding-top: 8px; color: #555; }

        /* Watermark jika ditolak/dibatalkan */
        .watermark {
            position: fixed;
            top: 35%;
            left: 15%;
            font-size: 72pt;
            color: rgba(200,0,0,0.10);
            font-weight: bold;
            transform: rotate(-30deg);
            text-transform: uppercase;
            z-index: -1;
        }
    </style>
</head>
<body>

@if(in_array($cuti->status, ['ditolak','dibatalkan']))
    <div class="watermark">{{ strtoupper($cuti->statusLabel()) }}</div>
@endif

{{-- KOP SURAT --}}
<div class="kop">
    <div class="instansi">Pengadilan Agama Makassar</div>
    <div class="alamat">Jl. Masjid Raya No.20, Makassar, Sulawesi Selatan 90111 | Telp. (0411) 3624951</div>
</div>

{{-- JUDUL --}}
<div class="judul">
    <h2>Formulir Permohonan Cuti</h2>
    <div class="nomor">Nomor: {{ $cuti->nomor_pengajuan }}</div>
</div>

{{-- DATA PEGAWAI --}}
<div class="section-header">I. Data Pegawai</div>
<table class="tabel-data">
    <tr>
        <td class="label">Nama Lengkap</td>
        <td class="titik">:</td>
        <td class="nilai">{{ $cuti->pegawai->nama }}</td>
    </tr>
    <tr>
        <td class="label">NIP</td>
        <td class="titik">:</td>
        <td class="nilai">{{ $cuti->pegawai->nip }}</td>
    </tr>
    <tr>
        <td class="label">Jabatan</td>
        <td class="titik">:</td>
        <td class="nilai">{{ $cuti->pegawai->jabatan?->nama_jabatan ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label">Unit Kerja</td>
        <td class="titik">:</td>
        <td class="nilai">{{ $cuti->pegawai->unitKerja?->nama_unit ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label">Atasan Langsung</td>
        <td class="titik">:</td>
        <td class="nilai">
            {{ $cuti->pegawai->atasanLangsung?->nama ?? '—' }}
            @if($cuti->pegawai->atasanLangsung?->jabatan)
                ({{ $cuti->pegawai->atasanLangsung->jabatan->nama_jabatan }})
            @endif
        </td>
    </tr>
</table>

{{-- DATA CUTI --}}
<div class="section-header">II. Data Permohonan Cuti</div>
<table class="tabel-data">
    <tr>
        <td class="label">Jenis Cuti</td>
        <td class="titik">:</td>
        <td class="nilai"><strong>{{ $cuti->jenisCuti->nama }}</strong></td>
    </tr>
    <tr>
        <td class="label">Tanggal Mulai</td>
        <td class="titik">:</td>
        <td class="nilai">{{ $cuti->tanggal_mulai->translatedFormat('d F Y') }}</td>
    </tr>
    <tr>
        <td class="label">Tanggal Selesai</td>
        <td class="titik">:</td>
        <td class="nilai">{{ $cuti->tanggal_selesai->translatedFormat('d F Y') }}</td>
    </tr>
    <tr>
        <td class="label">Lama Cuti</td>
        <td class="titik">:</td>
        <td class="nilai"><strong>{{ $cuti->jumlah_hari }} hari kerja</strong></td>
    </tr>
    <tr>
        <td class="label">Tanggal Pengajuan</td>
        <td class="titik">:</td>
        <td class="nilai">{{ $cuti->tanggal_pengajuan->translatedFormat('d F Y') }}</td>
    </tr>
    <tr>
        <td class="label">Alasan Cuti</td>
        <td class="titik">:</td>
        <td class="nilai">{{ $cuti->alasan }}</td>
    </tr>
    <tr>
        <td class="label">Alamat Selama Cuti</td>
        <td class="titik">:</td>
        <td class="nilai">{{ $cuti->alamat_cuti }}</td>
    </tr>
    @if($cuti->no_telepon)
    <tr>
        <td class="label">No. Telepon</td>
        <td class="titik">:</td>
        <td class="nilai">{{ $cuti->no_telepon }}</td>
    </tr>
    @endif
    <tr>
        <td class="label">Status Permohonan</td>
        <td class="titik">:</td>
        <td class="nilai">
            <span class="status-box">{{ strtoupper($cuti->statusLabel()) }}</span>
        </td>
    </tr>
</table>

{{-- TANDA TANGAN PERSETUJUAN --}}
<div class="section-header">III. Persetujuan</div>

@php
    $approvalAtasan = $cuti->persetujuan->where('level', 'atasan')->sortByDesc('tanggal_persetujuan')->first();
    $approvalKetua  = $cuti->persetujuan->where('level', 'ketua')->sortByDesc('tanggal_persetujuan')->first();
@endphp

<table class="tabel-persetujuan">
    <thead>
        <tr>
            <th style="width:33%">Pemohon</th>
            <th style="width:33%">
                Atasan Langsung<br>
                <span style="font-weight:normal;font-size:9pt">
                    ({{ $cuti->pegawai->atasanLangsung?->jabatan?->nama_jabatan ?? 'Panitera / Sekretaris' }})
                </span>
            </th>
            <th style="width:34%">Ketua<br><span style="font-weight:normal;font-size:9pt">Pengadilan Agama Makassar</span></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="tanda-tangan"></td>
            <td class="tanda-tangan">
                @if($approvalAtasan)
                    <div style="font-size:8.5pt;color:#555;text-align:center;margin-top:4px;">
                        {{ $approvalAtasan->statusLabel() }}<br>
                        {{ $approvalAtasan->tanggal_persetujuan?->format('d/m/Y') }}
                    </div>
                @endif
            </td>
            <td class="tanda-tangan">
                @if($approvalKetua)
                    <div style="font-size:8.5pt;color:#555;text-align:center;margin-top:4px;">
                        {{ $approvalKetua->statusLabel() }}<br>
                        {{ $approvalKetua->tanggal_persetujuan?->format('d/m/Y') }}
                    </div>
                @endif
            </td>
        </tr>
        <tr>
            <td style="padding-top:4px;">
                <strong>{{ $cuti->pegawai->nama }}</strong><br>
                <span style="font-size:9pt">NIP. {{ $cuti->pegawai->nip }}</span>
            </td>
            <td style="padding-top:4px;">
                <strong>{{ $cuti->pegawai->atasanLangsung?->nama ?? '...........................' }}</strong><br>
                <span style="font-size:9pt">
                    NIP. {{ $cuti->pegawai->atasanLangsung?->nip ?? '...........................' }}
                </span>
            </td>
            <td style="padding-top:4px;">
                @if($approvalKetua)
                    <strong>{{ $approvalKetua->user->name }}</strong><br>
                    <span style="font-size:9pt">NIP. {{ $approvalKetua->user->pegawai?->nip ?? '—' }}</span>
                @else
                    <strong>.................................</strong><br>
                    <span style="font-size:9pt">NIP. .................................</span>
                @endif
            </td>
        </tr>
    </tbody>
</table>

{{-- CATATAN PENOLAKAN --}}
@if($cuti->catatan && $cuti->status === 'ditolak')
    <div style="margin-top:12px;padding:8px 10px;border:1px solid #c00;background:#fff5f5;font-size:9.5pt;">
        <strong>Catatan Penolakan:</strong> {{ $cuti->catatan }}
    </div>
@endif

{{-- FOOTER --}}
<div class="footer-info">
    Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIT &nbsp;|&nbsp;
    Dokumen ini dicetak secara sistem &nbsp;|&nbsp;
    Sistem Informasi Cuti — Pengadilan Agama Makassar
</div>

</body>
</html>
