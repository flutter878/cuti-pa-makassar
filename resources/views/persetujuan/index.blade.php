<x-app-layout title="Pengajuan Cuti">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800">Pengajuan Cuti</h2>
        <p class="text-sm text-gray-500 mt-1">
            @if($isAdmin)
                Semua pengajuan cuti pegawai.
            @elseif($pegawaiLogin?->isKetua())
                Pengajuan yang menunggu persetujuan Anda sebagai <strong>Ketua</strong>.
            @elseif($pegawaiLogin?->isAtasanLangsung())
                Pengajuan dari bawahan langsung Anda
                ({{ $pegawaiLogin->jabatan?->nama_jabatan }}).
            @endif
        </p>
    </div>

    {{-- Filter status --}}
    <div class="flex flex-wrap gap-2 mb-5">
        @php
            $filters = [
                ''                => 'Semua',
                'menunggu_atasan' => 'Menunggu Atasan',
                'menunggu_ketua'  => 'Menunggu Ketua',
                'disetujui'       => 'Disetujui',
                'ditolak'         => 'Ditolak',
                'dibatalkan'      => 'Dibatalkan',
            ];
            $activeFilter = request('status', '');
        @endphp
        @foreach($filters as $value => $label)
            <a href="{{ route('persetujuan.index', $value ? ['status' => $value] : []) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors
                      {{ $activeFilter === $value
                          ? 'bg-blue-900 text-white border-blue-900'
                          : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-700' }}">
                {{ $label }}
                @if(isset($jumlahStatus[$value]) && $value !== '')
                    <span class="inline-flex items-center justify-center min-w-[16px] h-4 rounded-full px-1 text-[10px]
                                 {{ $activeFilter === $value ? 'bg-blue-700 text-white' : 'bg-gray-100 text-gray-500' }}">
                        {{ $jumlahStatus[$value] }}
                    </span>
                @endif
            </a>
        @endforeach
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3 font-semibold">Nomor / Jenis</th>
                        <th class="px-5 py-3 font-semibold">Pegawai</th>
                        <th class="px-5 py-3 font-semibold">Periode</th>
                        <th class="px-5 py-3 font-semibold">Atasan Langsung</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold">Diajukan</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($cuti as $item)
                        @php
                            $highlight = in_array($item->status, ['menunggu_atasan', 'menunggu_ketua']);
                        @endphp
                        <tr class="hover:bg-gray-50 {{ $highlight ? 'bg-amber-50/30' : '' }}">
                            <td class="px-5 py-4">
                                <p class="font-mono text-xs font-semibold text-gray-700">{{ $item->nomor_pengajuan }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $item->jenisCuti->nama }}</p>
                                <p class="text-xs text-gray-400">{{ $item->jumlah_hari }} hari</p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-medium text-gray-800">{{ $item->pegawai->nama }}</p>
                                <p class="text-xs text-gray-400">{{ $item->pegawai->jabatan?->nama_jabatan }}</p>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600 whitespace-nowrap">
                                {{ $item->tanggal_mulai->format('d/m/Y') }}<br>
                                s/d {{ $item->tanggal_selesai->format('d/m/Y') }}
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600">
                                @if($item->pegawai->atasanLangsung)
                                    <p>{{ $item->pegawai->atasanLangsung->nama }}</p>
                                    <p class="text-gray-400">{{ $item->pegawai->atasanLangsung->jabatan?->nama_jabatan }}</p>
                                @else
                                    <span class="text-gray-300 italic">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $item->statusColor() }}">
                                    {{ $item->statusLabel() }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-400 whitespace-nowrap">
                                {{ $item->tanggal_pengajuan->format('d/m/Y') }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('persetujuan.show', $item) }}"
                                   class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 text-xs font-medium whitespace-nowrap">
                                    Detail
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                                <svg class="w-10 h-10 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Tidak ada pengajuan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($cuti->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">{{ $cuti->links() }}</div>
        @endif
    </div>
</x-app-layout>
