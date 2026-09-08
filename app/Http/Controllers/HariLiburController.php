<?php

namespace App\Http\Controllers;

use App\Models\HariLibur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HariLiburController extends Controller
{
    public function index(Request $request): View
    {
        $query = HariLibur::orderBy('tanggal', 'desc');

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }

        $hariLibur = $query->paginate(20)->withQueryString();
        $tahunList = range(now()->year + 1, now()->year - 2);

        return view('hari-libur.index', compact('hariLibur', 'tahunList'));
    }

    public function create(): View
    {
        return view('hari-libur.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'tanggal'     => 'required|date|unique:hari_libur,tanggal',
            'nama'        => 'required|string|max:100',
            'keterangan'  => 'nullable|string|max:255',
            'aktif'       => 'boolean',
        ], [
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.unique'   => 'Tanggal ini sudah terdaftar sebagai hari libur.',
            'nama.required'    => 'Nama hari libur wajib diisi.',
        ]);

        HariLibur::create([
            'tanggal'    => $request->tanggal,
            'nama'       => $request->nama,
            'keterangan' => $request->keterangan,
            'aktif'      => $request->boolean('aktif', true),
        ]);

        return redirect()->route('hari-libur.index')
            ->with('success', "Hari libur '{$request->nama}' berhasil ditambahkan.");
    }

    public function edit(HariLibur $hariLibur): View
    {
        return view('hari-libur.edit', compact('hariLibur'));
    }

    public function update(Request $request, HariLibur $hariLibur): RedirectResponse
    {
        $request->validate([
            'tanggal'    => 'required|date|unique:hari_libur,tanggal,' . $hariLibur->id,
            'nama'       => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:255',
            'aktif'      => 'boolean',
        ], [
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.unique'   => 'Tanggal ini sudah terdaftar sebagai hari libur lain.',
            'nama.required'    => 'Nama hari libur wajib diisi.',
        ]);

        $hariLibur->update([
            'tanggal'    => $request->tanggal,
            'nama'       => $request->nama,
            'keterangan' => $request->keterangan,
            'aktif'      => $request->boolean('aktif', true),
        ]);

        return redirect()->route('hari-libur.index')
            ->with('success', "Hari libur '{$hariLibur->nama}' berhasil diperbarui.");
    }

    public function destroy(HariLibur $hariLibur): RedirectResponse
    {
        $nama = $hariLibur->nama;
        $hariLibur->delete();

        return redirect()->route('hari-libur.index')
            ->with('success', "Hari libur '{$nama}' berhasil dihapus.");
    }

    /** Toggle status aktif/nonaktif */
    public function toggleAktif(HariLibur $hariLibur): RedirectResponse
    {
        $hariLibur->update(['aktif' => ! $hariLibur->aktif]);

        $status = $hariLibur->aktif ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Hari libur '{$hariLibur->nama}' berhasil {$status}.");
    }
}
