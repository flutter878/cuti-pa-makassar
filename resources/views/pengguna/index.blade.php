<x-app-layout title="Manajemen Pengguna">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Manajemen Pengguna</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola akun pengguna sistem.</p>
        </div>
        <a href="{{ route('pengguna.create') }}"
           class="inline-flex items-center gap-2 bg-blue-900 hover:bg-blue-800 text-white text-sm
                  font-semibold px-4 py-2.5 rounded-lg transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Tambah Pengguna
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700 flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-5">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs text-gray-500 mb-1">Cari</label>
                <input type="text" name="cari" value="{{ request('cari') }}"
                       placeholder="Nama atau email..."
                       class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Role</label>
                <select name="role" class="border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->slug }}" {{ request('role') === $role->slug ? 'selected' : '' }}>
                            {{ ucfirst($role->name) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status" class="border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-blue-900 hover:bg-blue-800 text-white
                               text-sm font-semibold px-4 py-2.5 rounded-lg transition-colors">
                    Cari
                </button>
                @if(request()->hasAny(['cari', 'role', 'status']))
                    <a href="{{ route('pengguna.index') }}"
                       class="inline-flex items-center gap-1 border border-gray-200 text-gray-500 hover:bg-gray-50
                              text-sm px-3 py-2.5 rounded-lg transition-colors">
                        Reset
                    </a>
                @endif
            </div>
        </div>
    </form>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3 font-semibold">Pengguna</th>
                        <th class="px-5 py-3 font-semibold">Pegawai Terhubung</th>
                        <th class="px-5 py-3 font-semibold">Role</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold">Bergabung</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pengguna as $user)
                        <tr class="hover:bg-gray-50 {{ $user->status === 'nonaktif' ? 'opacity-60' : '' }}">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                        <span class="text-xs font-bold text-blue-800">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                @if($user->pegawai)
                                    <p class="text-sm text-gray-700">{{ $user->pegawai->nama }}</p>
                                    <p class="text-xs text-gray-400 font-mono">{{ $user->pegawai->nip }}</p>
                                @else
                                    <span class="text-xs text-gray-400 italic">Tidak terhubung</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $roleColor = match($user->role?->slug) {
                                        'superadmin' => 'bg-purple-100 text-purple-700',
                                        'admin'      => 'bg-blue-100 text-blue-700',
                                        default      => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $roleColor }}">
                                    {{ ucfirst($user->role?->name ?? '—') }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                @if($user->status === 'aktif')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-600">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-400 whitespace-nowrap">
                                {{ $user->created_at->format('d M Y') }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('pengguna.edit', $user) }}"
                                       class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</a>

                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('pengguna.toggle-status', $user) }}"
                                              onsubmit="return confirm('{{ $user->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }} akun ini?')">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="{{ $user->status === 'aktif'
                                                        ? 'text-red-500 hover:text-red-700'
                                                        : 'text-green-600 hover:text-green-800' }}
                                                    text-xs font-medium">
                                                {{ $user->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                                Tidak ada pengguna ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pengguna->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">{{ $pengguna->links() }}</div>
        @endif
    </div>
</x-app-layout>
