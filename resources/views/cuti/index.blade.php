<x-app-layout title="Riwayat Cuti">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Riwayat Cuti</h2>
            <p class="text-sm text-gray-500">Daftar seluruh pengajuan cuti Anda</p>
        </div>
        <a href="{{ route('cuti.create') }}"
           class="inline-flex items-center gap-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajukan Cuti
        </a>
    </div>

    {{-- Filter --}}
    <form method="GET" class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 mb-4">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <select name="tahun" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Tahun</option>
                @foreach($tahunList as $t)
                    <option value="{{ $t }}" {{ request('tahun') == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>

            <select name="jenis_cuti_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Jenis</option>
                @foreach($jenisCuti as $jc)
                    <option value="{{ $jc->id }}" {{ request('jenis_cuti_id') == $jc->id ? 'selected' : '' }}>{{ $jc->nama }}</option>
                @endforeach
            </select>

            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Status</option>
                @foreach(['diajukan','diproses','menunggu_persetujuan','disetujui','ditolak','dibatalkan'] as $st)
                    <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>
                        {{ ucwords(str_replace('_', ' ', $st)) }}
                    </option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-blue-900 hover:bg-blue-800 text-white text-sm px-3 py-2 rounded-lg transition-colors">Cari</button>
                @if(request()->hasAny(['tahun','jenis_cuti_id','status']))
                    <a href="{{ route('cuti.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm px-3 py-2 rounded-lg">Reset</a>
                @endif
            </div>
        </div>
    </form>

    {{-- Tabel --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs font-semibold tracking-wider">
                <tr>
                    <th class="px-4 py-3 text-left">No. Pengajuan</th>
                    <th class="px-4 py-3 text-left">Jenis Cuti</th>
                    <th class="px-4 py-3 text-left hidden md:table-cell">Tanggal</th>
                    <th class="px-4 py-3 text-center hidden sm:table-cell">Hari</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center w-20">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($cuti as $c)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $c->nomor_pengajuan }}</td>
                        <td class="px-4 py-3 text-gray-800">{{ $c->jenisCuti->nama }}</td>
                        <td class="px-4 py-3 text-gray-600 hidden md:table-cell">
                            {{ $c->tanggal_mulai->format('d/m/Y') }} — {{ $c->tanggal_selesai->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-700 hidden sm:table-cell">{{ $c->jumlah_hari }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $c->statusColor() }}">
                                {{ $c->statusLabel() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('cuti.show', $c) }}"
                               class="text-blue-600 hover:text-blue-800 text-xs font-medium">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-gray-400 italic">
                            Belum ada pengajuan cuti.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($cuti->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $cuti->links() }}</div>
        @endif
    </div>

</x-app-layout>
