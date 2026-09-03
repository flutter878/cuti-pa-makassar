<x-app-layout title="Edit Pegawai">

    <div class="mb-4">
        <a href="{{ route('pegawai.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar Pegawai
        </a>
    </div>

    <form method="POST" action="{{ route('pegawai.update', $pegawai) }}" class="space-y-5">
        @csrf @method('PUT')

        <x-form-card title="Data Pegawai">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIP <span class="text-red-500">*</span></label>
                    <input type="text" name="nip" value="{{ old('nip', $pegawai->nip) }}" maxlength="20"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nip') border-red-400 @enderror">
                    @error('nip')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama', $pegawai->nama) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nama') border-red-400 @enderror">
                    @error('nama')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $pegawai->email) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-400 @enderror">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                    <input type="text" name="no_telepon" value="{{ old('no_telepon', $pegawai->no_telepon) }}" maxlength="20"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                    <select name="jabatan_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">— Pilih Jabatan —</option>
                        @foreach($jabatan as $j)
                            <option value="{{ $j->id }}" {{ old('jabatan_id', $pegawai->jabatan_id) == $j->id ? 'selected' : '' }}>
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
                            <option value="{{ $u->id }}" {{ old('unit_kerja_id', $pegawai->unit_kerja_id) == $u->id ? 'selected' : '' }}>
                                {{ $u->nama_unit }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Atasan Langsung
                        <span class="text-gray-400 font-normal text-xs">(Panitera / Sekretaris yang membawahi pegawai ini)</span>
                    </label>
                    <select name="atasan_langsung_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">— Tidak ada atasan langsung —</option>
                        @foreach($calonAtasan as $a)
                            <option value="{{ $a->id }}"
                                    {{ old('atasan_langsung_id', $pegawai->atasan_langsung_id) == $a->id ? 'selected' : '' }}>
                                {{ $a->nama }} — {{ $a->jabatan?->nama_jabatan }}
                            </option>
                        @endforeach
                    </select>
                    @error('atasan_langsung_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-400 mt-1">
                        Pengajuan cuti pegawai ini akan diteruskan ke atasan langsung sebelum ke Ketua.
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="aktif" {{ old('status', $pegawai->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ old('status', $pegawai->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

            </div>
        </x-form-card>

        {{-- Info akun jika sudah ada --}}
        @if($pegawai->user)
            <x-form-card title="Akun Login">
                <div class="flex items-center gap-3 p-3 bg-blue-50 rounded-lg border border-blue-100">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $pegawai->user->email }}</p>
                        <p class="text-xs text-gray-500">Role: {{ $pegawai->user->role?->name }} &bull; Status: {{ ucfirst($pegawai->user->status) }}</p>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-2">Untuk mengubah password atau role, gunakan menu Pengguna.</p>
            </x-form-card>
        @endif

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium px-6 py-2.5 rounded-lg transition-colors">
                Simpan Perubahan
            </button>
            <a href="{{ route('pegawai.index') }}"
               class="text-sm text-gray-600 hover:text-gray-800">Batal</a>
        </div>
    </form>

</x-app-layout>
