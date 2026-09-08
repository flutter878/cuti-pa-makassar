<x-app-layout title="Pengajuan Masuk">

    <div class="mb-5 flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Pengajuan Masuk</h2>
            <p class="text-sm text-gray-500">Daftar pengajuan yang perlu Anda tindaklanjuti</p>
        </div>
        @if($jumlahPending > 0)
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-yellow-100 text-yellow-800 text-sm font-semibold rounded-full">
                <span class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></span>
                {{ $jumlahPending }} menunggu tindakan Anda
            </span>
        @endif
    </div>

    {{-- Filter status --}}
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4 mb-5">
        <form method="GET" class="flex flex-wrap gap-2">
            @foreach([''=>'Semua', 'pending'=>'Menunggu', 'approved'=>'Disetujui', 'rejected'=>'Ditolak', 'returned'=>'Dikembalikan'] as $val => $label)
                <a href="{{ request()->fullUrlWithQuery(['status' => $val]) }}"
                   class="px-3 py-1.5 text-sm rounded-lg border transition-colors
                   {{ request('status', '') === $val
                       ? 'bg-blue-600 text-white border-blue-600'
                       : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-600' }}">
                    {{ $label }}
                </a>
            @endforeach
        </form>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        @if($stages->isEmpty())
            <div class="py-16 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-sm font-medium">Tidak ada pengajuan</p>
                <p class="text-xs mt-1">Belum ada pengajuan yang ditugaskan ke Anda.</p>
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Pengajuan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden md:table-cell">Pemohon</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden lg:table-cell">Tanggal Cuti</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tahap Anda</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($stages as $stage)
                        @php $cuti = $stage->cuti; @endphp
                        <tr class="hover:bg-gray-50 transition-colors {{ $stage->isPending() ? 'bg-yellow-50/40' : '' }}">
                            <td class="px-4 py-3">
                                <p class="font-mono text-xs font-semibold text-gray-700">
                                    {{ $cuti?->nomor_pengajuan ?? '-' }}
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $cuti?->jenisCuti?->nama ?? '-' }}
                                </p>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <p class="font-medium text-gray-800">{{ $cuti?->pegawai?->nama ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $cuti?->pegawai?->jabatan?->nama_jabatan ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                @if($cuti)
                                    <p class="text-gray-700">
                                        {{ $cuti->tanggal_mulai->format('d M') }} –
                                        {{ $cuti->tanggal_selesai->format('d M Y') }}
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $cuti->jumlah_hari }} hari kerja</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-xs font-medium text-gray-700">{{ $stage->label_tahap }}</p>
                                <p class="text-xs text-gray-500">{{ $stage->jenisTindakanLabel() }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $stage->statusColor() }}">
                                    {{ $stage->statusLabel() }}
                                </span>
                                @if($stage->tanggal_tindakan)
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ $stage->tanggal_tindakan->format('d M Y') }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('approval.show', $cuti) }}"
                                   class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 font-medium px-2.5 py-1.5 rounded-lg hover:bg-blue-50 transition-colors">
                                    @if($stage->isPending())
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Proses
                                    @else
                                        Lihat
                                    @endif
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Pagination --}}
            @if($stages->hasPages())
                <div class="px-4 py-3 border-t border-gray-100">
                    {{ $stages->withQueryString()->links() }}
                </div>
            @endif
        @endif
    </div>

</x-app-layout>
