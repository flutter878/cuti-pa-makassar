<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — Login</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">

    <div class="min-h-screen flex">

        {{-- Panel kiri — branding (hanya desktop) --}}
        <div class="hidden lg:flex lg:w-1/2 bg-blue-900 flex-col justify-between p-12">
            {{-- Logo --}}
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-yellow-400 rounded-lg flex items-center justify-center flex-shrink-0">
                    <span class="text-blue-900 font-bold text-base">PA</span>
                </div>
                <div>
                    <p class="text-white font-bold text-base leading-tight">Pengadilan Agama</p>
                    <p class="text-blue-300 text-sm">Makassar</p>
                </div>
            </div>

            {{-- Teks tengah --}}
            <div>
                <h1 class="text-white text-4xl font-bold leading-snug mb-4">
                    Sistem Informasi<br>Pengajuan Cuti
                </h1>
                <p class="text-blue-200 text-base leading-relaxed max-w-sm">
                    Kelola pengajuan cuti pegawai secara online — mudah, cepat, dan transparan.
                </p>
            </div>

            {{-- Footer kiri --}}
            <p class="text-blue-400 text-xs">
                © {{ date('Y') }} Pengadilan Agama Makassar
            </p>
        </div>

        {{-- Panel kanan — form --}}
        <div class="flex-1 flex items-center justify-center p-6 sm:p-12">
            <div class="w-full max-w-md">

                {{-- Logo mobile --}}
                <div class="flex items-center gap-3 mb-8 lg:hidden">
                    <div class="w-9 h-9 bg-blue-900 rounded-lg flex items-center justify-center">
                        <span class="text-yellow-400 font-bold text-sm">PA</span>
                    </div>
                    <div>
                        <p class="font-bold text-gray-800 text-sm leading-tight">Sistem Informasi Cuti</p>
                        <p class="text-gray-500 text-xs">Pengadilan Agama Makassar</p>
                    </div>
                </div>

                {{ $slot }}
            </div>
        </div>
    </div>

</body>
</html>
