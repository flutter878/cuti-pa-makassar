<x-app-layout title="Perbaiki Pengajuan Cuti">

    <div class="mb-4">
        <a href="{{ route('cuti.show', $cuti) }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Detail Pengajuan
        </a>
    </div>

    {{-- Notifikasi alasan dikembalikan --}}
    @if($cuti->catatan)
        <div class="mb-5 rounded-lg bg-orange-50 border border-orange-300 px-5 py-4 text-sm text-orange-900">
            <p class="font-bold mb-1">Catatan dari Pejabat:</p>
            <p>{{ $cuti->catatan }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Form --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-800 mb-4">Perbaiki Data Pengajuan</h3>
                <p class="text-sm text-gray-500 mb-5">
                    Nomor Pengajuan: <span class="font-mono font-semibold text-gray-700">{{ $cuti->nomor_pengajuan }}</span>
                </p>

                <form method="POST" action="{{ route('cuti.update', $cuti) }}"
                      enctype="multipart/form-data" id="formCuti" class="space-y-4">
                    @csrf @method('PUT')

                    {{-- Jenis Cuti --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Jenis Cuti <span class="text-red-500">*</span>
                        </label>
                        <select name="jenis_cuti_id" id="jenisCuti" required
                                onchange="handleJenisCuti(this)"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                       @error('jenis_cuti_id') border-red-400 @enderror">
                            @foreach($jenisCuti as $jc)
                                <option value="{{ $jc->id }}"
                                        data-perlu-lampiran="{{ $jc->membutuhkan_lampiran ? '1' : '0' }}"
                                        data-mengurangi-saldo="{{ $jc->mengurangi_saldo ? '1' : '0' }}"
                                        data-kode="{{ $jc->kode }}"
                                        {{ old('jenis_cuti_id', $cuti->jenis_cuti_id) == $jc->id ? 'selected' : '' }}>
                                    {{ $jc->nama }}
                                    @if($jc->membutuhkan_lampiran) (perlu lampiran) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('jenis_cuti_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <div id="infoH3" class="hidden mt-2 p-2.5 bg-yellow-50 border border-yellow-200 rounded-lg text-xs text-yellow-800">
                            <strong>Aturan H-3:</strong> Cuti Tahunan harus diajukan minimal 3 hari sebelum tanggal mulai cuti.
                        </div>
                    </div>

                    {{-- Tanggal --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal Mulai <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal_mulai" id="tanggalMulai"
                                   value="{{ old('tanggal_mulai', $cuti->tanggal_mulai->format('Y-m-d')) }}"
                                   min="{{ now()->toDateString() }}"
                                   max="{{ now()->addDays(30)->toDateString() }}"
                                   onchange="hitungHariOtomatis()"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                          @error('tanggal_mulai') border-red-400 @enderror">
                            @error('tanggal_mulai')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal Selesai <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal_selesai" id="tanggalSelesai"
                                   value="{{ old('tanggal_selesai', $cuti->tanggal_selesai->format('Y-m-d')) }}"
                                   min="{{ now()->toDateString() }}"
                                   onchange="hitungHariOtomatis()"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                          @error('tanggal_selesai') border-red-400 @enderror">
                            @error('tanggal_selesai')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Jumlah hari --}}
                    <div class="flex items-center gap-3 px-4 py-3 bg-blue-50 rounded-lg">
                        <span class="text-sm text-blue-700">Jumlah hari kerja:</span>
                        <span id="jumlahHari" class="text-xl font-bold text-blue-900">{{ $cuti->jumlah_hari }}</span>
                        <span class="text-sm text-blue-700">hari</span>
                        <span id="loadingHari" class="hidden text-xs text-gray-500">Menghitung...</span>
                    </div>

                    {{-- Alasan --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Alasan Cuti <span class="text-red-500">*</span>
                        </label>
                        <textarea name="alasan" rows="3" required
                                  placeholder="Jelaskan alasan pengajuan cuti..."
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                         @error('alasan') border-red-400 @enderror">{{ old('alasan', $cuti->alasan) }}</textarea>
                        @error('alasan')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Alamat & Telepon --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Alamat Selama Cuti <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="alamat_cuti"
                                   value="{{ old('alamat_cuti', $cuti->alamat_cuti) }}" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                          @error('alamat_cuti') border-red-400 @enderror">
                            @error('alamat_cuti')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                            <input type="text" name="no_telepon"
                                   value="{{ old('no_telepon', $cuti->no_telepon) }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    {{-- Lampiran --}}
                    <div id="lampiranSection">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Lampiran
                            <span id="lampiranRequired" class="hidden text-red-500">*</span>
                        </label>
                        @if($cuti->dokumen->count())
                            <p class="text-xs text-gray-500 mb-1">
                                Lampiran saat ini: {{ $cuti->dokumen->first()?->nama_file }}
                                — Upload baru untuk mengganti.
                            </p>
                        @endif
                        <input type="file" name="lampiran" id="lampiran"
                               accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-400 mt-1">Format: PDF, JPG, PNG. Maks. 5 MB.</p>
                        @error('lampiran')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2 flex gap-3">
                        <button type="submit"
                                class="flex-1 bg-orange-500 text-white font-semibold py-2.5 rounded-lg hover:bg-orange-600 transition-colors text-sm">
                            Ajukan Ulang
                        </button>
                        <a href="{{ route('cuti.show', $cuti) }}"
                           class="px-4 py-2.5 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Sidebar: saldo --}}
        <div class="space-y-4">
            @if($saldo->isNotEmpty())
                <x-form-card title="Saldo Cuti Anda">
                    @foreach($saldo as $s)
                        <div class="flex justify-between items-center text-sm py-2 border-b border-gray-100 last:border-0">
                            <div>
                                <p class="font-medium text-gray-700">Tahun {{ $s->tahun }}</p>
                                <p class="text-xs text-gray-500">Hak: {{ $s->hak_cuti }} + Carry: {{ $s->carry_over }}</p>
                            </div>
                            <span class="font-bold text-lg {{ $s->sisa > 5 ? 'text-green-600' : ($s->sisa > 0 ? 'text-yellow-600' : 'text-red-600') }}">
                                {{ $s->sisa }}
                            </span>
                        </div>
                    @endforeach
                </x-form-card>
            @endif

            {{-- Riwayat tindakan --}}
            @if($cuti->auditTrail->count())
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Riwayat Pengajuan</h4>
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @foreach($cuti->auditTrail->sortByDesc('created_at') as $trail)
                            <div class="text-xs">
                                <p class="font-semibold text-gray-700">{{ $trail->aktor }}</p>
                                <p class="text-gray-600">{{ $trail->aksiLabel() }}</p>
                                <p class="text-gray-400">{{ $trail->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        function handleJenisCuti(select) {
            const opt     = select.options[select.selectedIndex];
            const perlu   = opt.dataset.perluLampiran === '1';
            const kode    = opt.dataset.kode || '';
            const h3Info  = document.getElementById('infoH3');
            const req     = document.getElementById('lampiranRequired');

            h3Info.classList.toggle('hidden', kode !== 'CT');
            if (req) req.classList.toggle('hidden', ! perlu);
        }

        function hitungHariOtomatis() {
            const mulai   = document.getElementById('tanggalMulai').value;
            const selesai = document.getElementById('tanggalSelesai').value;
            const display = document.getElementById('jumlahHari');
            const loading = document.getElementById('loadingHari');

            if (! mulai || ! selesai) return;
            if (selesai < mulai) return;

            loading?.classList.remove('hidden');

            fetch(`{{ route('cuti.hitung-hari') }}?tanggal_mulai=${mulai}&tanggal_selesai=${selesai}`)
                .then(r => r.json())
                .then(data => {
                    display.textContent = data.jumlah_hari;
                    loading?.classList.add('hidden');
                })
                .catch(() => loading?.classList.add('hidden'));
        }

        // Inisialisasi
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('jenisCuti');
            if (select) handleJenisCuti(select);
        });
    </script>

</x-app-layout>
