<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\RoutingTemplate;
use App\Models\RoutingTemplateStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RoutingTemplateController extends Controller
{
    // ─────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────
    public function index(): View
    {
        $templates = RoutingTemplate::with('stages')
            ->orderBy('kategori_jabatan')
            ->orderBy('nama')
            ->paginate(20);

        $kategoriOptions = Jabatan::daftarKategori();

        return view('routing-template.index', compact('templates', 'kategoriOptions'));
    }

    // ─────────────────────────────────────────────────────────
    // CREATE
    // ─────────────────────────────────────────────────────────
    public function create(): View
    {
        $kategoriOptions      = Jabatan::daftarKategori();
        $jenisTindakanOptions = [
            'approval'       => 'Menyetujui',
            'mengetahui'     => 'Mengetahui',
            'pertimbangan'   => 'Memberikan Pertimbangan',
            'final_approval' => 'Menyetujui (Final)',
        ];

        return view('routing-template.create', compact('kategoriOptions', 'jenisTindakanOptions'));
    }

    // ─────────────────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────────────────
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama'                              => 'required|string|max:100',
            'kategori_jabatan'                  => 'required|in:' . implode(',', array_keys(Jabatan::daftarKategori())),
            'aktif'                             => 'nullable|boolean',
            'stages'                            => 'required|array|min:1',
            'stages.*.label'                    => 'required|string|max:100',
            'stages.*.kategori_approver'        => 'required|in:' . implode(',', array_keys(Jabatan::daftarKategori())),
            'stages.*.jenis_tindakan'           => 'required|in:approval,mengetahui,pertimbangan,final_approval',
            'stages.*.perlu_ttd'                => 'nullable|boolean',
        ]);

        $this->validateTahapTerakhirFinal($request->stages);

        DB::transaction(function () use ($request) {
            $template = RoutingTemplate::create([
                'nama'             => $request->nama,
                'kategori_jabatan' => $request->kategori_jabatan,
                'aktif'            => $request->boolean('aktif', true),
            ]);

            foreach ($request->stages as $i => $stage) {
                RoutingTemplateStage::create([
                    'template_id'       => $template->id,
                    'urutan'            => $i + 1,
                    'label'             => $stage['label'],
                    'kategori_approver' => $stage['kategori_approver'],
                    'jenis_tindakan'    => $stage['jenis_tindakan'],
                    'perlu_ttd'         => isset($stage['perlu_ttd']) ? (bool) $stage['perlu_ttd'] : true,
                ]);
            }
        });

        return redirect()
            ->route('routing-template.index')
            ->with('success', 'Template routing berhasil disimpan.');
    }

    // ─────────────────────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────────────────────
    public function edit(RoutingTemplate $routingTemplate): View
    {
        $routingTemplate->load('stages');

        $kategoriOptions      = Jabatan::daftarKategori();
        $jenisTindakanOptions = [
            'approval'       => 'Menyetujui',
            'mengetahui'     => 'Mengetahui',
            'pertimbangan'   => 'Memberikan Pertimbangan',
            'final_approval' => 'Menyetujui (Final)',
        ];

        return view('routing-template.edit', compact(
            'routingTemplate',
            'kategoriOptions',
            'jenisTindakanOptions',
        ));
    }

    // ─────────────────────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────────────────────
    public function update(Request $request, RoutingTemplate $routingTemplate): RedirectResponse
    {
        $request->validate([
            'nama'                              => 'required|string|max:100',
            'kategori_jabatan'                  => 'required|in:' . implode(',', array_keys(Jabatan::daftarKategori())),
            'aktif'                             => 'nullable|boolean',
            'stages'                            => 'required|array|min:1',
            'stages.*.label'                    => 'required|string|max:100',
            'stages.*.kategori_approver'        => 'required|in:' . implode(',', array_keys(Jabatan::daftarKategori())),
            'stages.*.jenis_tindakan'           => 'required|in:approval,mengetahui,pertimbangan,final_approval',
            'stages.*.perlu_ttd'                => 'nullable|boolean',
        ]);

        $this->validateTahapTerakhirFinal($request->stages);

        DB::transaction(function () use ($request, $routingTemplate) {
            $routingTemplate->update([
                'nama'             => $request->nama,
                'kategori_jabatan' => $request->kategori_jabatan,
                'aktif'            => $request->boolean('aktif', true),
            ]);

            // Hapus stages lama dan buat ulang
            $routingTemplate->stages()->delete();

            foreach ($request->stages as $i => $stage) {
                RoutingTemplateStage::create([
                    'template_id'       => $routingTemplate->id,
                    'urutan'            => $i + 1,
                    'label'             => $stage['label'],
                    'kategori_approver' => $stage['kategori_approver'],
                    'jenis_tindakan'    => $stage['jenis_tindakan'],
                    'perlu_ttd'         => isset($stage['perlu_ttd']) ? (bool) $stage['perlu_ttd'] : true,
                ]);
            }
        });

        return redirect()
            ->route('routing-template.index')
            ->with('success', 'Template routing berhasil diperbarui.');
    }

    // ─────────────────────────────────────────────────────────
    // DESTROY
    // ─────────────────────────────────────────────────────────
    public function destroy(RoutingTemplate $routingTemplate): RedirectResponse
    {
        $routingTemplate->delete();

        return redirect()
            ->route('routing-template.index')
            ->with('success', 'Template routing berhasil dihapus.');
    }

    // ─────────────────────────────────────────────────────────
    // TOGGLE AKTIF
    // ─────────────────────────────────────────────────────────
    public function toggleAktif(RoutingTemplate $routingTemplate): RedirectResponse
    {
        $routingTemplate->update(['aktif' => ! $routingTemplate->aktif]);

        $status = $routingTemplate->aktif ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Template \"{$routingTemplate->nama}\" berhasil {$status}.");
    }

    // ─────────────────────────────────────────────────────────
    // PRIVATE HELPER
    // ─────────────────────────────────────────────────────────
    private function validateTahapTerakhirFinal(array $stages): void
    {
        $lastStage = end($stages);
        if (($lastStage['jenis_tindakan'] ?? '') !== 'final_approval') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'stages' => ['Tahap terakhir harus berjenis "Menyetujui (Final)".'],
            ]);
        }
    }
}
