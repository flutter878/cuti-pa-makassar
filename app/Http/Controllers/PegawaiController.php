<?php

namespace App\Http\Controllers;

use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\Role;
use App\Models\SaldoCuti;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PegawaiController extends Controller
{
    public function index(Request $request): View
    {
        $query = Pegawai::with(['jabatan', 'unitKerja', 'user'])
            ->orderBy('nama');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('jabatan_id')) {
            $query->where('jabatan_id', $request->jabatan_id);
        }

        if ($request->filled('unit_kerja_id')) {
            $query->where('unit_kerja_id', $request->unit_kerja_id);
        }

        $pegawai  = $query->paginate(15)->withQueryString();
        $jabatan  = Jabatan::orderBy('nama_jabatan')->get();
        $unitKerja = UnitKerja::orderBy('nama_unit')->get();

        return view('pegawai.index', compact('pegawai', 'jabatan', 'unitKerja'));
    }

    public function create(): View
    {
        $jabatan         = Jabatan::orderBy('nama_jabatan')->get();
        $unitKerja       = UnitKerja::orderBy('nama_unit')->get();
        $calonAtasan     = Pegawai::whereHas('jabatan', function ($q) {
            $q->whereIn('nama_jabatan', array_merge(
                config('approver.jabatan_atasan', []),
                config('approver.jabatan_ketua', [])
            ));
        })->where('status', 'aktif')->orderBy('nama')->get();

        return view('pegawai.create', compact('jabatan', 'unitKerja', 'calonAtasan'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nip'                => 'required|string|max:20|unique:pegawai,nip',
            'nama'               => 'required|string|max:100',
            'email'              => 'nullable|email|max:100|unique:pegawai,email',
            'no_telepon'         => 'nullable|string|max:20',
            'jabatan_id'         => 'nullable|exists:jabatan,id',
            'unit_kerja_id'      => 'nullable|exists:unit_kerja,id',
            'atasan_langsung_id' => 'nullable|exists:pegawai,id',
            'status'             => 'required|in:aktif,nonaktif',
            // Akun user (opsional saat tambah)
            'buat_akun' => 'nullable|boolean',
            'role_id'   => 'required_if:buat_akun,1|nullable|exists:roles,id',
        ], [
            'nip.unique'   => 'NIP sudah terdaftar.',
            'email.unique' => 'Email pegawai sudah digunakan.',
            'role_id.required_if' => 'Role wajib dipilih jika membuat akun.',
        ]);

        DB::transaction(function () use ($request) {
            $pegawai = Pegawai::create([
                'nip'                => $request->nip,
                'nama'               => $request->nama,
                'email'              => $request->email,
                'no_telepon'         => $request->no_telepon,
                'jabatan_id'         => $request->jabatan_id,
                'unit_kerja_id'      => $request->unit_kerja_id,
                'atasan_langsung_id' => $request->filled('atasan_langsung_id') ? $request->atasan_langsung_id : null,
                'status'             => $request->status,
            ]);

            // Inisialisasi saldo cuti tahun berjalan
            SaldoCuti::inisialisasi($pegawai->id);

            // Buat akun user jika diminta
            // Login: NIP | Password default: NIP (pegawai wajib ganti setelah login pertama)
            if ($request->boolean('buat_akun')) {
                // Email untuk akun: pakai email pegawai jika ada, atau buat internal nip@internal
                $emailAkun = $pegawai->email ?: ($pegawai->nip . '@internal.pa-makassar.go.id');

                User::create([
                    'role_id'    => $request->role_id,
                    'pegawai_id' => $pegawai->id,
                    'name'       => $pegawai->nama,
                    'email'      => $emailAkun,
                    'password'   => Hash::make($pegawai->nip),
                    'status'     => $pegawai->status,
                ]);
            }
        });

        return redirect()->route('pegawai.index')
            ->with('success', 'Data pegawai berhasil ditambahkan. Akun login: NIP sebagai username & password.');
    }

    public function show(Pegawai $pegawai): View
    {
        $pegawai->load(['jabatan', 'unitKerja', 'user.role']);
        $saldo = SaldoCuti::where('pegawai_id', $pegawai->id)
            ->orderByDesc('tahun')
            ->get();

        return view('pegawai.show', compact('pegawai', 'saldo'));
    }

    public function edit(Pegawai $pegawai): View
    {
        $jabatan     = Jabatan::orderBy('nama_jabatan')->get();
        $unitKerja   = UnitKerja::orderBy('nama_unit')->get();
        $roles       = Role::orderBy('name')->get();
        $calonAtasan = Pegawai::whereHas('jabatan', function ($q) {
                $q->whereIn('nama_jabatan', array_merge(
                    config('approver.jabatan_atasan', []),
                    config('approver.jabatan_ketua', [])
                ));
            })
            ->where('status', 'aktif')
            ->where('id', '!=', $pegawai->id) // tidak bisa jadi atasan diri sendiri
            ->orderBy('nama')
            ->get();

        $pegawai->load('user');

        return view('pegawai.edit', compact('pegawai', 'jabatan', 'unitKerja', 'roles', 'calonAtasan'));
    }

    public function update(Request $request, Pegawai $pegawai): RedirectResponse
    {
        $request->validate([
            'nip'                => 'required|string|max:20|unique:pegawai,nip,' . $pegawai->id,
            'nama'               => 'required|string|max:100',
            'email'              => 'nullable|email|max:100|unique:pegawai,email,' . $pegawai->id,
            'no_telepon'         => 'nullable|string|max:20',
            'jabatan_id'         => 'nullable|exists:jabatan,id',
            'unit_kerja_id'      => 'nullable|exists:unit_kerja,id',
            'atasan_langsung_id' => 'nullable|exists:pegawai,id|different:id',
            'status'             => 'required|in:aktif,nonaktif',
        ], [
            'nip.unique'         => 'NIP sudah digunakan pegawai lain.',
            'email.unique'       => 'Email sudah digunakan pegawai lain.',
            'atasan_langsung_id.different' => 'Pegawai tidak bisa menjadi atasan diri sendiri.',
        ]);

        DB::transaction(function () use ($request, $pegawai) {
            $pegawai->update([
                'nip'                => $request->nip,
                'nama'               => $request->nama,
                'email'              => $request->email,
                'no_telepon'         => $request->no_telepon,
                'jabatan_id'         => $request->jabatan_id,
                'unit_kerja_id'      => $request->unit_kerja_id,
                'atasan_langsung_id' => $request->filled('atasan_langsung_id') ? $request->atasan_langsung_id : null,
                'status'             => $request->status,
            ]);

            // Sinkronisasi status ke akun user terkait
            if ($pegawai->user) {
                $pegawai->user->update([
                    'name'   => $pegawai->nama,
                    'status' => $pegawai->status,
                ]);
            }
        });

        return redirect()->route('pegawai.index')
            ->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function destroy(Pegawai $pegawai): RedirectResponse
    {
        // Soft delete: nonaktifkan saja, tidak hapus permanen
        $pegawai->update(['status' => 'nonaktif']);

        if ($pegawai->user) {
            $pegawai->user->update(['status' => 'nonaktif']);
        }

        return redirect()->route('pegawai.index')
            ->with('success', 'Pegawai berhasil dinonaktifkan.');
    }
}
