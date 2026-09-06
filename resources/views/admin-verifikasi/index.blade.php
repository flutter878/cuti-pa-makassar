<x-app-layout title="Verifikasi Pengajuan Cuti">

    <div class="mb-5">
        <h2 class="text-lg font-semibold text-gray-800">Verifikasi Pengajuan Cuti</h2>
        <p class="text-sm text-gray-500 mt-0.5">Periksa dan verifikasi pengajuan sebelum diteruskan ke atasan</p>
    </div>

    {{-- Kartu status ringkasan --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-5">
        @php
            $statusList = [
                'menunggu_verifikasi_admin'   => ['label' => 'Menunggu Verifikasi',  'color' => 'purple'],
                'menunggu_persetujuan_atasan' => ['label' => 'Menunggu Atasan',      'color' => 'yellow'],
                'menunggu_persetujuan_ketua'  => ['label' => 'Menunggu Ketua',       'color' => 'orange'],
                'disetujui'                   => ['label' => 'Disetujui',            'color' => 'green'],
                'ditolak'                     => ['label' => 'Ditolak',              'color' => 'red'],
            ];
            $colorMap = [
                'purple' => 'border-purple-200 bg-purple-50 text-purple-700',
                'yellow' => 'border-yellow-200 bg-yellow-50 text-yellow-700',
                'orange' => 'border-orange-200 bg-orange-50 text-orange-700',
                'green'  => 'border-green-200 bg-green-50 text-green-700',
                'red'    => 'border-red-200 bg-red-50 text-red-700',
            ];
        @endphp
        @foreach($statusList as $st => $info)
            <a href="{{ route('admin-verifikasi.index', ['status' => $st]) }}"
               class="rounded-lg border px-3 py-3 text-center transition-all hover:shadow-sm
                      {{ $filterStatus === $st ? $colorMap[$info['color']] . ' ring-2 ring-offset-1 ring-' . $info['color'] . '-400' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300' }}">
                <p class="text-2xl font-bold">{{ $jumlahStatus[$st] ?? 0 }}</p>
                <p class="text-xs mt-0.5">{{ $info['label'] }}</p>
            </a>
        @endforeach
    </div>

    {{-- Filter + Search --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 mb-5">
        <form method="GET" action="{{ route('admin-verifikasi.index') }}" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="text-sm border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-blue-500">
                    <option value="semua" {{ $filterStatus === 'semua' ? 'selected' : '' }}>Semua Status</option>
                    <option value="menunggu_verifikasi_admin" {{ $filterStatus === 'menunggu_verifikasi_admin' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                    <option value="menunggu_persetujuan_atasan" {{ $filterStatus === 'menunggu_persetujuan_atasan' ? 'selected' : '' }}>Menunggu Atasan</option>
                    <option value="menunggu_persetujuan_ketua" {{ $filterStatus === 'menunggu_persetujuan_ketua' ? 'selected' : '' }}>Menunggu Ketua</option>
                    <option value="disetujui" {{ $filterStatus === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ $filterStatus === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari Pegawai</label>
                <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Nama / NIP"
                       class="text-sm border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-blue-500">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-1.5 rounded-lg transition-colors">
                Filter
            </button>
            @if(request('cari'))
                <a href="{{ route('admin-verifikasi.index', ['status' => $filterStatus]) }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-3 py-1.5 rounded-lg transition-colors">
                    Reset
                </a>
            @endif
        </form>
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
                        <th class="px-4 py-3 text-left">Jenis Cuti</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-center">Hari</th>
                        <th class="px-4 py-3 text-left">Tgl Pengajuan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pengajuan as $i => $item)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-500">{{ $pengajuan->firstItem() + $i }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $item->nomor_pengajuan }}</td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800">{{ $item->pegawai->nama }}</p>
                                <p class="text-xs text-gray-400">{{ $item->pegawai->jabatan?->nama_jabatan ?? '—' }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $item->jenisCuti->nama }}</td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                {{ $item->tanggal_mulai->format('d/m/Y') }} –
                                {{ $item->tanggal_selesai->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-center font-semibold text-gray-800">{{ $item->jumlah_hari }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $item->tanggal_pengajuan->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $item->statusColor() }}">
                                    {{ $item->statusLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('admin-verifikasi.show', $item) }}"
                                   class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 font-medium">
                                    @if($item->bisaDiprosesAdmin())
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Verifikasi
                                    @else
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Detail
                                    @endif
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center text-gray-400">
                                <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="text-sm">Tidak ada pengajuan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pengajuan->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $pengajuan->links() }}</div>
        @endif
    </div>

</x-app-layout>
