<?php

namespace App\Http\Controllers;

use App\Models\ApprovalStage;
use App\Models\Cuti;
use App\Models\Pegawai;
use App\Models\SaldoCuti;
use App\Services\ApprovalWorkflowService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ApprovalWorkflowService $workflowService
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $role = $user->role?->slug;

        if ($role === 'pegawai') {
            return $this->dashboardPegawai($user);
        }

        return $this->dashboardAdmin($user);
    }

    // ─────────────────────────────────────────────────────────
    // Dashboard Pegawai
    // ─────────────────────────────────────────────────────────
    private function dashboardPegawai($user): View
    {
        $pegawai   = $user->pegawai;
        $tahun     = now()->year;

        $saldoTahunIni     = null;
        $saldoCarryOver    = null;
        $totalPengajuan    = 0;
        $pengajuanTerakhir = collect();
        $jumlahApprovalPending = 0;

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

        // Approval pending untuk user ini (jika dia ditugaskan sebagai approver)
        $jumlahApprovalPending = ApprovalStage::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        return view('dashboard', compact(
            'saldoTahunIni',
            'saldoCarryOver',
            'totalPengajuan',
            'pengajuanTerakhir',
            'jumlahApprovalPending',
        ));
    }

    // ─────────────────────────────────────────────────────────
    // Dashboard Admin / Superadmin
    // ─────────────────────────────────────────────────────────
    private function dashboardAdmin($user): View
    {
        $tahun = now()->year;

        // ── Statistik utama ──────────────────────────────────
        $totalPegawai = Pegawai::where('status', 'aktif')->count();

        $totalMenungguVerifikasi = Cuti::where('status', 'menunggu_verifikasi_admin')->count();
        $totalMenungguRouting    = Cuti::where('status', 'menunggu_routing')->count();
        $totalMenungguApproval   = Cuti::where('status', 'menunggu_approval')->count();
        $totalDikembalikan       = Cuti::where('status', 'dikembalikan')->count();
        $totalDisetujui          = Cuti::where('status', 'disetujui')
            ->whereYear('tanggal_pengajuan', $tahun)
            ->count();
        $totalDitolak            = Cuti::where('status', 'ditolak')
            ->whereYear('tanggal_pengajuan', $tahun)
            ->count();

        // Total "menunggu tindakan" Admin (belum routing)
        $totalAktifAdmin = $totalMenungguVerifikasi + $totalMenungguRouting;

        // ── Cuti hari ini & besok ────────────────────────────
        $cutiHariIni = Cuti::with('pegawai.jabatan')
            ->whereIn('status', ['menunggu_approval', 'disetujui'])
            ->where('tanggal_mulai', '<=', now()->toDateString())
            ->where('tanggal_selesai', '>=', now()->toDateString())
            ->count();

        $cutiHariIniList = Cuti::with(['pegawai.jabatan', 'jenisCuti'])
            ->whereIn('status', ['menunggu_approval', 'disetujui'])
            ->where('tanggal_mulai', '<=', now()->toDateString())
            ->where('tanggal_selesai', '>=', now()->toDateString())
            ->get();

        $cutiBesok = Cuti::with('pegawai.jabatan')
            ->whereIn('status', ['menunggu_approval', 'disetujui'])
            ->where('tanggal_mulai', '<=', now()->addDay()->toDateString())
            ->where('tanggal_selesai', '>=', now()->addDay()->toDateString())
            ->count();

        $cutiMingguIni = Cuti::whereIn('status', ['menunggu_approval', 'disetujui'])
            ->where('tanggal_mulai', '<=', now()->endOfWeek()->toDateString())
            ->where('tanggal_selesai', '>=', now()->startOfWeek()->toDateString())
            ->count();

        // ── Rekap kalender 30 hari ke depan ─────────────────
        // Setiap hari: jumlah pegawai yang cuti
        $kalenderCuti = [];
        for ($i = 0; $i < 30; $i++) {
            $tgl    = now()->addDays($i)->toDateString();
            $jumlah = Cuti::whereIn('status', ['menunggu_approval', 'disetujui'])
                ->where('tanggal_mulai', '<=', $tgl)
                ->where('tanggal_selesai', '>=', $tgl)
                ->count();
            if ($jumlah > 0) {
                $kalenderCuti[$tgl] = $jumlah;
            }
        }

        // ── Rekap bulanan tahun berjalan ─────────────────────
        $rekapBulanan = [];
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $rekapBulanan[$bulan] = Cuti::where('status', 'disetujui')
                ->whereYear('tanggal_mulai', $tahun)
                ->whereMonth('tanggal_mulai', $bulan)
                ->count();
        }

        // ── Pengajuan terbaru ────────────────────────────────
        $pengajuanTerbaru = Cuti::with(['pegawai.jabatan', 'jenisCuti'])
            ->orderByRaw("FIELD(status,
                'menunggu_verifikasi_admin','menunggu_routing','menunggu_approval',
                'dikembalikan','disetujui','ditolak','dibatalkan','selesai'
            )")
            ->orderByDesc('tanggal_pengajuan')
            ->limit(10)
            ->get();

        // ── Ringkasan per status ─────────────────────────────
        $ringkasanStatus = Cuti::selectRaw('status, COUNT(*) as jumlah')
            ->whereYear('tanggal_pengajuan', $tahun)
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        return view('dashboard', compact(
            'totalPegawai',
            'totalMenungguVerifikasi',
            'totalMenungguRouting',
            'totalMenungguApproval',
            'totalDikembalikan',
            'totalDisetujui',
            'totalDitolak',
            'totalAktifAdmin',
            'cutiHariIni',
            'cutiHariIniList',
            'cutiBesok',
            'cutiMingguIni',
            'kalenderCuti',
            'rekapBulanan',
            'pengajuanTerbaru',
            'ringkasanStatus',
        ));
    }
}
