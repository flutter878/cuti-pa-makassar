<x-app-layout title="Laporan Saldo Cuti">

    {{-- Header --}}
    <div class="mb-5 flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Laporan Saldo Cuti</h2>
            <p class="text-sm text-gray-500 mt-0.5">Rekap saldo cuti seluruh pegawai aktif</p>
        </div>
        {{-- Tombol Export PDF --}}
        <a href="{{ route('laporan.export-saldo', request()->query()) }}"
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
        <form method="GET" action="{{ route('laporan.saldo') }}" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">

            {{-- Tahun --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tahun</label>
                <select name="tahun" class="w-full text-sm border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    @foreach($tahunList as $y)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
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
            <div class="flex items-end gap-2">
                <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-1.5 rounded-lg transition-colors">
                    Terapkan
                </button>
                @if(request()->hasAny(['unit_kerja_id','cari']) || request('tahun') != now()->year)
                    <a href="{{ route('laporan.saldo') }}"
                       class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-3 py-1.5 rounded-lg transition-colors">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Info tahun --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-2 mb-4 text-sm text-blue-800">
        Menampilkan data saldo cuti tahun <strong>{{ $tahun }}</strong> — total <strong>{{ $data->total() }}</strong> pegawai aktif
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wide border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left">No</th>
                        <th class="px-4 py-3 text-left">Nama Pegawai</th>
                        <th class="px-4 py-3 text-left">NIP</th>
                        <th class="px-4 py-3 text-left">Jabatan</th>
                        <th class="px-4 py-3 text-left">Unit Kerja</th>
                        <th class="px-4 py-3 text-center">Hak Cuti</th>
                        <th class="px-4 py-3 text-center">Carry Over</th>
                        <th class="px-4 py-3 text-center">Terpakai</th>
                        <th class="px-4 py-3 text-center">Sisa</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data as $i => $pegawai)
                        @php $saldo = $pegawai->saldoCuti->first(); @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-500">{{ $data->firstItem() + $i }}</td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800">{{ $pegawai->nama }}</p>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $pegawai->nip }}</td>
                            <td class="px-4 py-3 text-gray-600 text-xs">{{ $pegawai->jabatan?->nama_jabatan ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600 text-xs">{{ $pegawai->unitKerja?->nama_unit ?? '—' }}</td>

                            @if($saldo)
                                <td class="px-4 py-3 text-center font-medium text-gray-700">{{ $saldo->hak_cuti }}</td>
                                <td class="px-4 py-3 text-center font-medium text-gray-700">{{ $saldo->carry_over }}</td>
                                <td class="px-4 py-3 text-center font-medium text-gray-700">{{ $saldo->terpakai }}</td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $sisa = $saldo->sisa;
                                        $sisaColor = $sisa >= 8
                                            ? 'text-green-700 font-bold'
                                            : ($sisa >= 4 ? 'text-yellow-600 font-bold' : 'text-red-600 font-bold');
                                    @endphp
                                    <span class="{{ $sisaColor }} text-base">{{ $sisa }}</span>
                                </td>
                            @else
                                <td colspan="4" class="px-4 py-3 text-center text-xs text-gray-400 italic">
                                    Belum ada saldo tahun {{ $tahun }}
                                </td>
                            @endif

                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('saldo-cuti.show', $pegawai) }}"
                                   class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-10 text-center text-gray-400">
                                <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <p class="text-sm">Tidak ada data pegawai.</p>
                                @if(request()->hasAny(['unit_kerja_id','cari']))
                                    <p class="text-xs mt-1">Coba ubah atau reset filter.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($data->count() > 0 && $data->getCollection()->filter(fn($p) => $p->saldoCuti->isNotEmpty())->count() > 0)
                    {{-- Baris total --}}
                    @php
                        $totalHak      = $data->getCollection()->sum(fn($p) => $p->saldoCuti->first()?->hak_cuti ?? 0);
                        $totalCarry    = $data->getCollection()->sum(fn($p) => $p->saldoCuti->first()?->carry_over ?? 0);
                        $totalTerpakai = $data->getCollection()->sum(fn($p) => $p->saldoCuti->first()?->terpakai ?? 0);
                        $totalSisa     = $data->getCollection()->sum(fn($p) => $p->saldoCuti->first()?->sisa ?? 0);
                    @endphp
                    <tfoot class="bg-gray-50 border-t-2 border-gray-300 text-xs font-semibold text-gray-700">
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-right">Total (halaman ini):</td>
                            <td class="px-4 py-2 text-center">{{ $totalHak }}</td>
                            <td class="px-4 py-2 text-center">{{ $totalCarry }}</td>
                            <td class="px-4 py-2 text-center">{{ $totalTerpakai }}</td>
                            <td class="px-4 py-2 text-center">{{ $totalSisa }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        {{-- Pagination --}}
        @if($data->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $data->links() }}
            </div>
        @endif
    </div>

    {{-- Keterangan warna --}}
    <div class="mt-4 flex items-center gap-4 text-xs text-gray-500">
        <span>Keterangan sisa:</span>
        <span class="text-green-700 font-bold">≥ 8 hari</span>
        <span class="text-yellow-600 font-bold">4–7 hari</span>
        <span class="text-red-600 font-bold">0–3 hari</span>
    </div>

</x-app-layout>
