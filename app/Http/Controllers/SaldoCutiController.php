<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\SaldoCuti;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SaldoCutiController extends Controller
{
    public function index(Request $request): View
    {
        $tahun = $request->get('tahun', now()->year);

        $query = Pegawai::with(['jabatan', 'unitKerja',
            'saldoCuti' => fn ($q) => $q->where('tahun', $tahun)
        ])->where('status', 'aktif')->orderBy('nama');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $pegawai   = $query->paginate(20)->withQueryString();
        $tahunList = range(now()->year, now()->year - 3);

        return view('saldo-cuti.index', compact('pegawai', 'tahun', 'tahunList'));
    }

    public function edit(Pegawai $pegawai, Request $request): View
    {
        $tahun  = $request->get('tahun', now()->year);
        $saldo  = SaldoCuti::firstOrCreate(
            ['pegawai_id' => $pegawai->id, 'tahun' => $tahun],
            ['hak_cuti' => 12, 'carry_over' => 0, 'terpakai' => 0]
        );

        return view('saldo-cuti.edit', compact('pegawai', 'saldo', 'tahun'));
    }

    public function update(Request $request, Pegawai $pegawai): RedirectResponse
    {
        $request->validate([
            'tahun'       => 'required|integer|min:2000|max:2100',
            'hak_cuti'    => 'required|integer|min:0|max:365',
            'carry_over'  => 'required|integer|min:0|max:6',
        ], [
            'hak_cuti.required'   => 'Hak cuti wajib diisi.',
            'carry_over.max'      => 'Carry over maksimal 6 hari.',
        ]);

        SaldoCuti::updateOrCreate(
            ['pegawai_id' => $pegawai->id, 'tahun' => $request->tahun],
            ['hak_cuti' => $request->hak_cuti, 'carry_over' => $request->carry_over]
        );

        return redirect()->route('saldo-cuti.index', ['tahun' => $request->tahun])
            ->with('success', "Saldo cuti {$pegawai->nama} tahun {$request->tahun} berhasil diperbarui.");
    }

    public function show(Pegawai $pegawai): View
    {
        $saldo = SaldoCuti::where('pegawai_id', $pegawai->id)
            ->orderByDesc('tahun')
            ->get();

        return view('saldo-cuti.show', compact('pegawai', 'saldo'));
    }
}
