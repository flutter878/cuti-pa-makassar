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
                —
                <span class="text-sm font-normal text-gray-400">hari</span>
            </p>
            <p class="text-xs text-gray-400 mt-1">Tahun {{ now()->year }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Carry Over</p>
            <p class="text-3xl font-bold text-blue-700">
                —
                <span class="text-sm font-normal text-gray-400">hari</span>
            </p>
            <p class="text-xs text-gray-400 mt-1">Dari tahun {{ now()->year - 1 }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Total Pengajuan</p>
            <p class="text-3xl font-bold text-gray-700">—</p>
            <p class="text-xs text-gray-400 mt-1">Sepanjang waktu</p>
        </div>
    </div>

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
    </div>

@endif

{{-- ═══════════════════════════════════════
     DASHBOARD ADMIN / SUPERADMIN
═══════════════════════════════════════ --}}
@if($role === 'admin' || $role === 'superadmin')

    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800">Dashboard Admin</h2>
        <p class="text-sm text-gray-500 mt-0.5">{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Total Pegawai</p>
                <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-800">—</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Menunggu</p>
                <div class="w-8 h-8 bg-yellow-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-yellow-600">—</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Disetujui</p>
                <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-green-600">—</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Ditolak</p>
                <div class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-red-500">—</p>
        </div>
    </div>

    {{-- Tabel pengajuan terbaru --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Pengajuan Terbaru</h3>
        </div>
        <div class="px-5 py-10 text-center">
            <p class="text-sm text-gray-400">Belum ada pengajuan masuk.</p>
        </div>
    </div>

@endif

</x-app-layout>
