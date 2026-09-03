<x-app-layout title="Jenis Cuti">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Jenis Cuti</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola jenis-jenis cuti yang tersedia.</p>
        </div>
        <a href="{{ route('jenis-cuti.create') }}"
           class="inline-flex items-center gap-2 bg-blue-900 hover:bg-blue-800 text-white text-sm
                  font-semibold px-4 py-2.5 rounded-lg transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Tambah Jenis Cuti
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3 font-semibold">Nama Jenis Cuti</th>
                        <th class="px-5 py-3 font-semibold">Kode</th>
                        <th class="px-5 py-3 font-semibold">Batas Hari</th>
                        <th class="px-5 py-3 font-semibold">Kurangi Saldo</th>
                        <th class="px-5 py-3 font-semibold">Butuh Lampiran</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($jenisCuti as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 font-medium text-gray-800">{{ $item->nama }}</td>
                            <td class="px-5 py-4">
                                <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">
                                    {{ $item->kode }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                {{ $item->batas_hari ? $item->batas_hari . ' hari' : '—' }}
                            </td>
                            <td class="px-5 py-4">
                                @if($item->mengurangi_saldo)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">Ya</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Tidak</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($item->membutuhkan_lampiran)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-700">Ya</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Tidak</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($item->status === 'aktif')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Aktif</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-600">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('jenis-cuti.edit', $item) }}"
                                       class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</a>
                                    <form method="POST" action="{{ route('jenis-cuti.destroy', $item) }}"
                                          onsubmit="return confirm('Hapus jenis cuti ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                                Belum ada jenis cuti.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($jenisCuti->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">{{ $jenisCuti->links() }}</div>
        @endif
    </div>
</x-app-layout>
