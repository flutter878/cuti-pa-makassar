<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Cuti — {{ $cuti->nomor_pengajuan }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; color: #000; }

        .header-kanan { text-align: right; font-size: 10pt; margin-bottom: 10px; line-height: 1.6; }

        .judul { text-align: center; margin-bottom: 6px; }
        .judul h2 { font-size: 11.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .judul .nomor { font-size: 10pt; margin-top: 2px; }

        .tabel-utama { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .tabel-utama td, .tabel-utama th {
            border: 1px solid #000; padding: 3px 5px;
            vertical-align: top; font-size: 10pt;
        }
        .tabel-utama th { background: #fff; font-weight: bold; text-align: left; }
        .section-title { font-weight: bold; background: #fff; }

        .cb-table { width: 100%; border-collapse: collapse; }
        .cb-table td { border: none; padding: 1px 4px; font-size: 10pt; vertical-align: middle; }
        .cb { font-size: 12pt; vertical-align: middle; margin-right: 3px; }

        .ttd-cell { height: 70px; vertical-align: top; padding: 4px 6px !important; }
        .ttd-label { font-size: 9pt; text-align: center; margin-bottom: 40px; }
        .ttd-nama { font-weight: bold; font-size: 9.5pt; }
        .ttd-nip { font-size: 9pt; }
    </style>
</head>
<body>

@php
    use Carbon\Carbon;
    $kota     = 'Makassar';
    $tglCetak = Carbon::now('Asia/Makassar');

    // Saldo cuti tahunan (kode CT)
    $saldoList = [];
    if ($cuti->jenisCuti?->kode === 'CT') {
        $saldoList = $cuti->pegawai->saldoCuti()
            ->where('tahun', '<=', $tglCetak->year)
            ->orderBy('tahun')
            ->get();
    }

    $kodeAmbil  = $cuti->jenisCuti?->kode ?? '';
    $masaKerja  = $cuti->masa_kerja ?? '—';
    $nomorSurat = $cuti->nomor_surat ?? $cuti->nomor_pengajuan;

    // ── Deteksi sistem approval yang digunakan ──────────────
    $approvalStages = $cuti->approvalStages ?? collect();
    $usesNewSystem  = $approvalStages->count() > 0;

    if ($usesNewSystem) {
        // Sistem baru: approval_stages
        //
        // Section VII (Pertimbangan Atasan Langsung):
        //   = stage PERTAMA (urutan terkecil) — atasan langsung / kasubbag
        //
        // Section VIII (Keputusan Pejabat Berwenang / Ketua):
        //   = stage final_approval
        //
        // Jika hanya ada 1 stage (langsung final), section VII kosong

        $stageKetua     = $approvalStages->where('jenis_tindakan', 'final_approval')
                                         ->sortBy('urutan')->first();

        $stagesPreFinal = $approvalStages->where('jenis_tindakan', '!=', 'final_approval');

        // Section VII = atasan langsung = stage urutan pertama (bukan final)
        $stageAtasan    = $stagesPreFinal->sortBy('urutan')->first();

        // Backward compat variables
        $approvalAtasan  = null;
        $approvalKetua   = null;
        $atasanPegawai   = $stageAtasan?->pegawai;
        $ketuaPegawai    = $stageKetua?->pegawai;

    } else {
        // Sistem lama: persetujuan_cuti
        $persetujuan    = $cuti->persetujuan ?? collect();
        $approvalAtasan = $persetujuan->where('level', 'atasan')->sortByDesc('tanggal_persetujuan')->first();
        $approvalKetua  = $persetujuan->where('level', 'ketua')->sortByDesc('tanggal_persetujuan')->first();
        $atasanPegawai  = $cuti->pegawai->atasanLangsung ?? null;
        $ketuaPegawai   = $approvalKetua?->user?->pegawai ?? null;
        $stageAtasan    = null;
        $stageKetua     = null;
    }
@endphp

{{-- ── HEADER KANAN ── --}}
<div class="header-kanan">
    {{ $kota }},&nbsp;&nbsp; {{ $tglCetak->translatedFormat('d F Y') }}<br>
    Kepada Yth.<br>
    Ketua Pengadilan Agama Makassar<br>
    di-<br>
    Tempat
</div>

{{-- ── JUDUL ── --}}
<div class="judul">
    <h2>Formulir Permintaan dan Pemberian Cuti</h2>
    <div class="nomor">Nomor :&nbsp; {{ $nomorSurat }}</div>
</div>

{{-- ── TABEL UTAMA ── --}}
<table class="tabel-utama">

    {{-- I. DATA PEGAWAI --}}
    <tr>
        <td colspan="4" class="section-title">I. DATA PEGAWAI</td>
    </tr>
    <tr>
        <td style="width:15%">Nama</td>
        <td style="width:35%">{{ $cuti->pegawai?->nama ?? '—' }}</td>
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

    {{-- II. JENIS CUTI --}}
    <tr>
        <td colspan="4" class="section-title">II. JENIS CUTI YANG DIAMBIL</td>
    </tr>
    <tr>
        <td colspan="2">
            <span class="cb">{{ $kodeAmbil === 'CT' ? '☑' : '☐' }}</span> 1. Cuti Tahunan
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="cb">{{ $kodeAmbil === 'CB' ? '☑' : '☐' }}</span> 2. Cuti Besar
        </td>
        <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2">
            <span class="cb">{{ $kodeAmbil === 'CS' ? '☑' : '☐' }}</span> 3. Cuti Sakit
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="cb">{{ $kodeAmbil === 'CM' ? '☑' : '☐' }}</span> 4. Cuti Melahirkan
        </td>
        <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2">
            <span class="cb">{{ $kodeAmbil === 'CAP' ? '☑' : '☐' }}</span> 5. Cuti Alasan Penting
            &nbsp;&nbsp;&nbsp;&nbsp;
            <span class="cb">{{ $kodeAmbil === 'CDT' ? '☑' : '☐' }}</span> 6. Cuti diluar Tanggungan Negara
        </td>
        <td colspan="2">&nbsp;</td>
    </tr>

    {{-- III. ALASAN --}}
    <tr>
        <td colspan="4" class="section-title">III. ALASAN CUTI</td>
    </tr>
    <tr>
        <td colspan="4" style="min-height:30px; padding: 4px 6px;">{{ $cuti->alasan }}</td>
    </tr>

    {{-- IV. LAMA CUTI --}}
    <tr>
        <td colspan="4" class="section-title">IV. LAMANYA CUTI</td>
    </tr>
    <tr>
        <td colspan="4" style="padding: 4px 6px;">
            Selama &nbsp;&nbsp;
            <strong>{{ $cuti->jumlah_hari }}</strong>
            &nbsp;&nbsp; (hari/<del>bulan</del>/<del>tahun</del>) &nbsp;&nbsp;
            Mulai Tanggal &nbsp;&nbsp;
            <strong>{{ $cuti->tanggal_mulai->translatedFormat('d F Y') }}</strong>
            &nbsp;&nbsp; s.d. &nbsp;&nbsp;
            <strong>{{ $cuti->tanggal_selesai->translatedFormat('d F Y') }}</strong>
        </td>
    </tr>

    {{-- V. CATATAN CUTI --}}
    <tr>
        <td colspan="4" class="section-title">V. CATATAN CUTI</td>
    </tr>
    <tr>
        <td colspan="2" style="vertical-align:top; padding: 3px 5px;">
            <table style="width:100%; border-collapse: collapse; font-size:9.5pt;">
                <tr>
                    <td style="border:none; font-weight:bold; padding:1px 3px;" colspan="4">1. Cuti Tahunan</td>
                    <td style="border:none; text-align:center; font-weight:bold; padding:1px 3px; font-size:9pt;">Paraf<br>Petugas Cuti</td>
                </tr>
                <tr>
                    <td style="border:1px solid #000; padding:2px 4px; text-align:center; font-size:8.5pt; width:18%">Tahun</td>
                    <td style="border:1px solid #000; padding:2px 4px; text-align:center; font-size:8.5pt; width:18%">Sisa</td>
                    <td style="border:1px solid #000; padding:2px 4px; text-align:center; font-size:8.5pt; width:40%">Keterangan</td>
                    <td style="border:none; width:4%"></td>
                    <td style="border:1px solid #000; padding:2px 4px; text-align:center; font-size:8.5pt; width:20%" rowspan="5">&nbsp;</td>
                </tr>
                @forelse($saldoList->take(3) as $saldo)
                    <tr>
                        <td style="border:1px solid #000; padding:2px 4px; text-align:center; font-size:8.5pt;">{{ $saldo->tahun }}</td>
                        <td style="border:1px solid #000; padding:2px 4px; text-align:center; font-size:8.5pt;">{{ $saldo->sisa }}</td>
                        <td style="border:1px solid #000; padding:2px 4px; font-size:8.5pt;">
                            {{ $saldo->sisa == 0 ? 'Habis' : ($saldo->sisa < 3 ? 'Hampir Habis' : '') }}
                        </td>
                        <td style="border:none;"></td>
                    </tr>
                @empty
                    @for($r = 0; $r < 3; $r++)
                        <tr>
                            <td style="border:1px solid #000; padding:2px 4px; text-align:center; font-size:8.5pt;">&nbsp;</td>
                            <td style="border:1px solid #000; padding:2px 4px; text-align:center; font-size:8.5pt;">&nbsp;</td>
                            <td style="border:1px solid #000; padding:2px 4px; font-size:8.5pt;">&nbsp;</td>
                            <td style="border:none;"></td>
                        </tr>
                    @endfor
                @endforelse
            </table>
        </td>
        <td colspan="2" style="vertical-align:top; padding: 3px 5px; font-size:9.5pt; line-height:1.8;">
            2. Cuti Besar<br>
            3. Cuti Sakit<br>
            4. Cuti Melahirkan<br>
            5. Cuti Karena Alasan Penting<br>
            6. Cuti diluar Tanggungan Negara
        </td>
    </tr>

    {{-- VI. ALAMAT --}}
    <tr>
        <td colspan="4" class="section-title">VI. ALAMAT SELAMA MENJALANKAN CUTI</td>
    </tr>
    <tr>
        <td colspan="2" style="vertical-align:top; min-height:80px; padding:4px 6px;">
            {{ $cuti->alamat_cuti ?? '—' }}
        </td>
        <td colspan="2" style="vertical-align:top; padding:4px 6px;">
            <strong>TELPON/HP</strong> &nbsp; {{ $cuti->no_telepon ?? '—' }}<br><br>
            Hormat Saya,<br><br><br><br>
            <strong>{{ $cuti->pegawai?->nama ?? '—' }}</strong><br>
            NIP. &nbsp; {{ $cuti->pegawai?->nip ?? '—' }}
        </td>
    </tr>

    {{-- VII. PERTIMBANGAN ATASAN / PEJABAT --}}
    <tr>
        <td colspan="4" class="section-title">VII. PERTIMBANGAN ATASAN LANGSUNG</td>
    </tr>
    <tr>
        {{-- Status --}}
        <td style="text-align:center; width:18%; vertical-align:top; padding:4px;">
            <strong>DISETUJUI</strong><br>
            @if($usesNewSystem)
                <span style="font-size:16pt;">
                    {{ ($stageAtasan && $stageAtasan->isApproved()) ? '☑' : '☐' }}
                </span>
            @else
                <span style="font-size:16pt;">
                    {{ ($approvalAtasan && $approvalAtasan->status === 'disetujui') ? '☑' : '☐' }}
                </span>
            @endif
        </td>
        <td style="text-align:center; width:18%; vertical-align:top; padding:4px;">
            <strong>PERUBAHAN</strong><br><span style="font-size:16pt;">☐</span>
        </td>
        <td style="text-align:center; width:18%; vertical-align:top; padding:4px;">
            <strong>DITANGGUHKAN</strong><br><span style="font-size:16pt;">☐</span>
        </td>
        {{-- Tanda tangan area kosong --}}
        <td style="vertical-align:top; padding:4px 6px;" rowspan="2">
            @if($usesNewSystem)
                {{ $stageAtasan?->jabatan_approver ?? '.......................................' }}<br>
            @else
                {{ $atasanPegawai?->jabatan?->nama_jabatan ?? 'Panitera / Sekretaris' }}<br>
            @endif
            <br><br><br>
            {{-- Area TTD kosong untuk tanda tangan manual --}}
            @if($usesNewSystem)
                <strong>{{ $stageAtasan?->nama_approver ?? '.......................................' }}</strong><br>
                NIP. &nbsp; {{ $stageAtasan?->pegawai?->nip ?? '.......................................' }}
                @if($stageAtasan?->tanggal_tindakan)
                    <br><span style="font-size:8pt;color:#555;">{{ $stageAtasan->tanggal_tindakan->format('d/m/Y') }}</span>
                @endif
            @else
                <strong>{{ $atasanPegawai?->nama ?? '.......................................' }}</strong><br>
                NIP. &nbsp; {{ $atasanPegawai?->nip ?? '.......................................' }}
                @if($approvalAtasan?->tanggal_persetujuan)
                    <br><span style="font-size:8pt;color:#555;">{{ $approvalAtasan->tanggal_persetujuan->format('d/m/Y') }}</span>
                @endif
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="3" style="text-align:center; vertical-align:top; padding:4px;">
            <strong>TIDAK DISETUJUI</strong><br>
            @if($usesNewSystem)
                <span style="font-size:16pt;">
                    {{ ($stageAtasan && $stageAtasan->isRejected()) ? '☑' : '☐' }}
                </span>
                @if($stageAtasan && $stageAtasan->isRejected() && $stageAtasan->catatan)
                    <br><span style="font-size:8.5pt;">{{ $stageAtasan->catatan }}</span>
                @endif
            @else
                <span style="font-size:16pt;">
                    {{ ($approvalAtasan && $approvalAtasan->status === 'ditolak') ? '☑' : '☐' }}
                </span>
                @if($approvalAtasan?->status === 'ditolak' && $approvalAtasan->catatan)
                    <br><span style="font-size:8.5pt;">{{ $approvalAtasan->catatan }}</span>
                @endif
            @endif
        </td>
    </tr>

    {{-- VIII. KEPUTUSAN PEJABAT BERWENANG (KETUA) --}}
    <tr>
        <td colspan="4" class="section-title">VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI</td>
    </tr>
    <tr>
        <td style="text-align:center; vertical-align:top; padding:4px;">
            <strong>DISETUJUI</strong><br>
            @if($usesNewSystem)
                <span style="font-size:16pt;">
                    {{ ($stageKetua && $stageKetua->isApproved()) ? '☑' : '☐' }}
                </span>
            @else
                <span style="font-size:16pt;">
                    {{ ($approvalKetua && $approvalKetua->status === 'disetujui') ? '☑' : '☐' }}
                </span>
            @endif
        </td>
        <td style="text-align:center; vertical-align:top; padding:4px;">
            <strong>PERUBAHAN</strong><br><span style="font-size:16pt;">☐</span>
        </td>
        <td style="text-align:center; vertical-align:top; padding:4px;">
            <strong>DITANGGUHKAN</strong><br><span style="font-size:16pt;">☐</span>
        </td>
        {{-- Area TTD Ketua kosong --}}
        <td style="vertical-align:top; padding:4px 6px;" rowspan="2">
            Ketua<br><br><br><br>
            {{-- Area kosong untuk tanda tangan manual --}}
            @if($usesNewSystem)
                <strong>{{ $stageKetua?->nama_approver ?? '.......................................' }}</strong><br>
                NIP. &nbsp; {{ $stageKetua?->pegawai?->nip ?? '.......................................' }}
                @if($stageKetua?->tanggal_tindakan)
                    <br><span style="font-size:8pt;color:#555;">{{ $stageKetua->tanggal_tindakan->format('d/m/Y') }}</span>
                @endif
            @else
                <strong>{{ $ketuaPegawai?->nama ?? '.......................................' }}</strong><br>
                NIP. &nbsp; {{ $ketuaPegawai?->nip ?? '.......................................' }}
                @if($approvalKetua?->tanggal_persetujuan)
                    <br><span style="font-size:8pt;color:#555;">{{ $approvalKetua->tanggal_persetujuan->format('d/m/Y') }}</span>
                @endif
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="3" style="text-align:center; vertical-align:top; padding:4px;">
            <strong>TIDAK DISETUJUI</strong><br>
            @if($usesNewSystem)
                <span style="font-size:16pt;">
                    {{ ($stageKetua && $stageKetua->isRejected()) ? '☑' : '☐' }}
                </span>
                @if($stageKetua && $stageKetua->isRejected() && $stageKetua->catatan)
                    <br><span style="font-size:8.5pt;">{{ $stageKetua->catatan }}</span>
                @endif
            @else
                <span style="font-size:16pt;">
                    {{ ($approvalKetua && $approvalKetua->status === 'ditolak') ? '☑' : '☐' }}
                </span>
                @if($approvalKetua?->status === 'ditolak' && $approvalKetua->catatan)
                    <br><span style="font-size:8.5pt;">{{ $approvalKetua->catatan }}</span>
                @endif
            @endif
        </td>
    </tr>

</table>

<div style="font-size: 8pt; color:#555; text-align:right; margin-top:4px;">
    Dicetak: {{ $tglCetak->format('d/m/Y H:i') }} WIT &nbsp;|&nbsp; Sistem Informasi Cuti — Pengadilan Agama Makassar
</div>

</body>
</html>
