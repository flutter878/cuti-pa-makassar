<x-app-layout title="Tambah Pegawai">

    <div class="mb-4">
        <a href="{{ route('pegawai.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar Pegawai
        </a>
    </div>

    <form method="POST" action="{{ route('pegawai.store') }}" class="space-y-5" id="formPegawai">
        @csrf

        {{-- Data Pegawai --}}
        <x-form-card title="Data Pegawai">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIP <span class="text-red-500">*</span></label>
                    <input type="text" name="nip" value="{{ old('nip') }}" maxlength="20"
                           placeholder="18 digit NIP"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nip') border-red-400 @enderror">
                    @error('nip')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nama') border-red-400 @enderror">
                    @error('nama')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-400 @enderror">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                    <input type="text" name="no_telepon" value="{{ old('no_telepon') }}" maxlength="20"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                    <select name="jabatan_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">— Pilih Jabatan —</option>
                        @foreach($jabatan as $j)
                            <option value="{{ $j->id }}" {{ old('jabatan_id') == $j->id ? 'selected' : '' }}>
                                {{ $j->nama_jabatan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Unit Kerja</label>
                    <select name="unit_kerja_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">— Pilih Unit Kerja —</option>
                        @foreach($unitKerja as $u)
                            <option value="{{ $u->id }}" {{ old('unit_kerja_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->nama_unit }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="aktif" {{ old('status', 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ old('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

            </div>
        </x-form-card>

        {{-- Akun User --}}
        <x-form-card title="Akun Login (Opsional)">
            <div class="mb-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="buat_akun" value="1"
                           id="buatAkun" {{ old('buat_akun') ? 'checked' : '' }}
                           class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                           onchange="toggleAkun(this)">
                    <span class="text-sm font-medium text-gray-700">Buat akun login untuk pegawai ini</span>
                </label>
            </div>

            <div id="formAkun" class="{{ old('buat_akun') ? '' : 'hidden' }} grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Login <span class="text-red-500">*</span></label>
                    <input type="email" name="email_login" value="{{ old('email_login') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email_login') border-red-400 @enderror">
                    @error('email_login')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-400 @enderror"
                           placeholder="Min. 8 karakter">
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                    <select name="role_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">— Pilih Role —</option>
                        @foreach(\App\Models\Role::orderBy('name')->get() as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </x-form-card>

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium px-6 py-2.5 rounded-lg transition-colors">
                Simpan Pegawai
            </button>
            <a href="{{ route('pegawai.index') }}"
               class="text-sm text-gray-600 hover:text-gray-800">Batal</a>
        </div>
    </form>

    <script>
        function toggleAkun(checkbox) {
            document.getElementById('formAkun').classList.toggle('hidden', !checkbox.checked);
        }
    </script>

</x-app-layout>
