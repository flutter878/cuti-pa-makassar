<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengajuan Cuti</title>
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
        .meta span { margin-right: 16px; }

        table { width: 100%; border-collapse: collapse; }
        th { background: #1e3a5f; color: #fff; padding: 5px 6px; text-align: left; font-size: 8.5pt; }
        td { padding: 4px 6px; border-bottom: 1px solid #ddd; font-size: 8.5pt; vertical-align: top; }
        tr:nth-child(even) td { background: #f8f9fa; }

        .badge { display: inline-block; padding: 1px 6px; border-radius: 3px; font-size: 7.5pt; font-weight: bold; }
        .badge-green   { background: #d1fae5; color: #065f46; }
        .badge-red     { background: #fee2e2; color: #991b1b; }
        .badge-yellow  { background: #fef3c7; color: #92400e; }
        .badge-orange  { background: #ffedd5; color: #9a3412; }
        .badge-gray    { background: #f3f4f6; color: #374151; }

        .footer { margin-top: 10px; font-size: 7.5pt; color: #666; border-top: 1px solid #ccc; padding-top: 6px; }
        .summary { margin-bottom: 8px; display: flex; gap: 12px; flex-wrap: wrap; }
        .summary-box { border: 1px solid #ddd; padding: 4px 10px; border-radius: 3px; font-size: 8pt; }
        .summary-box .num { font-size: 13pt; font-weight: bold; }
    </style>
</head>
<body>

<div class="kop">
    <div class="instansi">Pengadilan Agama Makassar</div>
    <div class="alamat">Jl. Masjid Raya No.20, Makassar, Sulawesi Selatan | Telp. (0411) 3624951</div>
</div>

<div class="judul">
    <h2>Laporan Pengajuan Cuti Pegawai</h2>
    <div class="sub">Filter: {{ $filter }}</div>
</div>

<div class="meta">
    <span>Total data: <strong>{{ $data->count() }} pengajuan</strong></span>
    <span>Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIT</span>
</div>

@php
    $grouped = $data->groupBy('status');
@endphp

<table>
    <thead>
        <tr>
            <th style="width:3%">No</th>
            <th style="width:20%">Pegawai / NIP</th>
            <th style="width:14%">Nomor</th>
            <th style="width:13%">Jenis Cuti</th>
            <th style="width:11%">Tgl Mulai</th>
            <th style="width:11%">Tgl Selesai</th>
            <th style="width:5%">Hari</th>
            <th style="width:13%">Tgl Pengajuan</th>
            <th style="width:10%">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($data as $i => $item)
            @php
                $badgeClass = match($item->status) {
                    'disetujui'       => 'badge-green',
                    'ditolak'         => 'badge-red',
                    'menunggu_atasan' => 'badge-yellow',
                    'menunggu_ketua'  => 'badge-orange',
                    default           => 'badge-gray',
                };
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                    {{ $item->pegawai->nama }}<br>
                    <span style="color:#666">{{ $item->pegawai->nip }}</span>
                </td>
                <td style="font-family:monospace;font-size:7.5pt">{{ $item->nomor_pengajuan }}</td>
                <td>{{ $item->jenisCuti->nama }}</td>
                <td>{{ $item->tanggal_mulai->format('d/m/Y') }}</td>
                <td>{{ $item->tanggal_selesai->format('d/m/Y') }}</td>
                <td style="text-align:center">{{ $item->jumlah_hari }}</td>
                <td>{{ $item->tanggal_pengajuan->format('d/m/Y') }}</td>
                <td><span class="badge {{ $badgeClass }}">{{ $item->statusLabel() }}</span></td>
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
