<x-app-layout title="Dashboard">
@php $user = auth()->user(); $role = $user->role?->slug; @endphp

{{-- ═══════════════════════════════════════
     DASHBOARD PEGAWAI
═══════════════════════════════════════ --}}
@if($role === 'pegawai')

    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800">Selamat datang, {{ explode(' ', $user->name)[0] }} 👋</h2>
        <p class="text-sm text-gray-500 mt-0.5">{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    {{-- Cards saldo --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Sisa Cuti Tahun Ini</p>
            <p class="text-3xl font-bold text-blue-900">
                {{ $saldoTahunIni ? $saldoTahunIni->sisa : '—' }}
                <span class="text-sm font-normal text-gray-400">hari</span>
            </p>
            <p class="text-xs text-gray-400 mt-1">
                Tahun {{ now()->year }}
                @if($saldoTahunIni)
                    &bull; Terpakai {{ $saldoTahunIni->terpakai }} / {{ $saldoTahunIni->hak_cuti }} hari
                @endif
            </p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Carry Over</p>
            <p class="text-3xl font-bold text-blue-700">
                {{ $saldoCarryOver ? max(0, $saldoCarryOver->sisa) : '—' }}
                <span class="text-sm font-normal text-gray-400">hari</span>
            </p>
            <p class="text-xs text-gray-400 mt-1">Dari tahun {{ now()->year - 1 }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Total Pengajuan</p>
            <p class="text-3xl font-bold text-gray-700">{{ $totalPengajuan }}</p>
            <p class="text-xs text-gray-400 mt-1">Sepanjang waktu</p>
        </div>
    </div>

    {{-- Approval pending (jika ada) --}}
    @if($jumlahApprovalPending > 0)
        <div class="mb-6 rounded-lg bg-yellow-50 border border-yellow-300 px-5 py-4 flex items-center justify-between">
            <div>
                <p class="text-sm font-bold text-yellow-900">Ada {{ $jumlahApprovalPending }} pengajuan menunggu tindakan Anda</p>
                <p class="text-xs text-yellow-700 mt-0.5">Anda ditugaskan sebagai pejabat approver</p>
            </div>
            <a href="{{ route('approval.index') }}"
               class="inline-flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
                Proses Sekarang
            </a>
        </div>
    @endif

    {{-- Tombol ajukan --}}
    <div class="mb-6">
        <a href="{{ route('cuti.create') }}"
           class="inline-flex items-center gap-2 bg-blue-900 hover:bg-blue-800
                  text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Ajukan Cuti Sekarang
        </a>
    </div>

    {{-- Pengajuan terakhir --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700">Pengajuan Terakhir</h3>
            <a href="{{ route('cuti.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                Lihat semua →
            </a>
        </div>
        @if($pengajuanTerakhir->isEmpty())
            <div class="px-5 py-10 text-center">
                <svg class="w-10 h-10 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-sm text-gray-400">Belum ada pengajuan cuti.</p>
                <a href="{{ route('cuti.create') }}" class="text-sm text-blue-600 hover:text-blue-800 mt-1 inline-block">
                    Buat pengajuan pertama →
                </a>
            </div>
        @else
            <div class="divide-y divide-gray-50">
                @foreach($pengajuanTerakhir as $item)
                    <a href="{{ route('cuti.show', $item) }}"
                       class="flex items-center justify-between px-5 py-3.5 hover:bg-gray-50 transition-colors">
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $item->jenisCuti->nama }}</p>
                            <p class="text-xs text-gray-400 mt-0.5 font-mono">{{ $item->nomor_pengajuan }}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $item->statusColor() }}">
                                {{ $item->statusLabel() }}
                            </span>
                            <p class="text-xs text-gray-400 mt-1">{{ $item->tanggal_mulai->format('d M Y') }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

@endif

{{-- ═══════════════════════════════════════
     DASHBOARD ADMIN / SUPERADMIN
═══════════════════════════════════════ --}}
@if($role === 'admin' || $role === 'superadmin')

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Dashboard Admin</h2>
            <p class="text-sm text-gray-500 mt-0.5">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
        @if($totalAktifAdmin > 0)
            <a href="{{ route('admin-verifikasi.index') }}"
               class="inline-flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $totalAktifAdmin }} Menunggu Tindakan Admin
            </a>
        @endif
    </div>

    {{-- Stat cards utama --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">

        {{-- Total Pegawai --}}
        <a href="{{ route('pegawai.index') }}"
           class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:border-blue-200 hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Total Pegawai</p>
                <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $totalPegawai }}</p>
            <p class="text-xs text-gray-400 mt-1">Pegawai aktif</p>
        </a>

        {{-- Disetujui tahun ini --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Disetujui</p>
                <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-green-600">{{ $totalDisetujui }}</p>
            <p class="text-xs text-gray-400 mt-1">Tahun {{ now()->year }}</p>
        </div>

        {{-- Ditolak tahun ini --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Ditolak</p>
                <div class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-red-500">{{ $totalDitolak }}</p>
            <p class="text-xs text-gray-400 mt-1">Tahun {{ now()->year }}</p>
        </div>

        {{-- Cuti Hari Ini --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Cuti Hari Ini</p>
                <div class="w-8 h-8 bg-teal-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-teal-600">{{ $cutiHariIni }}</p>
            <p class="text-xs text-gray-400 mt-1">Pegawai sedang cuti</p>
        </div>
    </div>

    {{-- Baris status pipeline --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">

        {{-- Menunggu Verifikasi Admin --}}
        <a href="{{ route('admin-verifikasi.index', ['status' => 'menunggu_verifikasi_admin']) }}"
           class="bg-purple-50 border border-purple-200 rounded-xl p-4 hover:shadow-md transition-all">
            <p class="text-xs font-semibold text-purple-500 uppercase tracking-wide mb-1">Verifikasi Admin</p>
            <p class="text-2xl font-bold text-purple-700">{{ $totalMenungguVerifikasi }}</p>
            <p class="text-xs text-purple-400 mt-1">Pengajuan baru</p>
        </a>

        {{-- Menunggu Routing --}}
        <a href="{{ route('admin-verifikasi.index', ['status' => 'menunggu_routing']) }}"
           class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 hover:shadow-md transition-all">
            <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wide mb-1">Menunggu Routing</p>
            <p class="text-2xl font-bold text-indigo-700">{{ $totalMenungguRouting }}</p>
            <p class="text-xs text-indigo-400 mt-1">Perlu tentukan approver</p>
        </a>

        {{-- Dalam Proses Approval --}}
        <a href="{{ route('admin-verifikasi.index', ['status' => 'menunggu_approval']) }}"
           class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 hover:shadow-md transition-all">
            <p class="text-xs font-semibold text-yellow-600 uppercase tracking-wide mb-1">Proses Approval</p>
            <p class="text-2xl font-bold text-yellow-700">{{ $totalMenungguApproval }}</p>
            <p class="text-xs text-yellow-500 mt-1">Di tangan pejabat</p>
        </a>

        {{-- Dikembalikan --}}
        <a href="{{ route('admin-verifikasi.index', ['status' => 'dikembalikan']) }}"
           class="bg-orange-50 border border-orange-200 rounded-xl p-4 hover:shadow-md transition-all">
            <p class="text-xs font-semibold text-orange-500 uppercase tracking-wide mb-1">Dikembalikan</p>
            <p class="text-2xl font-bold text-orange-600">{{ $totalDikembalikan }}</p>
            <p class="text-xs text-orange-400 mt-1">Menunggu perbaikan</p>
        </a>
    </div>

    {{-- Baris tengah: ringkasan cuti & rekap bulanan --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

        {{-- Info cuti mendatang --}}
        <div class="bg-blue-900 rounded-xl p-5 text-white sm:col-span-1">
            <p class="text-xs font-semibold text-blue-200 uppercase tracking-wide mb-3">Cuti Mendatang</p>
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-xs text-blue-300">Hari ini</span>
                    <span class="text-lg font-bold">{{ $cutiHariIni }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-blue-300">Besok</span>
                    <span class="text-lg font-bold">{{ $cutiBesok }}</span>
                </div>
                <div class="flex justify-between items-center border-t border-blue-700 pt-2">
                    <span class="text-xs text-blue-300">Minggu ini</span>
                    <span class="text-lg font-bold">{{ $cutiMingguIni }}</span>
                </div>
            </div>
        </div>

        {{-- Rekap status tahun berjalan --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 sm:col-span-2">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Rekap Status ({{ now()->year }})</p>
            <div class="flex flex-wrap gap-3">
                @php
                    $statusList = [
                        'menunggu_verifikasi_admin' => ['label' => 'Menunggu Verifikasi', 'color' => 'bg-purple-100 text-purple-700'],
                        'menunggu_routing'          => ['label' => 'Menunggu Routing',    'color' => 'bg-indigo-100 text-indigo-700'],
                        'menunggu_approval'         => ['label' => 'Dalam Approval',      'color' => 'bg-yellow-100 text-yellow-700'],
                        'dikembalikan'              => ['label' => 'Dikembalikan',         'color' => 'bg-orange-100 text-orange-700'],
                        'disetujui'                 => ['label' => 'Disetujui',            'color' => 'bg-green-100 text-green-700'],
                        'ditolak'                   => ['label' => 'Ditolak',              'color' => 'bg-red-100 text-red-700'],
                        'dibatalkan'                => ['label' => 'Dibatalkan',           'color' => 'bg-gray-100 text-gray-500'],
                    ];
                @endphp
                @foreach($statusList as $key => $info)
                    @if(($ringkasanStatus[$key] ?? 0) > 0)
                        <div class="flex items-center gap-2">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $info['color'] }}">
                                {{ $info['label'] }}
                            </span>
                            <span class="text-sm font-bold text-gray-700">
                                {{ $ringkasanStatus[$key] ?? 0 }}
                            </span>
                        </div>
                    @endif
                @endforeach
                {{-- Jika semua 0 --}}
                @if(($ringkasanStatus->sum()) === 0)
                    <p class="text-xs text-gray-400 italic">Belum ada data pengajuan tahun ini.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Cuti hari ini: daftar pegawai --}}
    @if($cutiHariIniList->isNotEmpty())
        <div class="bg-white rounded-xl border border-teal-200 shadow-sm mb-6">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <div class="w-2.5 h-2.5 bg-teal-500 rounded-full animate-pulse"></div>
                <h3 class="text-sm font-semibold text-gray-700">Pegawai Cuti Hari Ini</h3>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($cutiHariIniList->take(5) as $item)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $item->pegawai?->nama }}</p>
                            <p class="text-xs text-gray-400">{{ $item->pegawai?->jabatan?->nama_jabatan }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-600">{{ $item->jenisCuti?->nama }}</p>
                            <p class="text-xs text-gray-400">
                                s.d. {{ $item->tanggal_selesai->format('d M Y') }}
                            </p>
                        </div>
                    </div>
                @endforeach
                @if($cutiHariIniList->count() > 5)
                    <div class="px-5 py-2 text-xs text-gray-400 text-center">
                        +{{ $cutiHariIniList->count() - 5 }} pegawai lainnya
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Tabel pengajuan terbaru --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700">Pengajuan Terbaru</h3>
            <a href="{{ route('admin-verifikasi.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                Lihat semua →
            </a>
        </div>
        @if($pengajuanTerbaru->isEmpty())
            <div class="px-5 py-10 text-center">
                <p class="text-sm text-gray-400">Belum ada pengajuan masuk.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3 font-semibold">Nomor</th>
                            <th class="px-5 py-3 font-semibold hidden md:table-cell">Pegawai</th>
                            <th class="px-5 py-3 font-semibold hidden lg:table-cell">Jenis</th>
                            <th class="px-5 py-3 font-semibold hidden lg:table-cell">Tgl Mulai</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($pengajuanTerbaru as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-mono text-xs text-gray-600">{{ $item->nomor_pengajuan }}</td>
                                <td class="px-5 py-3 hidden md:table-cell">
                                    <p class="font-medium text-gray-800">{{ $item->pegawai?->nama ?? '-' }}</p>
                                    <p class="text-xs text-gray-400">{{ $item->pegawai?->nip ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-3 text-gray-600 hidden lg:table-cell">{{ $item->jenisCuti?->nama ?? '-' }}</td>
                                <td class="px-5 py-3 text-gray-600 whitespace-nowrap hidden lg:table-cell">
                                    {{ $item->tanggal_mulai->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $item->statusColor() }}">
                                        {{ $item->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('admin-verifikasi.show', $item) }}"
                                       class="text-blue-600 hover:text-blue-800 text-xs font-medium">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@endif

</x-app-layout>
