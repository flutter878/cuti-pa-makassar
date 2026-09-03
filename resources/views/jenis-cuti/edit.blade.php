<x-app-layout title="Edit Jenis Cuti">
    <div class="mb-5">
        <a href="{{ route('jenis-cuti.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Jenis Cuti
        </a>
    </div>

    <div class="max-w-xl">
        <x-form-card title="Edit Jenis Cuti">
            <form method="POST" action="{{ route('jenis-cuti.update', $jenisCuti) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Jenis Cuti <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama', $jenisCuti->nama) }}" required
                           class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('nama')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="kode" class="block text-sm font-medium text-gray-700 mb-1">
                        Kode <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="kode" name="kode" value="{{ old('kode', $jenisCuti->kode) }}" required maxlength="10"
                           class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 uppercase">
                    @error('kode')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="batas_hari" class="block text-sm font-medium text-gray-700 mb-1">
                        Batas Hari
                    </label>
                    <input type="number" id="batas_hari" name="batas_hari"
                           value="{{ old('batas_hari', $jenisCuti->batas_hari) }}"
                           min="1" max="365"
                           class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Kosongkan jika tidak ada batas">
                    @error('batas_hari')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg">
                        <input type="hidden" name="mengurangi_saldo" value="0">
                        <input type="checkbox" id="mengurangi_saldo" name="mengurangi_saldo" value="1"
                               {{ old('mengurangi_saldo', $jenisCuti->mengurangi_saldo) ? 'checked' : '' }}
                               class="mt-0.5 rounded border-gray-300 text-blue-900 focus:ring-blue-500">
                        <div>
                            <label for="mengurangi_saldo" class="text-sm font-medium text-gray-700 cursor-pointer">
                                Kurangi Saldo
                            </label>
                            <p class="text-xs text-gray-400 mt-0.5">Pengajuan mengurangi saldo cuti pegawai.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg">
                        <input type="hidden" name="membutuhkan_lampiran" value="0">
                        <input type="checkbox" id="membutuhkan_lampiran" name="membutuhkan_lampiran" value="1"
                               {{ old('membutuhkan_lampiran', $jenisCuti->membutuhkan_lampiran) ? 'checked' : '' }}
                               class="mt-0.5 rounded border-gray-300 text-blue-900 focus:ring-blue-500">
                        <div>
                            <label for="membutuhkan_lampiran" class="text-sm font-medium text-gray-700 cursor-pointer">
                                Butuh Lampiran
                            </label>
                            <p class="text-xs text-gray-400 mt-0.5">Pegawai wajib unggah dokumen pendukung.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select id="status" name="status" required
                            class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="aktif" {{ old('status', $jenisCuti->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ old('status', $jenisCuti->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <div class="pt-2 flex gap-3">
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-blue-900 hover:bg-blue-800 text-white
                                   text-sm font-semibold px-5 py-2.5 rounded-lg transition-colors">
                        Perbarui
                    </button>
                    <a href="{{ route('jenis-cuti.index') }}"
                       class="inline-flex items-center gap-2 border border-gray-200 text-gray-600 hover:bg-gray-50
                              text-sm font-medium px-5 py-2.5 rounded-lg transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </x-form-card>
    </div>
</x-app-layout>
