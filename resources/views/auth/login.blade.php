<x-guest-layout>

    <h2 class="text-2xl font-bold text-gray-800 mb-1">Masuk</h2>
    <p class="text-sm text-gray-500 mb-7">Sistem Informasi Cuti — Pengadilan Agama Makassar</p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        {{-- NIP / Email --}}
        <div>
            <label for="login" class="block text-sm font-medium text-gray-700 mb-1">
                NIP / Email
            </label>
            <input id="login" type="text" name="login"
                   value="{{ old('login') }}"
                   required autofocus autocomplete="username"
                   placeholder="Masukkan NIP atau email"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                          @error('login') border-red-400 bg-red-50 @enderror">
            @error('login')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <div class="flex items-center justify-between mb-1">
                <label for="password" class="block text-sm font-medium text-gray-700">
                    Password
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       class="text-xs text-blue-600 hover:text-blue-800">
                        Lupa password?
                    </a>
                @endif
            </div>
            <input id="password" type="password" name="password"
                   required autocomplete="current-password"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                          @error('password') border-red-400 bg-red-50 @enderror">
            @error('password')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Remember me --}}
        <div class="flex items-center gap-2">
            <input id="remember_me" type="checkbox" name="remember"
                   class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <label for="remember_me" class="text-sm text-gray-600">Ingat saya</label>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="w-full bg-blue-900 hover:bg-blue-800 active:bg-blue-950
                       text-white font-semibold text-sm py-2.5 rounded-lg
                       transition-colors duration-150 mt-2">
            Masuk
        </button>

        {{-- Petunjuk login pertama --}}
        <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-100">
            <p class="text-xs text-blue-700 font-semibold mb-1">💡 Login Pertama Kali?</p>
            <p class="text-xs text-blue-600">Gunakan <strong>NIP</strong> sebagai username dan <strong>NIP</strong> sebagai password. Segera ubah password setelah masuk.</p>
        </div>
    </form>

</x-guest-layout>
