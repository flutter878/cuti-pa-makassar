<x-app-layout title="Dashboard">

    @php $user = auth()->user(); $role = $user->role?->slug; @endphp

    {{-- ===== DASHBOARD PEGAWAI ===== --}}
    @if($role === 'pegawai')

        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Selamat datang, {{ $user->name }}</h2>
            <p class="text-sm text-gray-500 mt-1">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>

        {{-- Card saldo & ringkasan --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Sisa Cuti Tahun Ini</p>
                <p class="text-3xl font-bold text-blue-900 mt-1">— <span class="text-base font-normal text-gray-500">hari</span></p>
                <p class="text-xs text-gray-400 mt-2">Tahun {{ now()->year }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Sisa Cuti Tahun Lalu</p>
                <p class="text-3xl font-bold text-blue-700 mt-1">— <span class="text-base font-normal text-gray-500">hari</span></p>
                <p class="text-xs text-gray-400 mt-2">Carry over {{ now()->year - 1 }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Total Pengajuan</p>
                <p class="text-3xl font-bold text-gray-700 mt-1">—</p>
                <p class="text-xs text-gray-400 mt-2">Sepanjang waktu</p>
            </div>
        </div>

        {{-- Tombol ajukan --}}
        <div class="mb-6">
            <a href="#"
               class="inline-flex items-center gap-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Ajukan Cuti
            </a>
        </div>

        {{-- Status pengajuan terakhir --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Pengajuan Terakhir</h3>
            <p class="text-sm text-gray-400 italic">Belum ada pengajuan cuti.</p>
        </div>

    @endif

    {{-- ===== DASHBOARD ADMIN / SUPERADMIN ===== --}}
    @if($role === 'admin' || $role === 'superadmin')

        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Dashboard Admin</h2>
            <p class="text-sm text-gray-500 mt-1">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Total Pegawai</p>
                <p class="text-3xl font-bold text-blue-900 mt-1">—</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Pengajuan Masuk</p>
                <p class="text-3xl font-bold text-yellow-600 mt-1">—</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Disetujui Bulan Ini</p>
                <p class="text-3xl font-bold text-green-600 mt-1">—</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Ditolak Bulan Ini</p>
                <p class="text-3xl font-bold text-red-500 mt-1">—</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Pengajuan Terbaru</h3>
            <p class="text-sm text-gray-400 italic">Belum ada pengajuan masuk.</p>
        </div>

    @endif

</x-app-layout>
