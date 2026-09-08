<x-app-layout title="Edit Hari Libur">

    <div class="mb-5">
        <a href="{{ route('hari-libur.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar
        </a>
    </div>

    <div class="max-w-lg">
        <x-form-card title="Edit Hari Libur">
            <form method="POST" action="{{ route('hari-libur.update', $hariLibur) }}">
                @csrf @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tanggal <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal"
                               value="{{ old('tanggal', $hariLibur->tanggal->format('Y-m-d')) }}"
                               class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 @error('tanggal') border-red-400 @enderror">
                        @error('tanggal')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nama Hari Libur <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nama"
                               value="{{ old('nama', $hariLibur->nama) }}"
                               class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 @error('nama') border-red-400 @enderror">
                        @error('nama')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                        <input type="text" name="keterangan"
                               value="{{ old('keterangan', $hariLibur->keterangan) }}"
                               class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="aktif" id="aktif" value="1"
                               {{ old('aktif', $hariLibur->aktif) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="aktif" class="text-sm text-gray-700">Aktif (dihitung sebagai hari libur)</label>
                    </div>
                </div>

                <div class="mt-5 flex gap-2">
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                        Perbarui
                    </button>
                    <a href="{{ route('hari-libur.index') }}"
                       class="border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </x-form-card>
    </div>

</x-app-layout>
