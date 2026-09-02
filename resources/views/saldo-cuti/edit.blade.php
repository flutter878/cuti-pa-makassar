<x-app-layout title="Edit Saldo Cuti">

    <div class="mb-4">
        <a href="{{ route('saldo-cuti.index', ['tahun' => $tahun]) }}"
           class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar Saldo
        </a>
    </div>

    <div class="max-w-lg">
        <x-form-card title="Edit Saldo Cuti — {{ $pegawai->nama }}">

            <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-100 text-sm">
                <p class="font-medium text-blue-900">{{ $pegawai->nama }}</p>
                <p class="text-blue-700 text-xs mt-0.5">NIP: {{ $pegawai->nip }}</p>
            </div>

            <form method="POST" action="{{ route('saldo-cuti.update', $pegawai) }}" class="space-y-4">
                @csrf @method('PUT')
                <input type="hidden" name="tahun" value="{{ $tahun }}">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                    <input type="text" value="{{ $tahun }}" disabled
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Hak Cuti <span class="text-red-500">*</span>
                        <span class="text-gray-400 font-normal">(hari)</span>
                    </label>
                    <input type="number" name="hak_cuti"
                           value="{{ old('hak_cuti', $saldo->hak_cuti) }}"
                           min="0" max="365"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('hak_cuti') border-red-400 @enderror">
                    @error('hak_cuti')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    <p class="text-xs text-gray-400 mt-1">Default: 12 hari per tahun</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Carry Over <span class="text-red-500">*</span>
                        <span class="text-gray-400 font-normal">(hari, maks. 6)</span>
                    </label>
                    <input type="number" name="carry_over"
                           value="{{ old('carry_over', $saldo->carry_over) }}"
                           min="0" max="6"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('carry_over') border-red-400 @enderror">
                    @error('carry_over')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    <p class="text-xs text-gray-400 mt-1">Sisa cuti yang dibawa dari tahun sebelumnya (maks. 6 hari)</p>
                </div>

                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 text-sm">
                    <p class="text-gray-500 text-xs mb-1">Terpakai saat ini</p>
                    <p class="font-semibold text-gray-800">{{ $saldo->terpakai }} hari</p>
                    <p class="text-gray-400 text-xs mt-1">Nilai ini otomatis berubah saat cuti disetujui.</p>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                        Simpan
                    </button>
                    <a href="{{ route('saldo-cuti.index', ['tahun' => $tahun]) }}"
                       class="text-sm text-gray-600 hover:text-gray-800">Batal</a>
                </div>
            </form>
        </x-form-card>
    </div>

</x-app-layout>
