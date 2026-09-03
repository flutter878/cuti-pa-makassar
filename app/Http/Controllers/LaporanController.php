<?php

namespace App\Http\Controllers;

use App\Models\Cuti;
use App\Models\JenisCuti;
use App\Models\Pegawai;
use App\Models\SaldoCuti;
use App\Models\UnitKerja;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class LaporanController extends Controller
{
    // ─────────────────────────────────────────────────────────
    // LAPORAN PENGAJUAN CUTI
    // ─────────────────────────────────────────────────────────

    public function pengajuan(Request $request): View
    {
        $query = Cuti::with(['pegawai.jabatan', 'pegawai.unitKerja', 'jenisCuti'])
            ->orderByDesc('tanggal_pengajuan');

        $this->applyFilterPengajuan($query, $request);

        $data      = $query->paginate(20)->withQueryString();
        $jenisCuti = JenisCuti::orderBy('nama')->get();
        $unitKerja = UnitKerja::orderBy('nama_unit')->get();

        return view('laporan.pengajuan', compact('data', 'jenisCuti', 'unitKerja'));
    }

    public function exportPengajuan(Request $request): Response
    {
        $query = Cuti::with(['pegawai.jabatan', 'pegawai.unitKerja', 'jenisCuti'])
            ->orderByDesc('tanggal_pengajuan');

        $this->applyFilterPengajuan($query, $request);

        $data      = $query->get();
        $jenisCuti = JenisCuti::orderBy('nama')->get();
        $unitKerja = UnitKerja::orderBy('nama_unit')->get();
        $filter    = $this->getFilterLabel($request);

        $pdf = Pdf::loadView('pdf.laporan-pengajuan', compact('data', 'filter'))
            ->setPaper('a4', 'landscape')
            ->setOption('margin_top', 15)
            ->setOption('margin_bottom', 15)
            ->setOption('margin_left', 15)
            ->setOption('margin_right', 15);

        $filename = 'laporan-pengajuan-cuti-' . now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }

    private function applyFilterPengajuan($query, Request $request): void
    {
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_pengajuan', $request->tahun);
        }

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal_pengajuan', $request->bulan);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('jenis_cuti_id')) {
            $query->where('jenis_cuti_id', $request->jenis_cuti_id);
        }

        if ($request->filled('unit_kerja_id')) {
            $query->whereHas('pegawai', fn($q) => $q->where('unit_kerja_id', $request->unit_kerja_id));
        }

        if ($request->filled('cari')) {
            $cari = $request->string('cari')->trim()->toString();
            $query->whereHas('pegawai', function ($q) use ($cari) {
                $q->where('nama', 'like', "%{$cari}%")
                  ->orWhere('nip', 'like', "%{$cari}%");
            });
        }
    }

    // ─────────────────────────────────────────────────────────
    // LAPORAN SALDO CUTI
    // ─────────────────────────────────────────────────────────

    public function saldo(Request $request): View
    {
        $tahun = $request->integer('tahun', now()->year);

        $query = Pegawai::with(['jabatan', 'unitKerja',
                'saldoCuti' => fn($q) => $q->where('tahun', $tahun),
            ])
            ->where('status', 'aktif')
            ->orderBy('nama');

        if ($request->filled('unit_kerja_id')) {
            $query->where('unit_kerja_id', $request->unit_kerja_id);
        }

        if ($request->filled('cari')) {
            $cari = $request->string('cari')->trim()->toString();
            $query->where(function ($q) use ($cari) {
                $q->where('nama', 'like', "%{$cari}%")
                  ->orWhere('nip', 'like', "%{$cari}%");
            });
        }

        $data      = $query->paginate(20)->withQueryString();
        $unitKerja = UnitKerja::orderBy('nama_unit')->get();
        $tahunList = range(now()->year, now()->year - 4);

        return view('laporan.saldo', compact('data', 'unitKerja', 'tahun', 'tahunList'));
    }

    public function exportSaldo(Request $request): Response
    {
        $tahun = $request->integer('tahun', now()->year);

        $query = Pegawai::with(['jabatan', 'unitKerja',
                'saldoCuti' => fn($q) => $q->where('tahun', $tahun),
            ])
            ->where('status', 'aktif')
            ->orderBy('nama');

        if ($request->filled('unit_kerja_id')) {
            $query->where('unit_kerja_id', $request->unit_kerja_id);
        }

        $data   = $query->get();
        $filter = "Tahun {$tahun}";

        if ($request->filled('unit_kerja_id')) {
            $uk = UnitKerja::find($request->unit_kerja_id);
            if ($uk) $filter .= " — {$uk->nama_unit}";
        }

        $pdf = Pdf::loadView('pdf.laporan-saldo', compact('data', 'tahun', 'filter'))
            ->setPaper('a4', 'portrait')
            ->setOption('margin_top', 15)
            ->setOption('margin_bottom', 15)
            ->setOption('margin_left', 15)
            ->setOption('margin_right', 15);

        $filename = 'laporan-saldo-cuti-' . $tahun . '-' . now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }

    // ─────────────────────────────────────────────────────────
    // PDF FORMULIR CUTI (per pengajuan)
    // ─────────────────────────────────────────────────────────

    public function formulirCuti(Cuti $cuti): Response
    {
        $cuti->load([
            'jenisCuti',
            'pegawai.jabatan',
            'pegawai.unitKerja',
            'pegawai.atasanLangsung.jabatan',
            'persetujuan.user.pegawai.jabatan',
        ]);

        $pdf = Pdf::loadView('pdf.formulir-cuti', compact('cuti'))
            ->setPaper('a4', 'portrait')
            ->setOption('margin_top', 20)
            ->setOption('margin_bottom', 20)
            ->setOption('margin_left', 25)
            ->setOption('margin_right', 25);

        $filename = 'formulir-cuti-' . $cuti->nomor_pengajuan . '.pdf';
        $filename = str_replace('/', '-', $filename);

        return $pdf->download($filename);
    }

    // ─────────────────────────────────────────────────────────
    // HELPER
    // ─────────────────────────────────────────────────────────

    private function getFilterLabel(Request $request): string
    {
        $parts = [];

        if ($request->filled('tahun')) {
            $parts[] = 'Tahun ' . $request->tahun;
        }

        if ($request->filled('bulan')) {
            $bulanNama = [
                1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
                5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
                9=>'September',10=>'Oktober',11=>'November',12=>'Desember',
            ];
            $parts[] = $bulanNama[(int)$request->bulan] ?? '';
        }

        if ($request->filled('status')) {
            $parts[] = ucfirst(str_replace('_', ' ', $request->status));
        }

        return implode(', ', array_filter($parts)) ?: 'Semua Data';
    }
}
