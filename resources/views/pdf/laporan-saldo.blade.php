<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Saldo Cuti {{ $tahun }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 9pt; color: #000; }

        .kop { text-align: center; border-bottom: 2px solid #000; padding-bottom: 6px; margin-bottom: 10px; }
        .kop .instansi { font-size: 12pt; font-weight: bold; text-transform: uppercase; }
        .kop .alamat { font-size: 8pt; margin-top: 2px; }

        .judul { text-align: center; margin-bottom: 8px; }
        .judul h2 { font-size: 11pt; font-weight: bold; text-transform: uppercase; }
        .judul .sub { font-size: 9pt; margin-top: 2px; color: #333; }

        .meta { margin-bottom: 8px; font-size: 8.5pt; }

        table { width: 100%; border-collapse: collapse; }
        th { background: #1e3a5f; color: #fff; padding: 5px 6px; text-align: center; font-size: 8.5pt; }
        th.left { text-align: left; }
        td { padding: 4px 6px; border-bottom: 1px solid #ddd; font-size: 8.5pt; vertical-align: middle; }
        tr:nth-child(even) td { background: #f8f9fa; }

        .num { text-align: center; }
        .sisa-ok   { color: #065f46; font-weight: bold; }
        .sisa-warn { color: #92400e; font-weight: bold; }
        .sisa-zero { color: #991b1b; font-weight: bold; }

        .footer { margin-top: 10px; font-size: 7.5pt; color: #666; border-top: 1px solid #ccc; padding-top: 6px; }
    </style>
</head>
<body>

<div class="kop">
    <div class="instansi">Pengadilan Agama Makassar</div>
    <div class="alamat">Jl. Masjid Raya No.20, Makassar, Sulawesi Selatan | Telp. (0411) 3624951</div>
</div>

<div class="judul">
    <h2>Laporan Saldo Cuti Pegawai</h2>
    <div class="sub">Filter: {{ $filter }}</div>
</div>

<div class="meta">
    Total pegawai aktif: <strong>{{ $data->count() }}</strong> &nbsp;|&nbsp;
    Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIT
</div>

<table>
    <thead>
        <tr>
            <th class="left" style="width:4%">No</th>
            <th class="left" style="width:24%">Nama Pegawai</th>
            <th class="left" style="width:16%">NIP</th>
            <th class="left" style="width:20%">Jabatan</th>
            <th class="left" style="width:15%">Unit Kerja</th>
            <th style="width:7%">Hak</th>
            <th style="width:7%">Carry Over</th>
            <th style="width:7%">Terpakai</th>
            <th style="width:7%">Sisa</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $i => $pegawai)
            @php $saldo = $pegawai->saldoCuti->first(); @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $pegawai->nama }}</td>
                <td style="font-family:monospace;font-size:8pt">{{ $pegawai->nip }}</td>
                <td>{{ $pegawai->jabatan?->nama_jabatan ?? '—' }}</td>
                <td>{{ $pegawai->unitKerja?->nama_unit ?? '—' }}</td>
                @if($saldo)
                    <td class="num">{{ $saldo->hak_cuti }}</td>
                    <td class="num">{{ $saldo->carry_over }}</td>
                    <td class="num">{{ $saldo->terpakai }}</td>
                    <td class="num {{ $saldo->sisa >= 8 ? 'sisa-ok' : ($saldo->sisa >= 4 ? 'sisa-warn' : 'sisa-zero') }}">
                        {{ $saldo->sisa }}
                    </td>
                @else
                    <td class="num" colspan="4" style="color:#999;text-align:center">Belum ada saldo</td>
                @endif
            </tr>
        @empty
            <tr><td colspan="9" style="text-align:center;padding:12px;color:#999">Tidak ada data</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    Laporan ini dicetak secara sistem &nbsp;|&nbsp; Sistem Informasi Cuti — Pengadilan Agama Makassar
</div>

</body>
</html>
