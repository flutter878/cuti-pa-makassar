<x-app-layout title="Laporan Pengajuan Cuti">

    {{-- Header --}}
    <div class="mb-5 flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Laporan Pengajuan Cuti</h2>
            <p class="text-sm text-gray-500 mt-0.5">Daftar seluruh pengajuan cuti pegawai</p>
        </div>
        {{-- Tombol Export PDF --}}
        <a href="{{ route('laporan.export-pengajuan', request()->query()) }}"
           target="_blank"
           class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Export PDF
        </a>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 mb-5">
        <form method="GET" action="{{ route('laporan.cuti') }}" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">

            {{-- Tahun --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tahun</label>
                <select name="tahun" class="w-full text-sm border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua</option>
                    @foreach(range(now()->year, now()->year - 4) as $y)
                        <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Bulan --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Bulan</label>
                <select name="bulan" class="w-full text-sm border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua</option>
                    @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $bln)
                        <option value="{{ $i + 1 }}" {{ request('bulan') == $i + 1 ? 'selected' : '' }}>{{ $bln }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Jenis Cuti --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Jenis Cuti</label>
                <select name="jenis_cuti_id" class="w-full text-sm border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua</option>
                    @foreach($jenisCuti as $j)
                        <option value="{{ $j->id }}" {{ request('jenis_cuti_id') == $j->id ? 'selected' : '' }}>{{ $j->nama }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full text-sm border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua</option>
                    @foreach([
                        'menunggu_atasan' => 'Menunggu Atasan',
                        'menunggu_ketua'  => 'Menunggu Ketua',
                        'disetujui'       => 'Disetujui',
                        'ditolak'         => 'Ditolak',
                        'dibatalkan'      => 'Dibatalkan',
                    ] as $val => $label)
                        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Unit Kerja --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Unit Kerja</label>
                <select name="unit_kerja_id" class="w-full text-sm border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua</option>
                    @foreach($unitKerja as $uk)
                        <option value="{{ $uk->id }}" {{ request('unit_kerja_id') == $uk->id ? 'selected' : '' }}>{{ $uk->nama_unit }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Cari --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari Pegawai</label>
                <input type="text" name="cari" value="{{ request('cari') }}"
                       placeholder="Nama / NIP"
                       class="w-full text-sm border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            </div>

            {{-- Tombol --}}
            <div class="col-span-2 sm:col-span-3 lg:col-span-6 flex gap-2">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-1.5 rounded-lg transition-colors">
                    Terapkan Filter
                </button>
                @if(request()->hasAny(['tahun','bulan','jenis_cuti_id','status','unit_kerja_id','cari']))
                    <a href="{{ route('laporan.cuti') }}"
                       class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-1.5 rounded-lg transition-colors">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Ringkasan --}}
    @php
        $total     = $data->total();
        $disetujui = \App\Models\Cuti::where('status', 'disetujui')
            ->when(request('tahun'), fn($q) => $q->whereYear('tanggal_pengajuan', request('tahun')))
            ->when(request('bulan'), fn($q) => $q->whereMonth('tanggal_pengajuan', request('bulan')))
            ->count();
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
        <div class="bg-white rounded-lg border border-gray-100 shadow-sm px-4 py-3">
            <p class="text-xs text-gray-500 mb-1">Total Pengajuan</p>
            <p class="text-2xl font-bold text-gray-800">{{ $total }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-100 shadow-sm px-4 py-3">
            <p class="text-xs text-gray-500 mb-1">Menunggu Proses</p>
            <p class="text-2xl font-bold text-yellow-600">
                {{ $data->getCollection()->whereIn('status', ['menunggu_atasan','menunggu_ketua'])->count() }}
            </p>
        </div>
        <div class="bg-white rounded-lg border border-gray-100 shadow-sm px-4 py-3">
            <p class="text-xs text-gray-500 mb-1">Disetujui (halaman ini)</p>
            <p class="text-2xl font-bold text-green-600">
                {{ $data->getCollection()->where('status', 'disetujui')->count() }}
            </p>
        </div>
        <div class="bg-white rounded-lg border border-gray-100 shadow-sm px-4 py-3">
            <p class="text-xs text-gray-500 mb-1">Ditolak / Dibatalkan</p>
            <p class="text-2xl font-bold text-red-600">
                {{ $data->getCollection()->whereIn('status', ['ditolak','dibatalkan'])->count() }}
            </p>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wide border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left">No</th>
                        <th class="px-4 py-3 text-left">Nomor Pengajuan</th>
                        <th class="px-4 py-3 text-left">Pegawai</th>
                        <th class="px-4 py-3 text-left">Unit Kerja</th>
                        <th class="px-4 py-3 text-left">Jenis Cuti</th>
                        <th class="px-4 py-3 text-center">Hari</th>
                        <th class="px-4 py-3 text-left">Tgl Mulai</th>
                        <th class="px-4 py-3 text-left">Tgl Selesai</th>
                        <th class="px-4 py-3 text-left">Tgl Pengajuan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data as $i => $item)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-500">{{ $data->firstItem() + $i }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $item->nomor_pengajuan }}</td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800">{{ $item->pegawai->nama }}</p>
                                <p class="text-xs text-gray-400">{{ $item->pegawai->nip }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-xs">{{ $item->pegawai->unitKerja?->nama_unit ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $item->jenisCuti->nama }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-gray-800">{{ $item->jumlah_hari }}</td>
                            <td class="px-4 py-3 text-gray-600 text-xs">{{ $item->tanggal_mulai->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-gray-600 text-xs">{{ $item->tanggal_selesai->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-gray-600 text-xs">{{ $item->tanggal_pengajuan->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $item->statusColor() }}">
                                    {{ $item->statusLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('laporan.formulir', $item) }}" target="_blank"
                                   title="Download Formulir PDF"
                                   class="inline-flex items-center gap-1 text-xs text-red-600 hover:text-red-800 font-medium">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-4 py-10 text-center text-gray-400">
                                <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="text-sm">Tidak ada data pengajuan.</p>
                                @if(request()->hasAny(['tahun','bulan','jenis_cuti_id','status','unit_kerja_id','cari']))
                                    <p class="text-xs mt-1">Coba ubah atau reset filter.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($data->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $data->links() }}
            </div>
        @endif
    </div>

</x-app-layout>
