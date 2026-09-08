<?php

namespace App\Http\Controllers;

use App\Models\Cuti;
use App\Models\CutiAuditTrail;
use App\Models\JenisCuti;
use App\Models\SaldoCuti;
use App\Services\ApprovalWorkflowService;
use App\Services\CutiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CutiController extends Controller
{
    public function __construct(
        private readonly CutiService             $cutiService,
        private readonly ApprovalWorkflowService $workflowService,
    ) {}

    // ─── Form pengajuan baru ─────────────────────────────────
    public function create(): View
    {
        $pegawai   = auth()->user()->pegawai;
        $jenisCuti = JenisCuti::aktif()->orderBy('nama')->get();

        $tahun = now()->year;
        $saldo = SaldoCuti::where('pegawai_id', $pegawai->id)
            ->whereIn('tahun', [$tahun - 1, $tahun])
            ->orderBy('tahun')
            ->get();

        return view('cuti.create', compact('pegawai', 'jenisCuti', 'saldo'));
    }

    // ─── Simpan pengajuan baru ───────────────────────────────
    public function store(Request $request): RedirectResponse
    {
        $pegawai   = auth()->user()->pegawai;
        $jenisCuti = JenisCuti::find($request->jenis_cuti_id);

        $rules = [
            'jenis_cuti_id'   => 'required|exists:jenis_cuti,id',
            'tanggal_mulai'   => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan'          => 'required|string|min:10|max:500',
            'alamat_cuti'     => 'required|string|max:255',
            'no_telepon'      => 'nullable|string|max:20',
        ];

        if ($jenisCuti?->membutuhkan_lampiran) {
            $rules['lampiran'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
        } else {
            $rules['lampiran'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
        }

        $messages = [
            'jenis_cuti_id.required'         => 'Jenis cuti wajib dipilih.',
            'tanggal_mulai.required'          => 'Tanggal mulai wajib diisi.',
            'tanggal_mulai.after_or_equal'    => 'Tanggal mulai tidak boleh di masa lalu.',
            'tanggal_selesai.required'        => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal'  => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'alasan.required'                 => 'Alasan cuti wajib diisi.',
            'alasan.min'                      => 'Alasan cuti minimal 10 karakter.',
            'alamat_cuti.required'            => 'Alamat selama cuti wajib diisi.',
            'lampiran.required'               => 'Lampiran wajib diunggah untuk jenis cuti ini.',
            'lampiran.mimes'                  => 'Lampiran harus berformat PDF, JPG, atau PNG.',
            'lampiran.max'                    => 'Ukuran lampiran maksimal 5 MB.',
        ];

        $request->validate($rules, $messages);

        $jumlahHari = $this->cutiService->hitungHari(
            $request->tanggal_mulai,
            $request->tanggal_selesai
        );

        try {
            $cuti = $this->cutiService->ajukan(
                pegawai:  $pegawai,
                data:     array_merge($request->only([
                    'jenis_cuti_id', 'tanggal_mulai', 'tanggal_selesai',
                    'alasan', 'alamat_cuti', 'no_telepon',
                ]), ['jumlah_hari' => $jumlahHari]),
                lampiran: $request->file('lampiran'),
            );

            // Catat audit trail
            CutiAuditTrail::catat(
                cutiId:     $cuti->id,
                aktor:      $pegawai->nama,
                aksi:       'MENGAJUKAN',
                keterangan: "Pengajuan cuti {$cuti->jenisCuti?->nama} selama {$cuti->jumlah_hari} hari kerja.",
                userId:     auth()->id(),
                ip:         $request->ip(),
            );

            return redirect()->route('cuti.show', $cuti)
                ->with('success', "Pengajuan cuti berhasil diajukan dengan nomor {$cuti->nomor_pengajuan}.");

        } catch (ValidationException $e) {
            throw $e;
        }
    }

    // ─── Form edit pengajuan yang dikembalikan ───────────────
    public function edit(Cuti $cuti): View
    {
        $pegawai = auth()->user()->pegawai;

        // Hanya pemohon yang bisa edit
        if ($pegawai && $cuti->pegawai_id !== $pegawai->id) {
            abort(403);
        }

        // Hanya bisa edit jika status dikembalikan
        if (! $cuti->bisaDiedit()) {
            return redirect()->route('cuti.show', $cuti)
                ->with('error', 'Pengajuan ini tidak dapat diedit.');
        }

        $cuti->load(['jenisCuti', 'dokumen', 'auditTrail']);

        $jenisCuti = JenisCuti::aktif()->orderBy('nama')->get();
        $tahun     = now()->year;
        $saldo     = SaldoCuti::where('pegawai_id', $pegawai->id)
            ->whereIn('tahun', [$tahun - 1, $tahun])
            ->orderBy('tahun')
            ->get();

        return view('cuti.edit', compact('cuti', 'jenisCuti', 'saldo'));
    }

    // ─── Update pengajuan yang dikembalikan ─────────────────
    public function update(Request $request, Cuti $cuti): RedirectResponse
    {
        $pegawai = auth()->user()->pegawai;

        if ($pegawai && $cuti->pegawai_id !== $pegawai->id) {
            abort(403);
        }

        if (! $cuti->bisaDiedit()) {
            return back()->with('error', 'Pengajuan ini tidak dapat diedit.');
        }

        $jenisCuti = JenisCuti::find($request->jenis_cuti_id);

        $rules = [
            'jenis_cuti_id'   => 'required|exists:jenis_cuti,id',
            'tanggal_mulai'   => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan'          => 'required|string|min:10|max:500',
            'alamat_cuti'     => 'required|string|max:255',
            'no_telepon'      => 'nullable|string|max:20',
        ];

        if ($jenisCuti?->membutuhkan_lampiran) {
            $rules['lampiran'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
        } else {
            $rules['lampiran'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
        }

        $request->validate($rules, [
            'jenis_cuti_id.required'         => 'Jenis cuti wajib dipilih.',
            'tanggal_mulai.required'          => 'Tanggal mulai wajib diisi.',
            'tanggal_mulai.after_or_equal'    => 'Tanggal mulai tidak boleh di masa lalu.',
            'tanggal_selesai.required'        => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal'  => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'alasan.required'                 => 'Alasan cuti wajib diisi.',
            'alasan.min'                      => 'Alasan cuti minimal 10 karakter.',
            'alamat_cuti.required'            => 'Alamat selama cuti wajib diisi.',
            'lampiran.required'               => 'Lampiran wajib diunggah untuk jenis cuti ini.',
            'lampiran.mimes'                  => 'Lampiran harus berformat PDF, JPG, atau PNG.',
            'lampiran.max'                    => 'Ukuran lampiran maksimal 5 MB.',
        ]);

        $jumlahHari = $this->cutiService->hitungHari(
            $request->tanggal_mulai,
            $request->tanggal_selesai
        );

        try {
            // Hitung ulang saldo FIFO dengan data baru
            $jenisCutiObj  = JenisCuti::findOrFail($request->jenis_cuti_id);
            $saldoDetail   = $this->cutiService->hitungSaldoFifo($pegawai, $jenisCutiObj, $jumlahHari);

            // Hapus detail saldo lama dan ganti dengan yang baru
            $cuti->saldoDetail()->delete();
            foreach ($saldoDetail as $detail) {
                \App\Models\CutiSaldoDetail::create([
                    'cuti_id'          => $cuti->id,
                    'saldo_cuti_id'    => $detail['saldo_cuti_id'],
                    'jumlah_digunakan' => $detail['jumlah_digunakan'],
                ]);
            }

            // Update lampiran jika ada yang baru
            if ($request->hasFile('lampiran')) {
                // Hapus file lama
                foreach ($cuti->dokumen as $dok) {
                    Storage::disk('public')->delete($dok->file_path);
                    $dok->delete();
                }

                $lampiran = $request->file('lampiran');
                $path     = $lampiran->store('lampiran-cuti', 'public');
                \App\Models\DokumenCuti::create([
                    'cuti_id'   => $cuti->id,
                    'nama_file' => $lampiran->getClientOriginalName(),
                    'file_path' => $path,
                    'tipe_file' => $lampiran->getClientMimeType(),
                ]);
            }

            // Submit ulang melalui service
            $this->workflowService->submitUlang(
                cuti: $cuti,
                data: [
                    'jenis_cuti_id'  => $request->jenis_cuti_id,
                    'tanggal_mulai'  => $request->tanggal_mulai,
                    'tanggal_selesai' => $request->tanggal_selesai,
                    'jumlah_hari'    => $jumlahHari,
                    'alasan'         => $request->alasan,
                    'alamat_cuti'    => $request->alamat_cuti,
                    'no_telepon'     => $request->no_telepon,
                ],
                user: auth()->user(),
                ip:   $request->ip(),
            );

            return redirect()->route('cuti.show', $cuti)
                ->with('success', "Pengajuan {$cuti->nomor_pengajuan} berhasil diperbaiki dan diajukan kembali.");

        } catch (ValidationException $e) {
            return back()
                ->withInput()
                ->with('error', collect($e->errors())->flatten()->first());
        }
    }

    // ─── Riwayat cuti pegawai ────────────────────────────────
    public function index(Request $request): View
    {
        $pegawai = auth()->user()->pegawai;

        $query = Cuti::with('jenisCuti')
            ->where('pegawai_id', $pegawai->id)
            ->orderByDesc('tanggal_pengajuan');

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_mulai', $request->tahun);
        }

        if ($request->filled('jenis_cuti_id')) {
            $query->where('jenis_cuti_id', $request->jenis_cuti_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $cuti      = $query->paginate(10)->withQueryString();
        $jenisCuti = JenisCuti::orderBy('nama')->get();
        $tahunList = range(now()->year, now()->year - 4);

        return view('cuti.index', compact('cuti', 'jenisCuti', 'tahunList'));
    }

    // ─── Detail pengajuan ────────────────────────────────────
    public function show(Cuti $cuti): View
    {
        $pegawai = auth()->user()->pegawai;
        if ($pegawai && $cuti->pegawai_id !== $pegawai->id) {
            abort(403);
        }

        $cuti->load([
            'jenisCuti',
            'pegawai.jabatan',
            'pegawai.unitKerja',
            'saldoDetail.saldoCuti',
            'dokumen',
            'approvalStages.user.pegawai.jabatan',
            'auditTrail.user',
            'persetujuan.user', // backward compat
        ]);

        return view('cuti.show', compact('cuti'));
    }

    // ─── Batalkan pengajuan ──────────────────────────────────
    public function destroy(Cuti $cuti): RedirectResponse
    {
        $pegawai = auth()->user()->pegawai;
        if ($pegawai && $cuti->pegawai_id !== $pegawai->id) {
            abort(403);
        }

        try {
            $this->cutiService->batalkan($cuti);

            CutiAuditTrail::catat(
                cutiId: $cuti->id,
                aktor:  $pegawai->nama,
                aksi:   'MEMBATALKAN',
                userId: auth()->id(),
                ip:     request()->ip(),
            );

            return redirect()->route('cuti.index')
                ->with('success', "Pengajuan {$cuti->nomor_pengajuan} berhasil dibatalkan.");

        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }
    }

    // ─── Hitung hari (AJAX) ──────────────────────────────────
    public function hitungHari(Request $request)
    {
        $request->validate([
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $hari = $this->cutiService->hitungHari(
            $request->tanggal_mulai,
            $request->tanggal_selesai
        );

        return response()->json(['jumlah_hari' => $hari]);
    }

    // ─── Download lampiran ───────────────────────────────────
    public function downloadLampiran(Cuti $cuti, int $dokumenId)
    {
        $pegawai = auth()->user()->pegawai;
        if ($pegawai && $cuti->pegawai_id !== $pegawai->id) {
            abort(403);
        }

        $dokumen = $cuti->dokumen()->findOrFail($dokumenId);

        if (! Storage::disk('public')->exists($dokumen->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download($dokumen->file_path, $dokumen->nama_file);
    }
}
