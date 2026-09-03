<?php

namespace App\Http\Controllers;

use App\Models\Cuti;
use App\Models\Pegawai;
use App\Models\SaldoCuti;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $role = $user->role?->slug;

        if ($role === 'pegawai') {
            return $this->dashboardPegawai($user);
        }

        return $this->dashboardAdmin();
    }

    // ─────────────────────────────────────────────────────────
    // Dashboard Pegawai
    // ─────────────────────────────────────────────────────────
    private function dashboardPegawai($user): View
    {
        $pegawai    = $user->pegawai;
        $tahun      = now()->year;

        $saldoTahunIni = null;
        $saldoCarryOver = null;
        $totalPengajuan = 0;
        $pengajuanTerakhir = collect();

        if ($pegawai) {
            $saldoTahunIni = SaldoCuti::where('pegawai_id', $pegawai->id)
                ->where('tahun', $tahun)
                ->first();

            $saldoCarryOver = SaldoCuti::where('pegawai_id', $pegawai->id)
                ->where('tahun', $tahun - 1)
                ->first();

            $totalPengajuan = Cuti::where('pegawai_id', $pegawai->id)->count();

            $pengajuanTerakhir = Cuti::with('jenisCuti')
                ->where('pegawai_id', $pegawai->id)
                ->orderByDesc('tanggal_pengajuan')
                ->limit(5)
                ->get();
        }

        return view('dashboard', compact(
            'saldoTahunIni',
            'saldoCarryOver',
            'totalPengajuan',
            'pengajuanTerakhir',
        ));
    }

    // ─────────────────────────────────────────────────────────
    // Dashboard Admin / Superadmin
    // ─────────────────────────────────────────────────────────
    private function dashboardAdmin(): View
    {
        $tahun = now()->year;

        // Statistik utama
        $totalPegawai   = Pegawai::where('status', 'aktif')->count();
        $totalMenunggu  = Cuti::whereIn('status', ['diajukan', 'diproses'])->count();
        $totalDisetujui = Cuti::where('status', 'disetujui')
            ->whereYear('tanggal_pengajuan', $tahun)
            ->count();
        $totalDitolak   = Cuti::where('status', 'ditolak')
            ->whereYear('tanggal_pengajuan', $tahun)
            ->count();
        $totalBulanIni  = Cuti::whereMonth('tanggal_pengajuan', now()->month)
            ->whereYear('tanggal_pengajuan', $tahun)
            ->count();

        // Pengajuan terbaru (10 terakhir)
        $pengajuanTerbaru = Cuti::with(['pegawai', 'jenisCuti'])
            ->orderByDesc('tanggal_pengajuan')
            ->limit(10)
            ->get();

        // Ringkasan per status (bulan ini)
        $ringkasanStatus = Cuti::selectRaw('status, COUNT(*) as jumlah')
            ->whereYear('tanggal_pengajuan', $tahun)
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        return view('dashboard', compact(
            'totalPegawai',
            'totalMenunggu',
            'totalDisetujui',
            'totalDitolak',
            'totalBulanIni',
            'pengajuanTerbaru',
            'ringkasanStatus',
        ));
    }
}
