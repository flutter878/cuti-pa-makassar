<?php

namespace App\Http\Controllers;

use App\Models\ApprovalStage;
use App\Models\Cuti;
use App\Services\ApprovalWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function __construct(
        private readonly ApprovalWorkflowService $workflowService
    ) {}

    // ─────────────────────────────────────────────────────────
    // INDEX — daftar pengajuan yang perlu diproses user login
    // ─────────────────────────────────────────────────────────
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Ambil semua approval_stages yang ditugaskan ke user ini
        // Termasuk yang sudah diproses untuk riwayat
        $stagesQuery = ApprovalStage::with(['cuti.pegawai.jabatan', 'cuti.jenisCuti'])
            ->where('user_id', $user->id)
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected', 'returned', 'skipped')")
            ->orderByDesc('updated_at');

        if ($request->filled('status')) {
            $stagesQuery->where('status', $request->status);
        }

        $stages = $stagesQuery->paginate(15)->withQueryString();

        // Hitung pending untuk badge
        $jumlahPending = ApprovalStage::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        return view('approval.index', compact('stages', 'jumlahPending'));
    }

    // ─────────────────────────────────────────────────────────
    // SHOW — detail pengajuan + form tindakan
    // ─────────────────────────────────────────────────────────
    public function show(Cuti $cuti): View
    {
        $user = Auth::user();

        $cuti->load([
            'jenisCuti',
            'pegawai.jabatan',
            'pegawai.unitKerja',
            'saldoDetail.saldoCuti',
            'dokumen',
            'approvalStages.user.pegawai.jabatan',
            'auditTrail.user',
        ]);

        // Cari tahap aktif untuk user ini
        $tahapAktif = $this->workflowService->getTahapAktifUntukUser($cuti, $user);

        // Apakah user ini punya akses lihat (pernah ditugaskan atau sedang ditugaskan)
        $pernahDitugaskan = $cuti->approvalStages()
            ->where('user_id', $user->id)
            ->exists();

        // Admin/superadmin bisa lihat semua
        $isAdmin = $user->isAdminOrSuperadmin();

        if (! $isAdmin && ! $pernahDitugaskan) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        return view('approval.show', compact(
            'cuti',
            'tahapAktif',
            'isAdmin',
        ));
    }

    // ─────────────────────────────────────────────────────────
    // SETUJUI — approve tahap ini
    // ─────────────────────────────────────────────────────────
    public function setujui(Request $request, Cuti $cuti, ApprovalStage $stage): RedirectResponse
    {
        $user = Auth::user();

        try {
            $this->workflowService->prosesTindakan(
                cuti:    $cuti,
                user:    $user,
                stage:   $stage,
                aksi:    'approve',
                catatan: $request->catatan,
                ip:      $request->ip(),
            );

            $label = $stage->jenisTindakanLabel();

            return redirect()
                ->route('approval.show', $cuti)
                ->with('success', "Berhasil: {$label} untuk pengajuan {$cuti->nomor_pengajuan}.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }
    }

    // ─────────────────────────────────────────────────────────
    // TOLAK — reject tahap ini, workflow berhenti
    // ─────────────────────────────────────────────────────────
    public function tolak(Request $request, Cuti $cuti, ApprovalStage $stage): RedirectResponse
    {
        $request->validate([
            'catatan' => 'required|string|min:3|max:1000',
        ], ['catatan.required' => 'Alasan penolakan wajib diisi.']);

        $user = Auth::user();

        try {
            $this->workflowService->prosesTindakan(
                cuti:    $cuti,
                user:    $user,
                stage:   $stage,
                aksi:    'reject',
                catatan: $request->catatan,
                ip:      $request->ip(),
            );

            return redirect()
                ->route('approval.show', $cuti)
                ->with('success', "Pengajuan {$cuti->nomor_pengajuan} telah ditolak.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }
    }

    // ─────────────────────────────────────────────────────────
    // KEMBALIKAN — return ke pemohon untuk perbaikan
    // ─────────────────────────────────────────────────────────
    public function kembalikan(Request $request, Cuti $cuti, ApprovalStage $stage): RedirectResponse
    {
        $request->validate([
            'catatan' => 'required|string|min:3|max:1000',
        ], ['catatan.required' => 'Catatan pengembalian wajib diisi.']);

        $user = Auth::user();

        try {
            $this->workflowService->prosesTindakan(
                cuti:    $cuti,
                user:    $user,
                stage:   $stage,
                aksi:    'return',
                catatan: $request->catatan,
                ip:      $request->ip(),
            );

            return redirect()
                ->route('approval.show', $cuti)
                ->with('success', "Pengajuan {$cuti->nomor_pengajuan} dikembalikan ke pemohon.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }
    }
}
