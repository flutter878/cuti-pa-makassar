<x-app-layout title="Saldo Cuti Pegawai">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Saldo Cuti Pegawai</h2>
            <p class="text-sm text-gray-500">Pantau dan kelola saldo cuti seluruh pegawai</p>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 mb-4">
        <div class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Tahun</label>
                <select name="tahun" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($tahunList as $t)
                        <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-48">
                <label class="block text-xs text-gray-500 mb-1">Cari Pegawai</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Nama atau NIP..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit"
                    class="bg-blue-900 hover:bg-blue-800 text-white text-sm px-4 py-2 rounded-lg transition-colors">
                Tampilkan
            </button>
        </div>
    </form>

    {{-- Tabel --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs font-semibold tracking-wider">
                <tr>
                    <th class="px-4 py-3 text-left w-10">#</th>
                    <th class="px-4 py-3 text-left">NIP</th>
                    <th class="px-4 py-3 text-left">Nama Pegawai</th>
                    <th class="px-4 py-3 text-left hidden md:table-cell">Jabatan</th>
                    <th class="px-4 py-3 text-center">Hak</th>
                    <th class="px-4 py-3 text-center">Carry Over</th>
                    <th class="px-4 py-3 text-center">Terpakai</th>
                    <th class="px-4 py-3 text-center">Sisa</th>
                    <th class="px-4 py-3 text-center w-20">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($pegawai as $p)
                    @php $s = $p->saldoCuti->first(); @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-gray-400">{{ $pegawai->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $p->nip }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $p->nama }}</td>
                        <td class="px-4 py-3 text-gray-600 hidden md:table-cell">{{ $p->jabatan?->nama_jabatan ?? '—' }}</td>
                        <td class="px-4 py-3 text-center text-gray-700">{{ $s?->hak_cuti ?? '—' }}</td>
                        <td class="px-4 py-3 text-center text-blue-600">{{ $s?->carry_over ?? '—' }}</td>
                        <td class="px-4 py-3 text-center text-red-500">{{ $s?->terpakai ?? '—' }}</td>
                        <td class="px-4 py-3 text-center font-semibold text-green-600">{{ $s?->sisa ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('saldo-cuti.edit', [$p, 'tahun' => $tahun]) }}"
                               class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-5 py-8 text-center text-gray-400 italic">
                            Tidak ada data pegawai.
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
