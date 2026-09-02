<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">

        {{-- ===== SIDEBAR ===== --}}
        <aside id="sidebar"
               class="w-64 bg-blue-900 text-white flex flex-col flex-shrink-0 transition-transform duration-300 ease-in-out
                      fixed inset-y-0 left-0 z-30
                      lg:static lg:translate-x-0
                      -translate-x-full">

            {{-- Logo / Instansi --}}
            <div class="flex items-center gap-3 px-5 py-5 border-b border-blue-800">
                <div class="w-9 h-9 bg-yellow-400 rounded flex items-center justify-center flex-shrink-0">
                    <span class="text-blue-900 font-bold text-sm">PA</span>
                </div>
                <div class="leading-tight">
                    <p class="font-semibold text-sm">Sistem Cuti</p>
                    <p class="text-blue-300 text-xs">PA Makassar</p>
                </div>
            </div>

            {{-- Menu navigasi --}}
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">

                @php $role = auth()->user()->role?->slug; @endphp

                {{-- === MENU PEGAWAI === --}}
                @if($role === 'pegawai')
                    <x-sidebar-link route="dashboard" icon="home">Dashboard</x-sidebar-link>
                    <x-sidebar-link route="cuti.create" icon="plus-circle">Ajukan Cuti</x-sidebar-link>
                    <x-sidebar-link route="cuti.index" icon="list">Riwayat Cuti</x-sidebar-link>
                    <x-sidebar-link route="profile.edit" icon="user">Profil Saya</x-sidebar-link>
                @endif

                {{-- === MENU ADMIN === --}}
                @if($role === 'admin' || $role === 'superadmin')
                    <x-sidebar-link route="dashboard" icon="home">Dashboard</x-sidebar-link>

                    <div class="pt-2 pb-1">
                        <p class="px-3 text-xs font-semibold text-blue-400 uppercase tracking-wider">Kepegawaian</p>
                    </div>
                    <x-sidebar-link route="pegawai.index" icon="users">Data Pegawai</x-sidebar-link>

                    <div class="pt-2 pb-1">
                        <p class="px-3 text-xs font-semibold text-blue-400 uppercase tracking-wider">Cuti</p>
                    </div>
                    <x-sidebar-link route="persetujuan.index" icon="clipboard-check">Pengajuan Cuti</x-sidebar-link>
                    <x-sidebar-link route="saldo-cuti.index" icon="calculator">Saldo Cuti</x-sidebar-link>

                    <div class="pt-2 pb-1">
                        <p class="px-3 text-xs font-semibold text-blue-400 uppercase tracking-wider">Laporan</p>
                    </div>
                    <x-sidebar-link route="laporan.cuti" icon="chart-bar">Laporan Cuti</x-sidebar-link>
                    <x-sidebar-link route="laporan.saldo" icon="document-text">Laporan Saldo</x-sidebar-link>
                @endif

                {{-- === MENU SUPERADMIN TAMBAHAN === --}}
                @if($role === 'superadmin')
                    <div class="pt-2 pb-1">
                        <p class="px-3 text-xs font-semibold text-blue-400 uppercase tracking-wider">Pengaturan</p>
                    </div>
                    <x-sidebar-link route="jenis-cuti.index" icon="tag">Jenis Cuti</x-sidebar-link>
                    <x-sidebar-link route="pengguna.index" icon="shield-check">Pengguna</x-sidebar-link>
                @endif

            </nav>

            {{-- Info user di bawah sidebar --}}
            <div class="px-4 py-4 border-t border-blue-800">
                <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-blue-300 capitalize">{{ auth()->user()->role?->name }}</p>
            </div>
        </aside>

        {{-- Overlay untuk mobile --}}
        <div id="sidebar-overlay"
             class="fixed inset-0 bg-black/50 z-20 hidden lg:hidden"
             onclick="toggleSidebar()">
        </div>

        {{-- ===== KONTEN UTAMA ===== --}}
        <div class="flex-1 flex flex-col overflow-hidden">

            {{-- Topbar --}}
            <header class="bg-white shadow-sm z-10 flex items-center justify-between px-4 lg:px-6 h-16 flex-shrink-0">

                {{-- Tombol hamburger (mobile) --}}
                <button onclick="toggleSidebar()"
                        class="lg:hidden p-2 rounded-md text-gray-500 hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                {{-- Judul halaman --}}
                <h1 class="text-lg font-semibold text-gray-800 ml-2 lg:ml-0">
                    {{ $title ?? 'Dashboard' }}
                </h1>

                {{-- User dropdown --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900 focus:outline-none">
                        <div class="w-8 h-8 bg-blue-900 rounded-full flex items-center justify-center text-white text-xs font-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <span class="hidden sm:block">{{ auth()->user()->name }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="open"
                         @click.outside="open = false"
                         x-transition
                         class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-100 z-50">
                        <a href="{{ route('profile.edit') }}"
                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            Profil Saya
                        </a>
                        <div class="border-t border-gray-100"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            {{-- Konten halaman --}}
            <main class="flex-1 overflow-y-auto p-4 lg:p-6">

                {{-- Flash messages --}}
                @if(session('success'))
                    <div class="mb-4 px-4 py-3 rounded-md bg-green-50 border border-green-200 text-green-800 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 px-4 py-3 rounded-md bg-red-50 border border-red-200 text-red-800 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ session('error') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
    </script>

</body>
</html>
