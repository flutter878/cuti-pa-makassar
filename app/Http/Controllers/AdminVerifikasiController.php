<?php

namespace App\Http\Controllers;

use App\Models\Cuti;
use App\Models\CutiAuditTrail;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\RoutingTemplate;
use App\Services\ApprovalWorkflowService;
use App\Services\CutiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminVerifikasiController extends Controller
{
    public function __construct(
        private readonly ApprovalWorkflowService $workflowService,
        private readonly CutiService             $cutiService,
    ) {}

    // ─────────────────────────────────────────────────────────
    // INDEX — daftar pengajuan menunggu verifikasi / routing
    // ─────────────────────────────────────────────────────────
    public function index(Request $request): View
    {
        $query = Cuti::with(['pegawai.jabatan', 'pegawai.unitKerja', 'jenisCuti'])
            ->orderByRaw("FIELD(status,
                'menunggu_verifikasi_admin','menunggu_routing','menunggu_approval',
                'dikembalikan','disetujui','ditolak','dibatalkan','selesai'
            )")
            ->orderByDesc('tanggal_pengajuan');

        $filterStatus = $request->get('status', 'aktif');

        if ($filterStatus === 'aktif') {
            $query->whereIn('status', [
                'menunggu_verifikasi_admin',
                'menunggu_routing',
                'menunggu_approval',
                'dikembalikan',
            ]);
        } elseif ($filterStatus !== 'semua') {
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
            'saldoDetail.saldoCuti',
            'dokumen',
            'approvalStages.user.pegawai.jabatan',
            'auditTrail.user',
        ]);

        // Cek kuota cuti pada tanggal yang diajukan
        $kuota = $this->workflowService->cekKuota(
            $cuti->tanggal_mulai->format('Y-m-d'),
            $cuti->tanggal_selesai->format('Y-m-d'),
        );

        return view('admin-verifikasi.show', compact('cuti', 'kuota'));
    }

    // ─────────────────────────────────────────────────────────
    // VERIFIKASI — admin isi nomor surat + masa kerja
    // Setelah ini: status → menunggu_routing
    // ─────────────────────────────────────────────────────────
    public function verifikasi(Request $request, Cuti $cuti): RedirectResponse
    {
        if (! $cuti->bisaDiprosesAdmin()) {
            return back()->with('error', 'Pengajuan ini tidak dalam tahap verifikasi admin.');
        }

        $request->validate([
            'nomor_awal' => ['required', 'string', 'max:20', 'regex:/^\d+$/'],
            'masa_kerja' => 'required|string|max:50',
        ], [
            'nomor_awal.required' => 'Nomor surat wajib diisi.',
            'nomor_awal.regex'    => 'Nomor surat hanya boleh berisi angka (contoh: 444).',
            'masa_kerja.required' => 'Masa kerja wajib diisi.',
        ]);

        $nomorSurat = Cuti::formatNomorSurat($request->nomor_awal);

        try {
            $this->workflowService->verifikasiAdmin(
                cuti:       $cuti,
                nomorSurat: $nomorSurat,
                masaKerja:  $request->masa_kerja,
                admin:      Auth::user(),
                ip:         $request->ip(),
            );

            return redirect()
                ->route('admin-verifikasi.routing', $cuti)
                ->with('success', "Verifikasi berhasil. Silakan tentukan routing approval untuk pengajuan {$cuti->nomor_pengajuan}.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }
    }

    // ─────────────────────────────────────────────────────────
    // ROUTING — tampilkan form pilih pejabat approval
    // ─────────────────────────────────────────────────────────
    public function routing(Cuti $cuti): View
    {
        if (! $cuti->bisaDiRouting()) {
            return redirect()
                ->route('admin-verifikasi.show', $cuti)
                ->with('error', 'Pengajuan ini tidak dalam tahap penentuan routing.');
        }

        $cuti->load([
            'pegawai.jabatan',
            'pegawai.unitKerja',
            'jenisCuti',
            'approvalStages',
        ]);

        // Ambil template routing berdasarkan kategori jabatan pemohon
        $kategoriPemohon = $cuti->pegawai?->jabatan?->kategori ?? 'staf';
        $templates       = RoutingTemplate::aktif()
            ->where('kategori_jabatan', $kategoriPemohon)
            ->with('stages')
            ->get();

        // Ambil semua pegawai aktif yang punya user (bisa di-assign sebagai approver)
        // Kecualikan: staf, pemohon itu sendiri
        // Kelompokkan per kategori jabatan
        $daftarPegawai = Pegawai::with(['jabatan', 'unitKerja', 'user'])
            ->whereHas('user')
            ->whereHas('jabatan', function ($q) {
                $q->whereNotIn('kategori', ['staf', 'panitera_pengganti', 'jurusita_pengganti']);
            })
            ->where('status', 'aktif')
            ->where('id', '!=', $cuti->pegawai_id) // Pemohon tidak bisa jadi approver dirinya sendiri
            ->orderBy('nama')
            ->get()
            ->groupBy(fn($p) => $p->jabatan?->kategori ?? 'lainnya');

        // Cek kuota
        $kuota = $this->workflowService->cekKuota(
            $cuti->tanggal_mulai->format('Y-m-d'),
            $cuti->tanggal_selesai->format('Y-m-d'),
        );

        $jenisTindakanOptions = [
            'approval'       => 'Menyetujui',
            'mengetahui'     => 'Mengetahui',
            'pertimbangan'   => 'Memberikan Pertimbangan',
            'final_approval' => 'Menyetujui (Final)',
        ];

        return view('admin-verifikasi.routing', compact(
            'cuti',
            'templates',
            'daftarPegawai',
            'kategoriPemohon',
            'kuota',
            'jenisTindakanOptions',
        ));
    }

    // ─────────────────────────────────────────────────────────
    // TERUSKAN — simpan routing dan teruskan ke approval
    // ─────────────────────────────────────────────────────────
    public function teruskan(Request $request, Cuti $cuti): RedirectResponse
    {
        if (! $cuti->bisaDiRouting()) {
            return back()->with('error', 'Pengajuan ini tidak dalam tahap penentuan routing.');
        }

        $request->validate([
            'stages'                    => 'required|array|min:1',
            'stages.*.user_id'          => 'required|exists:users,id',
            'stages.*.label_tahap'      => 'required|string|max:100',
            'stages.*.jenis_tindakan'   => 'required|in:approval,mengetahui,pertimbangan,final_approval',
            'stages.*.perlu_ttd'        => 'nullable|boolean',
        ], [
            'stages.required'               => 'Minimal harus ada satu tahap approval.',
            'stages.*.user_id.required'     => 'Pilih pejabat untuk setiap tahap.',
            'stages.*.user_id.exists'       => 'Pejabat tidak ditemukan.',
            'stages.*.label_tahap.required' => 'Label tahap wajib diisi.',
            'stages.*.jenis_tindakan.required' => 'Jenis tindakan wajib dipilih.',
        ]);

        // Pastikan tahap terakhir adalah final_approval
        $stages = $request->stages;
        $lastJenis = end($stages)['jenis_tindakan'] ?? '';

        if ($lastJenis !== 'final_approval') {
            return back()
                ->withInput()
                ->with('error', 'Tahap terakhir harus berjenis "Menyetujui (Final)".');
        }

        // Tambah perlu_ttd default jika tidak dikirim
        foreach ($stages as &$stage) {
            $stage['perlu_ttd'] = isset($stage['perlu_ttd']) ? (bool) $stage['perlu_ttd'] : true;
        }

        try {
            $this->workflowService->buatStagesFromRouting(
                cuti:   $cuti,
                stages: $stages,
                admin:  Auth::user(),
                ip:     $request->ip(),
            );

            return redirect()
                ->route('admin-verifikasi.index')
                ->with('success', "Pengajuan {$cuti->nomor_pengajuan} berhasil diteruskan ke tahap approval.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withInput()
                ->with('error', collect($e->errors())->flatten()->first());
        }
    }

    // ─────────────────────────────────────────────────────────
    // TOLAK — admin tolak pengajuan (sebelum routing)
    // ─────────────────────────────────────────────────────────
    public function tolak(Request $request, Cuti $cuti): RedirectResponse
    {
        if (! in_array($cuti->status, ['menunggu_verifikasi_admin', 'menunggu_routing'], true)) {
            return back()->with('error', 'Pengajuan ini tidak dapat ditolak pada tahap ini.');
        }

        $request->validate([
            'catatan' => 'required|string|min:3|max:1000',
        ], ['catatan.required' => 'Alasan penolakan wajib diisi.']);

        try {
            $this->workflowService->tolakAdmin(
                cuti:    $cuti,
                catatan: $request->catatan,
                admin:   Auth::user(),
                ip:      $request->ip(),
            );

            return redirect()
                ->route('admin-verifikasi.index')
                ->with('success', "Pengajuan {$cuti->nomor_pengajuan} telah ditolak oleh Admin.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }
    }

    // ─────────────────────────────────────────────────────────
    // API: cek kuota (AJAX)
    // ─────────────────────────────────────────────────────────
    public function cekKuota(Request $request)
    {
        $request->validate([
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $kuota = $this->workflowService->cekKuota(
            $request->tanggal_mulai,
            $request->tanggal_selesai,
        );

        return response()->json($kuota);
    }
}
