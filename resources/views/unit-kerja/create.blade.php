<x-app-layout title="Tambah Unit Kerja">

    <div class="mb-4">
        <a href="{{ route('unit-kerja.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar Unit Kerja
        </a>
    </div>

    <x-form-card title="Tambah Unit Kerja Baru">
        <form method="POST" action="{{ route('unit-kerja.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Unit Kerja <span class="text-red-500">*</span></label>
                <input type="text" name="nama_unit" value="{{ old('nama_unit') }}"
                       placeholder="Contoh: Kepaniteraan Gugatan"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                              @error('nama_unit') border-red-400 @enderror">
                @error('nama_unit')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                    Simpan
                </button>
                <a href="{{ route('unit-kerja.index') }}"
                   class="text-sm text-gray-600 hover:text-gray-800">Batal</a>
            </div>
        </form>
    </x-form-card>

</x-app-layout>
