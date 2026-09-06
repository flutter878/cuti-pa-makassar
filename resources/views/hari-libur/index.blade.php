<x-app-layout title="Hari Libur & Cuti Bersama">

    <div class="mb-5 flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Hari Libur & Cuti Bersama</h2>
            <p class="text-sm text-gray-500 mt-0.5">Tanggal libur yang tidak dihitung sebagai hari kerja</p>
        </div>
        <a href="{{ route('hari-libur.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Hari Libur
        </a>
    </div>

    {{-- Filter tahun --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 mb-5">
        <form method="GET" action="{{ route('hari-libur.index') }}" class="flex gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tahun</label>
                <select name="tahun" class="text-sm border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-blue-500">
                    <option value="">Semua</option>
                    @foreach($tahunList as $y)
                        <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-1.5 rounded-lg transition-colors">
                Filter
            </button>
            @if(request('tahun'))
                <a href="{{ route('hari-libur.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-3 py-1.5 rounded-lg transition-colors">Reset</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wide border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left">No</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Hari</th>
                        <th class="px-4 py-3 text-left">Nama Libur</th>
                        <th class="px-4 py-3 text-left">Keterangan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($hariLibur as $i => $item)
                        <tr class="hover:bg-gray-50 transition-colors {{ !$item->aktif ? 'opacity-50' : '' }}">
                            <td class="px-4 py-3 text-gray-500">{{ $hariLibur->firstItem() + $i }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800">
                                {{ $item->tanggal->format('d F Y') }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $item->tanggal->locale('id')->dayName }}
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $item->nama }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $item->keterangan ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                <form method="POST" action="{{ route('hari-libur.toggle', $item) }}">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold transition-colors
                                            {{ $item->aktif
                                                ? 'bg-green-100 text-green-700 hover:bg-green-200'
                                                : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                        {{ $item->aktif ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-3">
                                    <a href="{{ route('hari-libur.edit', $item) }}"
                                       class="text-xs text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                                    <form method="POST" action="{{ route('hari-libur.destroy', $item) }}"
                                          onsubmit="return confirm('Yakin hapus hari libur {{ $item->nama }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                                <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-sm">Belum ada data hari libur.</p>
                                <a href="{{ route('hari-libur.create') }}" class="text-xs text-blue-600 hover:underline mt-1 inline-block">Tambah sekarang</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($hariLibur->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $hariLibur->links() }}</div>
        @endif
    </div>

</x-app-layout>
