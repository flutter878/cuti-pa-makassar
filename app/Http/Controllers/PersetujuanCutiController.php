<?php

namespace App\Http\Controllers;

use App\Models\Cuti;
use App\Models\Pegawai;
use App\Models\PersetujuanCuti;
use App\Services\CutiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PersetujuanCutiController extends Controller
{
    public function __construct(private readonly CutiService $cutiService) {}

    // ─────────────────────────────────────────────────────────
    // Helper: ambil data pegawai user yang sedang login
    // ─────────────────────────────────────────────────────────
    private function pegawaiLogin(): ?Pegawai
    {
        return Auth::user()->pegawai?->loadMissing('jabatan');
    }

    // ─────────────────────────────────────────────────────────
    // INDEX — daftar pengajuan yang relevan untuk user login
    // ─────────────────────────────────────────────────────────
    public function index(Request $request): View
    {
        $pegawaiLogin = $this->pegawaiLogin();
        $isAdmin      = Auth::user()->isAdminOrSuperadmin();

        $query = Cuti::with(['pegawai.jabatan', 'jenisCuti'])
            ->orderByRaw("FIELD(status,
                'menunggu_atasan','menunggu_ketua','diajukan',
                'disetujui','ditolak','dibatalkan'
            )")
            ->orderByDesc('tanggal_pengajuan');

        if ($isAdmin) {
            // Admin/superadmin lihat semua
        } elseif ($pegawaiLogin) {
            if ($pegawaiLogin->isKetua()) {
                // Ketua lihat yang menunggu ketuanya + yang sudah final
                $query->where(function ($q) {
                    $q->whereIn('status', ['menunggu_persetujuan_ketua', 'disetujui', 'ditolak']);
                });
            } elseif ($pegawaiLogin->isAtasanLangsung()) {
                // Atasan lihat bawahan langsungnya
                $bawahanIds = $pegawaiLogin->bawahan()->pluck('id');
                $query->whereIn('pegawai_id', $bawahanIds);
            } else {
                // Pegawai biasa tidak boleh akses halaman ini
                abort(403, 'Anda tidak memiliki akses ke halaman persetujuan.');
            }
        } else {
            abort(403);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $cuti         = $query->paginate(15)->withQueryString();
        $jumlahStatus = Cuti::selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        return view('persetujuan.index', compact('cuti', 'jumlahStatus', 'pegawaiLogin', 'isAdmin'));
    }

    // ─────────────────────────────────────────────────────────
    // SHOW — detail pengajuan
    // ─────────────────────────────────────────────────────────
    public function show(Cuti $cuti): View
    {
        $cuti->load([
            'jenisCuti',
            'pegawai.jabatan',
            'pegawai.unitKerja',
            'pegawai.atasanLangsung.jabatan',
            'saldoDetail.saldoCuti',
            'dokumen',
            'persetujuan.user.pegawai.jabatan',
        ]);

        $pegawaiLogin = $this->pegawaiLogin();
        $isAdmin      = Auth::user()->isAdminOrSuperadmin();

        // Tentukan peran user yang sedang login terhadap pengajuan ini
        $bisaApproveAtasan = false;
        $bisaApproveKetua  = false;

        if ($pegawaiLogin) {
            // Bisa approve sebagai atasan jika:
            // - jabatannya Panitera/Sekretaris
            // - dia adalah atasan langsung dari pegawai yang mengajukan
            // - status cuti sedang menunggu_atasan
            $bisaApproveAtasan = $cuti->bisaDiprosesAtasan()
                && $pegawaiLogin->isAtasanLangsung()
                && (int) $cuti->pegawai->atasan_langsung_id === (int) $pegawaiLogin->id;

            // Bisa approve sebagai Ketua jika jabatannya Ketua & status menunggu_ketua
            $bisaApproveKetua = $cuti->bisaDiprosesKetua()
                && $pegawaiLogin->isKetua();
        }

        // Admin bisa lihat semua tapi tidak bisa approve (hanya monitor)
        return view('persetujuan.show', compact(
            'cuti',
            'pegawaiLogin',
            'isAdmin',
            'bisaApproveAtasan',
            'bisaApproveKetua',
        ));
    }

    // ─────────────────────────────────────────────────────────
    // APPROVE ATASAN — Panitera/Sekretaris setujui (Level 1)
    // ─────────────────────────────────────────────────────────
    public function approveAtasan(Cuti $cuti): RedirectResponse
    {
        $pegawaiLogin = $this->pegawaiLogin();

        if (! $pegawaiLogin || ! $pegawaiLogin->isAtasanLangsung()) {
            return back()->with('error', 'Anda tidak memiliki wewenang untuk menyetujui.');
        }

        if ($cuti->pegawai->atasan_langsung_id !== $pegawaiLogin->id) {
            return back()->with('error', 'Anda bukan atasan langsung dari pegawai ini.');
        }

        if (! $cuti->bisaDiprosesAtasan()) {
            return back()->with('error', 'Pengajuan ini tidak dalam tahap persetujuan atasan.');
        }

        DB::transaction(function () use ($cuti, $pegawaiLogin) {
            $cuti->update(['status' => 'menunggu_persetujuan_ketua']);

            PersetujuanCuti::create([
                'cuti_id'            => $cuti->id,
                'user_id'            => Auth::id(),
                'level'              => 'atasan',
                'status'             => 'disetujui',
                'catatan'            => request('catatan'),
                'tanggal_persetujuan' => now(),
            ]);
        });

        return redirect()->route('persetujuan.show', $cuti)
            ->with('success', "Pengajuan {$cuti->nomor_pengajuan} disetujui. Menunggu persetujuan Ketua.");
    }

    // ─────────────────────────────────────────────────────────
    // REJECT ATASAN — Panitera/Sekretaris tolak (Level 1)
    // ─────────────────────────────────────────────────────────
    public function rejectAtasan(Request $request, Cuti $cuti): RedirectResponse
    {
        $request->validate([
            'catatan' => 'required|string|min:3|max:1000',
        ], ['catatan.required' => 'Catatan penolakan wajib diisi.']);

        $pegawaiLogin = $this->pegawaiLogin();

        if (! $pegawaiLogin || ! $pegawaiLogin->isAtasanLangsung()) {
            return back()->with('error', 'Anda tidak memiliki wewenang untuk menolak.');
        }

        if ($cuti->pegawai->atasan_langsung_id !== $pegawaiLogin->id) {
            return back()->with('error', 'Anda bukan atasan langsung dari pegawai ini.');
        }

        if (! $cuti->bisaDiprosesAtasan()) {
            return back()->with('error', 'Pengajuan ini tidak dalam tahap persetujuan atasan.');
        }

        DB::transaction(function () use ($cuti, $request) {
            $cuti->update([
                'status'  => 'ditolak',
                'catatan' => $request->catatan,
            ]);

            PersetujuanCuti::create([
                'cuti_id'            => $cuti->id,
                'user_id'            => Auth::id(),
                'level'              => 'atasan',
                'status'             => 'ditolak',
                'catatan'            => $request->catatan,
                'tanggal_persetujuan' => now(),
            ]);
        });

        return redirect()->route('persetujuan.show', $cuti)
            ->with('success', "Pengajuan {$cuti->nomor_pengajuan} telah ditolak.");
    }

    // ─────────────────────────────────────────────────────────
    // APPROVE KETUA — Ketua setujui final (Level 2)
    // ─────────────────────────────────────────────────────────
    public function approveKetua(Cuti $cuti): RedirectResponse
    {
        $pegawaiLogin = $this->pegawaiLogin();

        if (! $pegawaiLogin || ! $pegawaiLogin->isKetua()) {
            return back()->with('error', 'Anda tidak memiliki wewenang sebagai Ketua.');
        }

        if (! $cuti->bisaDiprosesKetua()) {
            return back()->with('error', 'Pengajuan ini tidak dalam tahap persetujuan Ketua.');
        }

        DB::transaction(function () use ($cuti) {
            $cuti->load('saldoDetail');
            $this->cutiService->kurangiSaldo($cuti);

            $cuti->update(['status' => 'disetujui']);

            PersetujuanCuti::create([
                'cuti_id'            => $cuti->id,
                'user_id'            => Auth::id(),
                'level'              => 'ketua',
                'status'             => 'disetujui',
                'catatan'            => request('catatan'),
                'tanggal_persetujuan' => now(),
            ]);
        });

        return redirect()->route('persetujuan.show', $cuti)
            ->with('success', "Pengajuan {$cuti->nomor_pengajuan} telah disetujui oleh Ketua. Saldo cuti dikurangi.");
    }

    // ─────────────────────────────────────────────────────────
    // REJECT KETUA — Ketua tolak (Level 2)
    // ─────────────────────────────────────────────────────────
    public function rejectKetua(Request $request, Cuti $cuti): RedirectResponse
    {
        $request->validate([
            'catatan' => 'required|string|min:3|max:1000',
        ], ['catatan.required' => 'Catatan penolakan wajib diisi.']);

        $pegawaiLogin = $this->pegawaiLogin();

        if (! $pegawaiLogin || ! $pegawaiLogin->isKetua()) {
            return back()->with('error', 'Anda tidak memiliki wewenang sebagai Ketua.');
        }

        if (! $cuti->bisaDiprosesKetua()) {
            return back()->with('error', 'Pengajuan ini tidak dalam tahap persetujuan Ketua.');
        }

        DB::transaction(function () use ($cuti, $request) {
            $cuti->update([
                'status'  => 'ditolak',
                'catatan' => $request->catatan,
            ]);

            PersetujuanCuti::create([
                'cuti_id'            => $cuti->id,
                'user_id'            => Auth::id(),
                'level'              => 'ketua',
                'status'             => 'ditolak',
                'catatan'            => $request->catatan,
                'tanggal_persetujuan' => now(),
            ]);
        });

        return redirect()->route('persetujuan.show', $cuti)
            ->with('success', "Pengajuan {$cuti->nomor_pengajuan} telah ditolak oleh Ketua.");
    }
}
