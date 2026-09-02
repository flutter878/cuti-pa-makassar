<x-app-layout title="Edit Jabatan">

    <div class="mb-4">
        <a href="{{ route('jabatan.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar Jabatan
        </a>
    </div>

    <x-form-card title="Edit Jabatan">
        <form method="POST" action="{{ route('jabatan.update', $jabatan) }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Jabatan <span class="text-red-500">*</span></label>
                <input type="text" name="nama_jabatan"
                       value="{{ old('nama_jabatan', $jabatan->nama_jabatan) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                              @error('nama_jabatan') border-red-400 @enderror">
                @error('nama_jabatan')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                    Perbarui
                </button>
                <a href="{{ route('jabatan.index') }}"
                   class="text-sm text-gray-600 hover:text-gray-800">Batal</a>
            </div>
        </form>
    </x-form-card>

</x-app-layout>
