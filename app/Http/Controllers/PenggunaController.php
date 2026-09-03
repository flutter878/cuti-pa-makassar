<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PenggunaController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with(['role', 'pegawai'])
            ->orderByDesc('created_at');

        if ($request->filled('role')) {
            $query->whereHas('role', fn($q) => $q->where('slug', $request->role));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('cari')) {
            $cari = $request->string('cari')->trim()->toString();
            $query->where(function ($q) use ($cari) {
                $q->where('name', 'like', "%{$cari}%")
                  ->orWhere('email', 'like', "%{$cari}%");
            });
        }

        $pengguna = $query->paginate(15)->withQueryString();
        $roles    = Role::orderBy('name')->get();

        return view('pengguna.index', compact('pengguna', 'roles'));
    }

    public function create(): View
    {
        $roles   = Role::orderBy('name')->get();
        $pegawai = Pegawai::whereDoesntHave('user')
            ->where('status', 'aktif')
            ->orderBy('nama')
            ->get();

        return view('pengguna.create', compact('roles', 'pegawai'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'email'      => 'required|email|max:150|unique:users,email',
            'role_id'    => 'required|exists:roles,id',
            'pegawai_id' => 'nullable|exists:pegawai,id|unique:users,pegawai_id',
            'password'   => ['required', Password::min(8)],
            'status'     => 'required|in:aktif,nonaktif',
        ], [
            'name.required'           => 'Nama pengguna wajib diisi.',
            'email.required'          => 'Email wajib diisi.',
            'email.unique'            => 'Email sudah digunakan.',
            'role_id.required'        => 'Role wajib dipilih.',
            'pegawai_id.unique'       => 'Pegawai ini sudah memiliki akun.',
            'password.required'       => 'Password wajib diisi.',
            'password.min'            => 'Password minimal 8 karakter.',
        ]);

        User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'role_id'    => $request->role_id,
            'pegawai_id' => $request->filled('pegawai_id') ? $request->pegawai_id : null,
            'password'   => Hash::make($request->password),
            'status'     => $request->status,
        ]);

        return redirect()->route('pengguna.index')
            ->with('success', "Pengguna {$request->name} berhasil ditambahkan.");
    }

    public function edit(User $user): View
    {
        $roles   = Role::orderBy('name')->get();
        // Pegawai yang belum punya user, atau pegawai milik user ini sendiri
        $pegawai = Pegawai::where(function ($q) use ($user) {
                $q->whereDoesntHave('user')
                  ->orWhere('id', $user->pegawai_id);
            })
            ->where('status', 'aktif')
            ->orderBy('nama')
            ->get();

        return view('pengguna.edit', compact('user', 'roles', 'pegawai'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'email'      => 'required|email|max:150|unique:users,email,' . $user->id,
            'role_id'    => 'required|exists:roles,id',
            'pegawai_id' => 'nullable|exists:pegawai,id|unique:users,pegawai_id,' . $user->id,
            'password'   => ['nullable', Password::min(8)],
            'status'     => 'required|in:aktif,nonaktif',
        ], [
            'name.required'     => 'Nama pengguna wajib diisi.',
            'email.required'    => 'Email wajib diisi.',
            'email.unique'      => 'Email sudah digunakan.',
            'role_id.required'  => 'Role wajib dipilih.',
            'pegawai_id.unique' => 'Pegawai ini sudah memiliki akun lain.',
        ]);

        // Proteksi: superadmin tidak bisa diubah rolenya kecuali oleh superadmin
        if ($user->isSuperadmin() && ! auth()->user()->isSuperadmin()) {
            return back()->with('error', 'Anda tidak memiliki izin untuk mengubah akun superadmin.');
        }

        $data = [
            'name'       => $request->name,
            'email'      => $request->email,
            'role_id'    => $request->role_id,
            'pegawai_id' => $request->filled('pegawai_id') ? $request->pegawai_id : null,
            'status'     => $request->status,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('pengguna.index')
            ->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    /**
     * Toggle status aktif/nonaktif pengguna.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        // Tidak boleh menonaktifkan diri sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        // Proteksi superadmin
        if ($user->isSuperadmin() && ! auth()->user()->isSuperadmin()) {
            return back()->with('error', 'Anda tidak memiliki izin untuk mengubah status akun superadmin.');
        }

        $user->update([
            'status' => $user->status === 'aktif' ? 'nonaktif' : 'aktif',
        ]);

        $statusBaru = $user->fresh()->status;
        $pesan = $statusBaru === 'aktif'
            ? "Akun {$user->name} berhasil diaktifkan."
            : "Akun {$user->name} berhasil dinonaktifkan.";

        return back()->with('success', $pesan);
    }
}
