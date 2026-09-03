<x-app-layout title="Tambah Pengguna">
    <div class="mb-5">
        <a href="{{ route('pengguna.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar Pengguna
        </a>
    </div>

    <div class="max-w-xl">
        <x-form-card title="Tambah Pengguna Baru">
            <form method="POST" action="{{ route('pengguna.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Nama pengguna">
                    @error('name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                           class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="email@pa-makassar.go.id">
                    @error('email')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="password" name="password" required
                           class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Minimal 8 karakter">
                    @error('password')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="role_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Role <span class="text-red-500">*</span>
                    </label>
                    <select id="role_id" name="role_id" required
                            class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">— Pilih Role —</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="pegawai_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Hubungkan ke Pegawai
                        <span class="text-gray-400 font-normal">(opsional, untuk role Pegawai)</span>
                    </label>
                    <select id="pegawai_id" name="pegawai_id"
                            class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">— Tidak dihubungkan —</option>
                        @foreach($pegawai as $p)
                            <option value="{{ $p->id }}" {{ old('pegawai_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->nama }} — {{ $p->nip }}
                            </option>
                        @endforeach
                    </select>
                    @error('pegawai_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-400 mt-1">Hanya pegawai aktif yang belum memiliki akun yang ditampilkan.</p>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select id="status" name="status" required
                            class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="aktif" {{ old('status', 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ old('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <div class="pt-2 flex gap-3">
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-blue-900 hover:bg-blue-800 text-white
                                   text-sm font-semibold px-5 py-2.5 rounded-lg transition-colors">
                        Simpan
                    </button>
                    <a href="{{ route('pengguna.index') }}"
                       class="inline-flex items-center gap-2 border border-gray-200 text-gray-600 hover:bg-gray-50
                              text-sm font-medium px-5 py-2.5 rounded-lg transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </x-form-card>
    </div>
</x-app-layout>
