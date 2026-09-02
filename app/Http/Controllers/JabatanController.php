<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class JabatanController extends Controller
{
    public function index(): View
    {
        $jabatan = Jabatan::orderBy('nama_jabatan')->paginate(15);
        return view('jabatan.index', compact('jabatan'));
    }

    public function create(): View
    {
        return view('jabatan.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_jabatan' => 'required|string|max:100|unique:jabatan,nama_jabatan',
        ], [
            'nama_jabatan.required' => 'Nama jabatan wajib diisi.',
            'nama_jabatan.unique'   => 'Nama jabatan sudah ada.',
        ]);

        Jabatan::create($request->only('nama_jabatan'));

        return redirect()->route('jabatan.index')
            ->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function edit(Jabatan $jabatan): View
    {
        return view('jabatan.edit', compact('jabatan'));
    }

    public function update(Request $request, Jabatan $jabatan): RedirectResponse
    {
        $request->validate([
            'nama_jabatan' => 'required|string|max:100|unique:jabatan,nama_jabatan,' . $jabatan->id,
        ], [
            'nama_jabatan.required' => 'Nama jabatan wajib diisi.',
            'nama_jabatan.unique'   => 'Nama jabatan sudah ada.',
        ]);

        $jabatan->update($request->only('nama_jabatan'));

        return redirect()->route('jabatan.index')
            ->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function destroy(Jabatan $jabatan): RedirectResponse
    {
        // Cek apakah jabatan masih digunakan pegawai
        if ($jabatan->pegawai()->exists()) {
            return redirect()->route('jabatan.index')
                ->with('error', 'Jabatan tidak dapat dihapus karena masih digunakan oleh pegawai.');
        }

        $jabatan->delete();

        return redirect()->route('jabatan.index')
            ->with('success', 'Jabatan berhasil dihapus.');
    }
}
