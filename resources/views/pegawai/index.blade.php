<x-app-layout title="Data Pegawai">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Data Pegawai</h2>
            <p class="text-sm text-gray-500">Kelola data seluruh pegawai</p>
        </div>
        <a href="{{ route('pegawai.create') }}"
           class="inline-flex items-center gap-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Pegawai
        </a>
    </div>

    {{-- Filter --}}
    <form method="GET" class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari nama / NIP..."
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

            <select name="jabatan_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Jabatan</option>
                @foreach($jabatan as $j)
                    <option value="{{ $j->id }}" {{ request('jabatan_id') == $j->id ? 'selected' : '' }}>
                        {{ $j->nama_jabatan }}
                    </option>
                @endforeach
            </select>

            <select name="unit_kerja_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Unit Kerja</option>
                @foreach($unitKerja as $u)
                    <option value="{{ $u->id }}" {{ request('unit_kerja_id') == $u->id ? 'selected' : '' }}>
                        {{ $u->nama_unit }}
                    </option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <select name="status" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                <button type="submit"
                        class="bg-blue-900 hover:bg-blue-800 text-white text-sm px-4 py-2 rounded-lg transition-colors">
                    Cari
                </button>
                @if(request()->hasAny(['search','jabatan_id','unit_kerja_id','status']))
                    <a href="{{ route('pegawai.index') }}"
                       class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm px-3 py-2 rounded-lg transition-colors">
                        Reset
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- Tabel --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs font-semibold tracking-wider">
                <tr>
                    <th class="px-4 py-3 text-left w-10">#</th>
                    <th class="px-4 py-3 text-left">NIP</th>
                    <th class="px-4 py-3 text-left">Nama</th>
                    <th class="px-4 py-3 text-left hidden lg:table-cell">Jabatan</th>
                    <th class="px-4 py-3 text-left hidden lg:table-cell">Unit Kerja</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Akun</th>
                    <th class="px-4 py-3 text-center w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($pegawai as $p)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-gray-400">{{ $pegawai->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $p->nip }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $p->nama }}</td>
                        <td class="px-4 py-3 text-gray-600 hidden lg:table-cell">{{ $p->jabatan?->nama_jabatan ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 hidden lg:table-cell">{{ $p->unitKerja?->nama_unit ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full
                                {{ $p->status === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ ucfirst($p->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($p->user)
                                <span class="text-xs text-green-600 font-medium">Ada</span>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('pegawai.show', $p) }}"
                                   class="text-gray-500 hover:text-gray-700 text-xs font-medium">Detail</a>
                                <a href="{{ route('pegawai.edit', $p) }}"
                                   class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</a>
                                <form method="POST" action="{{ route('pegawai.destroy', $p) }}"
                                      onsubmit="return confirm('Nonaktifkan pegawai ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="text-red-500 hover:text-red-700 text-xs font-medium">
                                        Nonaktifkan
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-8 text-center text-gray-400 italic">
                            Belum ada data pegawai.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($pegawai->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">
                {{ $pegawai->links() }}
            </div>
        @endif
    </div>

</x-app-layout>
