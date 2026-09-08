<x-app-layout title="Profil Saya">

    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800">Profil Saya</h2>
        <p class="text-sm text-gray-500 mt-1">Kelola informasi akun dan keamanan login Anda.</p>
    </div>

    @php $user = auth()->user(); $pegawai = $user->pegawai; @endphp

    {{-- Banner peringatan jika masih pakai password default (NIP) --}}
    @if($pegawai && \Illuminate\Support\Facades\Hash::check($pegawai->nip, $user->password))
        <div class="mb-5 flex items-start gap-3 px-4 py-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <p class="font-semibold">Anda masih menggunakan password default (NIP)</p>
                <p class="text-amber-700 mt-0.5">Segera ubah password Anda untuk keamanan akun. Gunakan form di bawah.</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Info Akun --}}
        <x-form-card title="Informasi Akun">
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between py-2 border-b border-gray-50">
                    <dt class="text-gray-500">Nama</dt>
                    <dd class="font-medium text-gray-800">{{ $user->name }}</dd>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-50">
                    <dt class="text-gray-500">Email</dt>
                    <dd class="text-gray-700">{{ $user->email }}</dd>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-50">
                    <dt class="text-gray-500">Role</dt>
                    <dd>
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold
                            {{ $user->isSuperadmin() ? 'bg-purple-100 text-purple-700' : ($user->isAdmin() ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
                            {{ ucfirst($user->role?->name ?? '—') }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between py-2">
                    <dt class="text-gray-500">Status</dt>
                    <dd>
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold
                            {{ $user->status === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                            {{ ucfirst($user->status) }}
                        </span>
                    </dd>
                </div>
            </dl>

            @if($pegawai)
                <hr class="my-4 border-gray-100">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Data Pegawai</p>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between py-1.5 border-b border-gray-50">
                        <dt class="text-gray-500">NIP</dt>
                        <dd class="font-mono text-gray-700">{{ $pegawai->nip }}</dd>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-gray-50">
                        <dt class="text-gray-500">Jabatan</dt>
                        <dd class="text-gray-700">{{ $pegawai->jabatan?->nama_jabatan ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <dt class="text-gray-500">Unit Kerja</dt>
                        <dd class="text-gray-700">{{ $pegawai->unitKerja?->nama_unit ?? '—' }}</dd>
                    </div>
                </dl>
            @endif
        </x-form-card>

        {{-- Ganti Password --}}
        <div class="space-y-5">
            <x-form-card title="Ubah Data Akun">

                @if(session('status') === 'profile-updated')
                    <div class="mb-4 flex items-center gap-2 px-3 py-2.5 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Data akun berhasil diperbarui.
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Nama <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name"
                               value="{{ old('name', $user->name) }}"
                               required autocomplete="name"
                               class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500
                                      @error('name') border-red-400 bg-red-50 @enderror">
                        @error('name')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email"
                               value="{{ old('email', $user->email) }}"
                               required autocomplete="email"
                               class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500
                                      @error('email') border-red-400 bg-red-50 @enderror">
                        @error('email')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-400 mt-1">
                            Email juga bisa digunakan untuk login.
                        </p>
                    </div>

                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-blue-900 hover:bg-blue-800 text-white
                                   text-sm font-semibold px-5 py-2.5 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 13l4 4L19 7"/>
                        </svg>
                        Simpan Perubahan
                    </button>
                </form>
            </x-form-card>

            <x-form-card title="Ganti Password">

                @if(session('status') === 'password-updated')
                    <div class="mb-4 flex items-center gap-2 px-3 py-2.5 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Password berhasil diperbarui.
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">
                            Password Saat Ini <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="current_password" name="current_password"
                               autocomplete="current-password"
                               class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500
                                      @error('current_password', 'updatePassword') border-red-400 bg-red-50 @enderror">
                        @error('current_password', 'updatePassword')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Password Baru <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="password" name="password"
                               autocomplete="new-password"
                               class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500
                                      @error('password', 'updatePassword') border-red-400 bg-red-50 @enderror"
                               placeholder="Minimal 8 karakter">
                        @error('password', 'updatePassword')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                            Konfirmasi Password Baru <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               autocomplete="new-password"
                               class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-blue-900 hover:bg-blue-800 text-white
                                   text-sm font-semibold px-5 py-2.5 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Perbarui Password
                    </button>
                </form>
            </x-form-card>
        </div>

    </div>
</x-app-layout>
