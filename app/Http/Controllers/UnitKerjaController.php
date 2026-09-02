<?php

namespace App\Http\Controllers;

use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UnitKerjaController extends Controller
{
    public function index(): View
    {
        $unitKerja = UnitKerja::orderBy('nama_unit')->paginate(15);
        return view('unit-kerja.index', compact('unitKerja'));
    }

    public function create(): View
    {
        return view('unit-kerja.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_unit' => 'required|string|max:150|unique:unit_kerja,nama_unit',
        ], [
            'nama_unit.required' => 'Nama unit kerja wajib diisi.',
            'nama_unit.unique'   => 'Nama unit kerja sudah ada.',
        ]);

        UnitKerja::create($request->only('nama_unit'));

        return redirect()->route('unit-kerja.index')
            ->with('success', 'Unit kerja berhasil ditambahkan.');
    }

    public function edit(UnitKerja $unitKerja): View
    {
        return view('unit-kerja.edit', compact('unitKerja'));
    }

    public function update(Request $request, UnitKerja $unitKerja): RedirectResponse
    {
        $request->validate([
            'nama_unit' => 'required|string|max:150|unique:unit_kerja,nama_unit,' . $unitKerja->id,
        ], [
            'nama_unit.required' => 'Nama unit kerja wajib diisi.',
            'nama_unit.unique'   => 'Nama unit kerja sudah ada.',
        ]);

        $unitKerja->update($request->only('nama_unit'));

        return redirect()->route('unit-kerja.index')
            ->with('success', 'Unit kerja berhasil diperbarui.');
    }

    public function destroy(UnitKerja $unitKerja): RedirectResponse
    {
        if ($unitKerja->pegawai()->exists()) {
            return redirect()->route('unit-kerja.index')
                ->with('error', 'Unit kerja tidak dapat dihapus karena masih digunakan oleh pegawai.');
        }

        $unitKerja->delete();

        return redirect()->route('unit-kerja.index')
            ->with('success', 'Unit kerja berhasil dihapus.');
    }
}
