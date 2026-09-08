<?php

namespace App\Http\Controllers;

use App\Models\Cuti;
use App\Services\CutiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminVerifikasiController extends Controller
{
    public function __construct(private readonly CutiService $cutiService) {}

    // ─────────────────────────────────────────────────────────
    // INDEX — daftar pengajuan menunggu verifikasi admin
    // ─────────────────────────────────────────────────────────
    public function index(Request $request): View
    {
        $query = Cuti::with(['pegawai.jabatan', 'pegawai.unitKerja', 'jenisCuti'])
            ->orderByDesc('tanggal_pengajuan');

        // Filter status: default tampilkan yang menunggu verifikasi
        $filterStatus = $request->get('status', 'menunggu_verifikasi_admin');

        if ($filterStatus !== 'semua') {
            $query->where('status', $filterStatus);
        }

        if ($request->filled('cari')) {
            $cari = $request->string('cari')->trim()->toString();
            $query->whereHas('pegawai', fn($q) =>
                $q->where('nama', 'like', "%{$cari}%")
                  ->orWhere('nip', 'like', "%{$cari}%")
            );
        }

        $pengajuan    = $query->paginate(15)->withQueryString();
        $jumlahStatus = Cuti::selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        return view('admin-verifikasi.index', compact('pengajuan', 'jumlahStatus', 'filterStatus'));
    }

    // ─────────────────────────────────────────────────────────
    // SHOW — detail pengajuan + form verifikasi
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

        return view('admin-verifikasi.show', compact('cuti'));
    }

    // ─────────────────────────────────────────────────────────
    // VERIFIKASI — admin isi nomor surat + masa kerja
    // ─────────────────────────────────────────────────────────
    public function verifikasi(Request $request, Cuti $cuti): RedirectResponse
    {
        if (! $cuti->bisaDiprosesAdmin()) {
            return back()->with('error', 'Pengajuan ini tidak dalam tahap verifikasi admin.');
        }

        $request->validate([
            'nomor_awal' => ['required', 'string', 'max:20', 'regex:/^[\d]+$/'],
            'masa_kerja' => 'required|string|max:50',
        ], [
            'nomor_awal.required' => 'Nomor surat wajib diisi.',
            'nomor_awal.regex'    => 'Nomor surat hanya boleh berisi angka (contoh: 444).',
            'masa_kerja.required' => 'Masa kerja wajib diisi.',
        ]);

        $nomorSurat = Cuti::formatNomorSurat($request->nomor_awal);

        try {
            $this->cutiService->verifikasiAdmin($cuti, $nomorSurat, $request->masa_kerja);

            $statusBerikut = $cuti->fresh()->status === 'menunggu_persetujuan_atasan'
                ? 'atasan langsung'
                : 'Ketua';

            return redirect()->route('admin-verifikasi.index')
                ->with('success', "Pengajuan {$cuti->nomor_pengajuan} berhasil diverifikasi. Diteruskan ke {$statusBerikut}.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }
    }

    // ─────────────────────────────────────────────────────────
    // TOLAK — admin tolak pengajuan sebelum diverifikasi
    // ─────────────────────────────────────────────────────────
    public function tolak(Request $request, Cuti $cuti): RedirectResponse
    {
        if (! $cuti->bisaDiprosesAdmin()) {
            return back()->with('error', 'Pengajuan ini tidak dalam tahap verifikasi admin.');
        }

        $request->validate([
            'catatan' => 'required|string|min:3|max:1000',
        ], [
            'catatan.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $cuti->update([
            'status'  => 'ditolak',
            'catatan' => $request->catatan,
        ]);

        return redirect()->route('admin-verifikasi.index')
            ->with('success', "Pengajuan {$cuti->nomor_pengajuan} telah ditolak oleh Admin.");
    }
}
