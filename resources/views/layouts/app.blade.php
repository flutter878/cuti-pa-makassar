<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($title ?? 'Dashboard') . ' — ' . config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">

<div class="flex h-screen overflow-hidden">

    {{-- ════════════════════════════════════
         SIDEBAR
    ════════════════════════════════════ --}}
    <aside id="sidebar"
           class="w-64 bg-blue-900 text-white flex flex-col flex-shrink-0
                  fixed inset-y-0 left-0 z-30 transition-transform duration-300 ease-in-out
                  -translate-x-full lg:static lg:translate-x-0">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-5 py-[18px] border-b border-blue-800/60">
            <div class="w-9 h-9 bg-yellow-400 rounded-lg flex items-center justify-center flex-shrink-0 shadow-sm">
                <span class="text-blue-900 font-bold text-sm">PA</span>
            </div>
            <div class="leading-tight">
                <p class="font-bold text-sm text-white">Sistem Cuti</p>
                <p class="text-blue-300 text-xs">PA Makassar</p>
            </div>
        </div>

        {{-- Navigasi --}}
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5">
            @php $role = auth()->user()->role?->slug; @endphp

            {{-- PEGAWAI --}}
            @if($role === 'pegawai')
                <x-sidebar-link route="dashboard"    icon="home">Dashboard</x-sidebar-link>
                <x-sidebar-link route="cuti.create"  icon="plus-circle">Ajukan Cuti</x-sidebar-link>
                <x-sidebar-link route="cuti.index"   icon="list">Riwayat Cuti</x-sidebar-link>

                {{-- Menu persetujuan hanya muncul untuk Panitera, Sekretaris, Ketua --}}
                @php
                    $jabatanUser = auth()->user()->pegawai?->jabatan?->nama_jabatan;
                    $jabatanApprover = array_merge(
                        config('approver.jabatan_atasan', []),
                        config('approver.jabatan_ketua', [])
                    );
                    $isApprover = in_array($jabatanUser, $jabatanApprover);
                @endphp
                @if($isApprover)
                    <div class="pt-2 pb-1">
                        <p class="px-3 py-1 text-xs font-semibold text-blue-400 uppercase tracking-wider">Persetujuan</p>
                        <x-sidebar-link route="persetujuan.index" icon="clipboard-check">Pengajuan Masuk</x-sidebar-link>
                    </div>
                @endif

                <x-sidebar-link route="profile.edit" icon="user">Profil Saya</x-sidebar-link>
            @endif

            {{-- ADMIN & SUPERADMIN --}}
            @if($role === 'admin' || $role === 'superadmin')
                <x-sidebar-link route="dashboard" icon="home">Dashboard</x-sidebar-link>

                <p class="px-3 pt-4 pb-1 text-xs font-semibold text-blue-400 uppercase tracking-wider">Kepegawaian</p>
                <x-sidebar-link route="pegawai.index"  icon="users">Data Pegawai</x-sidebar-link>
                <x-sidebar-link route="pengguna.index" icon="shield-check">Pengguna</x-sidebar-link>

                <p class="px-3 pt-4 pb-1 text-xs font-semibold text-blue-400 uppercase tracking-wider">Cuti</p>
                <x-sidebar-link route="persetujuan.index" icon="clipboard-check">Pengajuan Cuti</x-sidebar-link>
                <x-sidebar-link route="saldo-cuti.index"  icon="calculator">Saldo Cuti</x-sidebar-link>

                <p class="px-3 pt-4 pb-1 text-xs font-semibold text-blue-400 uppercase tracking-wider">Laporan</p>
                <x-sidebar-link route="laporan.cuti"   icon="chart-bar">Laporan Cuti</x-sidebar-link>
                <x-sidebar-link route="laporan.saldo"  icon="document-text">Laporan Saldo</x-sidebar-link>
            @endif

            {{-- SUPERADMIN TAMBAHAN --}}
            @if($role === 'superadmin')
                <p class="px-3 pt-4 pb-1 text-xs font-semibold text-blue-400 uppercase tracking-wider">Pengaturan</p>
                <x-sidebar-link route="jabatan.index"    icon="tag">Jabatan</x-sidebar-link>
                <x-sidebar-link route="unit-kerja.index" icon="tag">Unit Kerja</x-sidebar-link>
                <x-sidebar-link route="jenis-cuti.index" icon="tag">Jenis Cuti</x-sidebar-link>
            @endif
        </nav>

        {{-- User info bawah --}}
        <div class="px-4 py-3 border-t border-blue-800/60 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-blue-700 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-blue-300 capitalize">{{ auth()->user()->role?->name }}</p>
            </div>
        </div>
    </aside>

    {{-- Overlay mobile --}}
    <div id="sidebar-overlay"
         class="fixed inset-0 bg-black/40 z-20 hidden lg:hidden"
         onclick="toggleSidebar()"></div>

    {{-- ════════════════════════════════════
         KONTEN UTAMA
    ════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col overflow-hidden min-w-0">

        {{-- Topbar --}}
        <header class="bg-white border-b border-gray-200 z-10 flex items-center justify-between px-4 lg:px-6 h-16 flex-shrink-0">

            {{-- Hamburger --}}
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()"
                        class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <h1 class="text-base font-semibold text-gray-800">{{ $title ?? 'Dashboard' }}</h1>
            </div>

            {{-- User dropdown --}}
            <div class="relative">
                <button id="userMenuBtn"
                        onclick="toggleUserMenu()"
                        class="flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900
                               px-2 py-1.5 rounded-lg hover:bg-gray-100 transition-colors focus:outline-none">
                    <div class="w-8 h-8 bg-blue-900 rounded-full flex items-center justify-center text-white text-xs font-bold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <span class="hidden sm:block max-w-[120px] truncate font-medium">
                        {{ auth()->user()->name }}
                    </span>
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div id="userMenu"
                     class="hidden absolute right-0 mt-1.5 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
                    <div class="px-3 py-2 border-b border-gray-100">
                        <p class="text-xs font-semibold text-gray-800 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500 capitalize">{{ auth()->user()->role?->name }}</p>
                    </div>
                    <a href="{{ route('profile.edit') }}"
                       class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Profil Saya
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </header>

        {{-- Konten --}}
        <main class="flex-1 overflow-y-auto p-4 lg:p-6">

            {{-- Flash success --}}
            @if(session('success'))
                <div class="mb-5 flex items-start gap-3 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Flash error --}}
            @if(session('error'))
                <div class="mb-5 flex items-start gap-3 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>

<script>
    // Sidebar toggle (mobile)
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('-translate-x-full');
        document.getElementById('sidebar-overlay').classList.toggle('hidden');
    }

    // User dropdown
    function toggleUserMenu() {
        document.getElementById('userMenu').classList.toggle('hidden');
    }

    // Tutup dropdown saat klik luar
    document.addEventListener('click', function(e) {
        const btn  = document.getElementById('userMenuBtn');
        const menu = document.getElementById('userMenu');
        if (btn && menu && !btn.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.add('hidden');
        }
    });
</script>

</body>
</html>
