<?php

namespace App\Http\Controllers;

use App\Models\JenisCuti;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JenisCutiController extends Controller
{
    public function index(): View
    {
        $jenisCuti = JenisCuti::orderBy('nama')->paginate(15);
        return view('jenis-cuti.index', compact('jenisCuti'));
    }

    public function create(): View
    {
        return view('jenis-cuti.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama'                 => 'required|string|max:100',
            'kode'                 => 'required|string|max:10|unique:jenis_cuti,kode',
            'batas_hari'           => 'nullable|integer|min:1|max:365',
            'membutuhkan_lampiran' => 'boolean',
            'mengurangi_saldo'     => 'boolean',
            'status'               => 'required|in:aktif,nonaktif',
        ], [
            'nama.required'   => 'Nama jenis cuti wajib diisi.',
            'kode.required'   => 'Kode jenis cuti wajib diisi.',
            'kode.unique'     => 'Kode jenis cuti sudah digunakan.',
            'batas_hari.min'  => 'Batas hari minimal 1.',
            'batas_hari.max'  => 'Batas hari maksimal 365.',
        ]);

        JenisCuti::create([
            'nama'                 => $request->nama,
            'kode'                 => strtoupper($request->kode),
            'membutuhkan_lampiran' => $request->boolean('membutuhkan_lampiran'),
            'mengurangi_saldo'     => $request->boolean('mengurangi_saldo'),
            'batas_hari'           => $request->filled('batas_hari') ? $request->batas_hari : null,
            'status'               => $request->status,
        ]);

        return redirect()->route('jenis-cuti.index')
            ->with('success', 'Jenis cuti berhasil ditambahkan.');
    }

    public function edit(JenisCuti $jenisCuti): View
    {
        return view('jenis-cuti.edit', compact('jenisCuti'));
    }

    public function update(Request $request, JenisCuti $jenisCuti): RedirectResponse
    {
        $request->validate([
            'nama'                 => 'required|string|max:100',
            'kode'                 => 'required|string|max:10|unique:jenis_cuti,kode,' . $jenisCuti->id,
            'batas_hari'           => 'nullable|integer|min:1|max:365',
            'membutuhkan_lampiran' => 'boolean',
            'mengurangi_saldo'     => 'boolean',
            'status'               => 'required|in:aktif,nonaktif',
        ], [
            'nama.required'   => 'Nama jenis cuti wajib diisi.',
            'kode.required'   => 'Kode jenis cuti wajib diisi.',
            'kode.unique'     => 'Kode jenis cuti sudah digunakan.',
        ]);

        $jenisCuti->update([
            'nama'                 => $request->nama,
            'kode'                 => strtoupper($request->kode),
            'membutuhkan_lampiran' => $request->boolean('membutuhkan_lampiran'),
            'mengurangi_saldo'     => $request->boolean('mengurangi_saldo'),
            'batas_hari'           => $request->filled('batas_hari') ? $request->batas_hari : null,
            'status'               => $request->status,
        ]);

        return redirect()->route('jenis-cuti.index')
            ->with('success', 'Jenis cuti berhasil diperbarui.');
    }

    public function destroy(JenisCuti $jenisCuti): RedirectResponse
    {
        // Cek apakah jenis cuti masih digunakan dalam pengajuan
        $digunakan = \App\Models\Cuti::where('jenis_cuti_id', $jenisCuti->id)->exists();
        if ($digunakan) {
            return redirect()->route('jenis-cuti.index')
                ->with('error', "Jenis cuti \"{$jenisCuti->nama}\" tidak dapat dihapus karena sudah digunakan dalam pengajuan.");
        }

        $jenisCuti->delete();

        return redirect()->route('jenis-cuti.index')
            ->with('success', 'Jenis cuti berhasil dihapus.');
    }
}
