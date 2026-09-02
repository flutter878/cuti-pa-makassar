<x-app-layout title="Data Unit Kerja">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Data Unit Kerja</h2>
            <p class="text-sm text-gray-500">Kelola daftar unit kerja</p>
        </div>
        <a href="{{ route('unit-kerja.create') }}"
           class="inline-flex items-center gap-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Unit Kerja
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs font-semibold tracking-wider">
                <tr>
                    <th class="px-5 py-3 text-left w-12">#</th>
                    <th class="px-5 py-3 text-left">Nama Unit Kerja</th>
                    <th class="px-5 py-3 text-left">Jumlah Pegawai</th>
                    <th class="px-5 py-3 text-center w-28">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($unitKerja as $item)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3 text-gray-400">{{ $unitKerja->firstItem() + $loop->index }}</td>
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $item->nama_unit }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $item->pegawai()->count() }} pegawai</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('unit-kerja.edit', $item) }}"
                                   class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</a>
                                <form method="POST" action="{{ route('unit-kerja.destroy', $item) }}"
                                      onsubmit="return confirm('Hapus unit kerja ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="text-red-500 hover:text-red-700 text-xs font-medium">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-gray-400 italic">
                            Belum ada data unit kerja.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($unitKerja->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">
                {{ $unitKerja->links() }}
            </div>
        @endif
    </div>

</x-app-layout>
