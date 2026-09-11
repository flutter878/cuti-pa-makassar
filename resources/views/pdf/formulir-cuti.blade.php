<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Cuti — {{ $cuti->nomor_pengajuan }}</title>
    <style>
        @page { size: A4 portrait; margin: 12mm 14mm 10mm 14mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 9pt;
            color: #000;
            padding: 0 10px;
        }

        /* ── Header kanan ── */
        .header-kanan {
            text-align: left;
            font-size: 9pt;
            line-height: 1.3;
            margin-bottom: 6px;
            margin-left: 60%;
        }

        /* ── Judul ── */
        .judul {
            text-align: center;
            margin-bottom: 5px;
        }
        .judul h2 {
            font-size: 10.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .judul .nomor {
            font-size: 9pt;
            margin-top: 2px;
        }

        /* ── Spacer antar section ── */
        .spacer {
            height: 8px;
            font-size: 1pt;
            line-height: 1pt;
        }

        /* ── Tabel utama ── */
        table.form {
            width: 100%;
            border-collapse: collapse;
        }

        table.form td {
            border: 1px solid #000;
            padding: 2px 5px;
            vertical-align: top;
            font-size: 9pt;
            line-height: 1.3;
        }

        /* Header section (bold, full-width) */
        td.sec {
            font-weight: bold;
            padding: 2px 5px;
            font-size: 9pt;
        }

        /* Centang — pakai DejaVu Sans yang punya glyph U+2713 */
        .centang {
            font-family: 'DejaVu Sans', sans-serif;
            font-weight: bold;
            font-size: 11pt;
        }

        /* ── Sub-tabel saldo cuti (Section V kiri) ── */
        table.saldo {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
        }
        table.saldo td {
            border: none;
            padding: 1px 2px;
        }
        table.saldo td.br {
            border: 1px solid #000;
            padding: 1px 4px;
        }

        /* ── Footer ── */
        .footer {
            font-size: 7.5pt;
            color: #555;
            text-align: right;
            margin-top: 3px;
        }
    </style>
</head>
<body>

@php
    use Carbon\Carbon;
    $kota     = 'Makassar';
    $tglCetak = Carbon::now('Asia/Makassar');

    // Saldo cuti tahunan
    $saldoList = collect();
    if ($cuti->jenisCuti?->kode === 'CT') {
        $saldoList = $cuti->pegawai->saldoCuti()
            ->where('tahun', '<=', $tglCetak->year)
            ->orderBy('tahun')
            ->get();
    }

    $kodeAmbil  = $cuti->jenisCuti?->kode ?? '';
    $masaKerja  = $cuti->masa_kerja ?? '—';
    $nomorSurat = $cuti->nomor_surat ?? $cuti->nomor_pengajuan;

    // ── Sistem approval ──
    $approvalStages = $cuti->approvalStages ?? collect();
    $usesNewSystem  = $approvalStages->count() > 0;

    if ($usesNewSystem) {
        $stageKetua     = $approvalStages->where('jenis_tindakan', 'final_approval')->sortBy('urutan')->first();
        $stageAtasan    = $approvalStages->where('jenis_tindakan', '!=', 'final_approval')->sortBy('urutan')->first();

        $atasanJabatan   = $stageAtasan?->jabatan_approver ?? 'Sekretaris';
        $atasanNama      = $stageAtasan?->nama_approver ?? '';
        $atasanNip       = $stageAtasan?->pegawai?->nip ?? '';
        $atasanTgl       = $stageAtasan?->tanggal_tindakan?->format('d/m/Y') ?? '';
        $atasanDisetujui = (bool)($stageAtasan?->isApproved());
        $atasanDitolak   = (bool)($stageAtasan?->isRejected());
        $atasanCatatan   = $atasanDitolak ? ($stageAtasan?->catatan ?? '') : '';

        $ketuaJabatan    = 'Ketua';
        $ketuaNama       = $stageKetua?->nama_approver ?? '';
        $ketuaNip        = $stageKetua?->pegawai?->nip ?? '';
        $ketuaTgl        = $stageKetua?->tanggal_tindakan?->format('d/m/Y') ?? '';
        $ketuaDisetujui  = (bool)($stageKetua?->isApproved());
        $ketuaDitolak    = (bool)($stageKetua?->isRejected());
        $ketuaCatatan    = $ketuaDitolak ? ($stageKetua?->catatan ?? '') : '';
    } else {
        $persetujuan    = $cuti->persetujuan ?? collect();
        $approvalAtasan = $persetujuan->where('level', 'atasan')->sortByDesc('tanggal_persetujuan')->first();
        $approvalKetua  = $persetujuan->where('level', 'ketua')->sortByDesc('tanggal_persetujuan')->first();
        $atasanPegawai  = $cuti->pegawai->atasanLangsung ?? null;
        $ketuaPegawai   = $approvalKetua?->user?->pegawai ?? null;

        $atasanJabatan   = $atasanPegawai?->jabatan?->nama_jabatan ?? 'Sekretaris';
        $atasanNama      = $atasanPegawai?->nama ?? '';
        $atasanNip       = $atasanPegawai?->nip ?? '';
        $atasanTgl       = $approvalAtasan?->tanggal_persetujuan?->format('d/m/Y') ?? '';
        $atasanDisetujui = $approvalAtasan?->status === 'disetujui';
        $atasanDitolak   = $approvalAtasan?->status === 'ditolak';
        $atasanCatatan   = $atasanDitolak ? ($approvalAtasan?->catatan ?? '') : '';

        $ketuaJabatan    = 'Ketua';
        $ketuaNama       = $ketuaPegawai?->nama ?? '';
        $ketuaNip        = $ketuaPegawai?->nip ?? '';
        $ketuaTgl        = $approvalKetua?->tanggal_persetujuan?->format('d/m/Y') ?? '';
        $ketuaDisetujui  = $approvalKetua?->status === 'disetujui';
        $ketuaDitolak    = $approvalKetua?->status === 'ditolak';
        $ketuaCatatan    = $ketuaDitolak ? ($approvalKetua?->catatan ?? '') : '';
    }
@endphp

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- HEADER KANAN                                                      --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<div class="header-kanan">
    {{ $kota }},&nbsp;&nbsp; {{ $tglCetak->translatedFormat('d F Y') }}<br>
    Kepada Yth.<br>
    Ketua Pengadilan Agama Makassar<br>
    di-<br>
    Tempat
</div>

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- JUDUL                                                             --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<div class="judul">
    <h2>Formulir Permintaan dan Pemberian Cuti</h2>
    <div class="nomor">Nomor :&nbsp;&nbsp;&nbsp; {{ $nomorSurat }}</div>
</div>

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- I. DATA PEGAWAI                                                   --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<table class="form">
    <tr>
        <td colspan="4" class="sec">I. DATA PEGAWAI</td>
    </tr>
    <tr>
        <td style="width:14%">Nama</td>
        <td style="width:36%">{{ $cuti->pegawai?->nama ?? '—' }}</td>
        <td style="width:10%">NIP</td>
        <td style="width:40%">{{ $cuti->pegawai?->nip ?? '—' }}</td>
    </tr>
    <tr>
        <td>Jabatan</td>
        <td>{{ $cuti->pegawai?->jabatan?->nama_jabatan ?? '—' }}</td>
        <td>Masa Kerja</td>
        <td>{{ $masaKerja }}</td>
    </tr>
    <tr>
        <td>Unit Kerja</td>
        <td colspan="3">{{ $cuti->pegawai?->unitKerja?->nama_unit ?? '—' }}</td>
    </tr>
</table>

<div class="spacer">&nbsp;</div>

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- II. JENIS CUTI YANG DIAMBIL                                       --}}
{{-- 4 kolom: label | box centang | label | box centang               --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<table class="form">
    <tr>
        <td colspan="4" class="sec">II. JENIS CUTI YANG DIAMBIL</td>
    </tr>
    <tr>
        <td style="width:38%; padding:3px 5px;">1. Cuti Tahunan</td>
        <td style="width:12%; text-align:center; padding:3px 4px;">
            @if($kodeAmbil === 'CT') <span class="centang">&#10003;</span> @endif
        </td>
        <td style="width:38%; padding:3px 5px;">2. Cuti Besar</td>
        <td style="width:12%; text-align:center; padding:3px 4px;">
            @if($kodeAmbil === 'CB') <span class="centang">&#10003;</span> @endif
        </td>
    </tr>
    <tr>
        <td style="padding:3px 5px;">3. Cuti Sakit</td>
        <td style="text-align:center; padding:3px 4px;">
            @if($kodeAmbil === 'CS') <span class="centang">&#10003;</span> @endif
        </td>
        <td style="padding:3px 5px;">4. Cuti Melahirkan</td>
        <td style="text-align:center; padding:3px 4px;">
            @if($kodeAmbil === 'CM') <span class="centang">&#10003;</span> @endif
        </td>
    </tr>
    <tr>
        <td style="padding:3px 5px;">5. Cuti Alasan Penting</td>
        <td style="text-align:center; padding:3px 4px;">
            @if($kodeAmbil === 'CAP') <span class="centang">&#10003;</span> @endif
        </td>
        <td style="padding:3px 5px;">6. Cuti diluar Tanggungan Negara</td>
        <td style="text-align:center; padding:3px 4px;">
            @if($kodeAmbil === 'CLTN') <span class="centang">&#10003;</span> @endif
        </td>
    </tr>
</table>

<div class="spacer">&nbsp;</div>

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- III. ALASAN CUTI                                                  --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<table class="form">
    <tr>
        <td colspan="4" class="sec">III. ALASAN CUTI</td>
    </tr>
    <tr>
        <td colspan="4" style="min-height:28px; padding:3px 5px;">
            {{ $cuti->alasan }}
        </td>
    </tr>
</table>

<div class="spacer">&nbsp;</div>

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- IV. LAMANYA CUTI                                                  --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<table class="form">
    <tr>
        <td colspan="7" class="sec">IV. LAMANYA CUTI</td>
    </tr>
    <tr>
        <td style="width:12%; padding:3px 5px;">Selama</td>
        <td style="width:8%; text-align:center; padding:3px 4px;"><strong>{{ $cuti->jumlah_hari }}</strong></td>
        <td style="width:18%; text-align:center; padding:3px 4px;">(hari/<del>bulan</del>/<del>tahun</del>)</td>
        <td style="width:16%; text-align:center; padding:3px 4px;">Mulai Tanggal</td>
        <td style="width:18%; text-align:center; padding:3px 4px;"><strong>{{ $cuti->tanggal_mulai->translatedFormat('d F Y') }}</strong></td>
        <td style="width:6%; text-align:center; padding:3px 4px;">s.d.</td>
        <td style="padding:3px 4px; text-align:center;"><strong>{{ $cuti->tanggal_selesai->translatedFormat('d F Y') }}</strong></td>
    </tr>
</table>

<div class="spacer">&nbsp;</div>

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- V. CATATAN CUTI                                                   --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<table class="form">
    <tr>
        <td colspan="6" class="sec">V. CATATAN CUTI</td>
    </tr>
    <tr>
        {{-- ── Kolom kiri: judul "1. Cuti Tahunan" + Paraf ── --}}
        <td colspan="3" style="padding:2px 5px; font-weight:bold;">1. Cuti Tahunan</td>
        <td style="text-align:center; font-weight:bold; font-size:8pt; padding:2px 4px; vertical-align:middle; white-space:nowrap;">
            Paraf Petugas Cuti
        </td>
        {{-- ── Kolom kanan: 2. Cuti Besar ── --}}
        <td style="padding:2px 5px;">2. Cuti Besar</td>
        <td style="padding:2px 5px;">&nbsp;</td>
    </tr>
    {{-- Header tabel saldo + baris kanan --}}
    <tr>
        <td style="width:10%; text-align:center; font-size:9pt; padding:2px 3px; font-weight:bold;">Tahun</td>
        <td style="width:8%; text-align:center; font-size:9pt; padding:2px 3px; font-weight:bold;">Sisa</td>
        <td style="width:18%; text-align:center; font-size:9pt; padding:2px 3px; font-weight:bold;">Keterangan</td>
        <td style="width:10%; text-align:center; padding:2px 4px;" rowspan="4">&nbsp;</td>
        <td style="padding:2px 5px;">3. Cuti Sakit</td>
        <td style="padding:2px 5px;">&nbsp;</td>
    </tr>
    {{-- Baris data saldo (selalu 3 baris) --}}
    @php $rows = $saldoList->take(3); $cnt = $rows->count(); @endphp
    @foreach($rows as $i => $s)
    <tr>
        <td style="text-align:center; font-size:9pt; padding:2px 3px;">{{ $s->tahun }}</td>
        <td style="text-align:center; font-size:9pt; padding:2px 3px;">{{ $s->sisa }}</td>
        <td style="text-align:center; font-size:9pt; padding:2px 3px;">{{ $s->sisa == 0 ? 'Habis' : '' }}</td>
        @if($i === 0)
        <td style="padding:2px 5px;">4. Cuti Melahirkan</td>
        <td style="padding:2px 5px;">&nbsp;</td>
        @elseif($i === 1)
        <td style="padding:2px 5px;">5. Cuti Karena Alasan Penting</td>
        <td style="padding:2px 5px;">&nbsp;</td>
        @else
        <td style="padding:2px 5px;">6. Cuti diluar Tanggungan Negara</td>
        <td style="padding:2px 5px;">&nbsp;</td>
        @endif
    </tr>
    @endforeach
    @for($r = $cnt; $r < 3; $r++)
    <tr>
        <td style="text-align:center; font-size:9pt; padding:2px 3px;">&nbsp;</td>
        <td style="text-align:center; font-size:9pt; padding:2px 3px;">&nbsp;</td>
        <td style="text-align:center; font-size:9pt; padding:2px 3px;">&nbsp;</td>
        @if($r === 0)
        <td style="padding:2px 5px;">4. Cuti Melahirkan</td>
        <td style="padding:2px 5px;">&nbsp;</td>
        @elseif($r === 1)
        <td style="padding:2px 5px;">5. Cuti Karena Alasan Penting</td>
        <td style="padding:2px 5px;">&nbsp;</td>
        @else
        <td style="padding:2px 5px;">6. Cuti diluar Tanggungan Negara</td>
        <td style="padding:2px 5px;">&nbsp;</td>
        @endif
    </tr>
    @endfor
</table>

<div class="spacer">&nbsp;</div>

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- VI. ALAMAT SELAMA MENJALANKAN CUTI                               --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<table class="form">
    <tr>
        <td colspan="3" class="sec">VI. ALAMAT SELAMA MENJALANKAN CUTI</td>
    </tr>
    <tr>
        <td style="width:50%; vertical-align:top; padding:3px 5px;" rowspan="2">
            {{ $cuti->alamat_cuti ?? '' }}
        </td>
        <td style="width:20%; font-weight:bold; padding:2px 5px;">TELPON/HP</td>
        <td style="width:30%; padding:2px 5px;">{{ $cuti->no_telepon ?? '' }}</td>
    </tr>
    <tr>
        <td colspan="2" style="vertical-align:bottom; padding:3px 6px;">
            Hormat Saya,<br>
            <br><br>
            <strong>{{ $cuti->pegawai?->nama ?? '—' }}</strong><br>
            NIP. &nbsp; {{ $cuti->pegawai?->nip ?? '—' }}
        </td>
    </tr>
</table>

<div class="spacer">&nbsp;</div>

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- VII. PERTIMBANGAN ATASAN LANGSUNG                                 --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<table class="form">
    <tr>
        <td colspan="4" class="sec">VII. PERTIMBANGAN ATASAN LANGSUNG</td>
    </tr>
    <tr>
        <td style="text-align:center; font-weight:bold; padding:2px 4px; width:18%;">DISETUJUI</td>
        <td style="text-align:center; font-weight:bold; padding:2px 4px; width:18%;">PERUBAHAN</td>
        <td style="text-align:center; font-weight:bold; padding:2px 4px; width:18%;">DITANGGUHKAN</td>
        <td style="text-align:center; font-weight:bold; padding:2px 4px;">TIDAK DISETUJUI</td>
    </tr>
    <tr>
        <td style="text-align:center; vertical-align:top; padding:2px 3px;">
            @if($atasanDisetujui) <span class="centang">&#10003;</span> @endif
        </td>
        <td style="text-align:center; vertical-align:top; padding:2px 3px;">&nbsp;</td>
        <td style="text-align:center; vertical-align:top; padding:2px 3px;">&nbsp;</td>
        <td style="text-align:center; vertical-align:top; padding:2px 3px;">
            @if($atasanDitolak) <span class="centang">&#10003;</span> @endif
        </td>
    </tr>
    <tr>
        <td colspan="3" style="border:none; padding:0;">&nbsp;</td>
        <td style="vertical-align:top; padding:3px 5px; height:70px;">
            @if($atasanCatatan) <span style="font-size:8pt;">{{ $atasanCatatan }}</span><br> @endif
            {{ $atasanJabatan }}<br>
            <br><br>
            {{ $atasanNama }}<br>
            NIP. &nbsp; {{ $atasanNip }}
            @if($atasanTgl) <br><span style="font-size:8pt;">{{ $atasanTgl }}</span> @endif
        </td>
    </tr>
</table>

<div class="spacer">&nbsp;</div>

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI           --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<table class="form">
    <tr>
        <td colspan="4" class="sec">VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI</td>
    </tr>
    <tr>
        <td style="text-align:center; font-weight:bold; padding:2px 4px; width:18%;">DISETUJUI</td>
        <td style="text-align:center; font-weight:bold; padding:2px 4px; width:18%;">PERUBAHAN</td>
        <td style="text-align:center; font-weight:bold; padding:2px 4px; width:18%;">DITANGGUHKAN</td>
        <td style="text-align:center; font-weight:bold; padding:2px 4px;">TIDAK DISETUJUI</td>
    </tr>
    <tr>
        <td style="text-align:center; vertical-align:top; padding:2px 3px;">
            @if($ketuaDisetujui) <span class="centang">&#10003;</span> @endif
        </td>
        <td style="text-align:center; vertical-align:top; padding:2px 3px;">&nbsp;</td>
        <td style="text-align:center; vertical-align:top; padding:2px 3px;">&nbsp;</td>
        <td style="text-align:center; vertical-align:top; padding:2px 3px;">
            @if($ketuaDitolak) <span class="centang">&#10003;</span> @endif
        </td>
    </tr>
    <tr>
        <td colspan="3" style="border:none; padding:0;">&nbsp;</td>
        <td style="vertical-align:top; padding:3px 5px; height:70px;">
            @if($ketuaCatatan) <span style="font-size:8pt;">{{ $ketuaCatatan }}</span><br> @endif
            {{ $ketuaJabatan }}<br>
            <br><br>
            {{ $ketuaNama }}<br>
            NIP. &nbsp; {{ $ketuaNip }}
            @if($ketuaTgl) <br><span style="font-size:8pt;">{{ $ketuaTgl }}</span> @endif
        </td>
    </tr>
</table>



</body>
</html>
