<x-guest-layout>

    <h2 class="text-2xl font-bold text-gray-800 mb-1">Masuk</h2>
    <p class="text-sm text-gray-500 mb-7">Masukkan kredensial akun Anda</p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                Email
            </label>
            <input id="email" type="email" name="email"
                   value="{{ old('email') }}"
                   required autofocus autocomplete="username"
                   placeholder="nama@pa-makassar.go.id"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                          @error('email') border-red-400 bg-red-50 @enderror">
            @error('email')
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
    </form>

</x-guest-layout>
