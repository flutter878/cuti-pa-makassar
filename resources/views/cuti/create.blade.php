<x-app-layout title="Ajukan Cuti">

    <div class="mb-4">
        <a href="{{ route('cuti.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Riwayat Cuti
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ===== FORM ===== --}}
        <div class="lg:col-span-2">
            <x-form-card title="Form Pengajuan Cuti">
                <form method="POST" action="{{ route('cuti.store') }}"
                      enctype="multipart/form-data"
                      id="formCuti"
                      class="space-y-4">
                    @csrf

                    {{-- Jenis Cuti --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Jenis Cuti <span class="text-red-500">*</span>
                        </label>
                        <select name="jenis_cuti_id" id="jenisCuti" required
                                onchange="handleJenisCuti(this)"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                       @error('jenis_cuti_id') border-red-400 @enderror">
                            <option value="">— Pilih Jenis Cuti —</option>
                            @foreach($jenisCuti as $jc)
                                <option value="{{ $jc->id }}"
                                        data-perlu-lampiran="{{ $jc->membutuhkan_lampiran ? '1' : '0' }}"
                                        data-mengurangi-saldo="{{ $jc->mengurangi_saldo ? '1' : '0' }}"
                                        data-kode="{{ $jc->kode }}"
                                        {{ old('jenis_cuti_id') == $jc->id ? 'selected' : '' }}>
                                    {{ $jc->nama }}
                                    @if($jc->membutuhkan_lampiran) (perlu lampiran) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('jenis_cuti_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror

                        {{-- Info H-3 --}}
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
                                   value="{{ old('tanggal_mulai') }}"
                                   min="{{ now()->toDateString() }}"
                                   max="{{ now()->addDays(30)->toDateString() }}"
                                   onchange="hitungHariOtomatis()"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                          @error('tanggal_mulai') border-red-400 @enderror">
                            <p id="infoMinTanggal" class="text-xs text-gray-400 mt-1">
                                Min. {{ now()->format('d/m/Y') }} &nbsp;|&nbsp; Maks. {{ now()->addDays(30)->format('d/m/Y') }}
                            </p>
                            @error('tanggal_mulai')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal Selesai <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal_selesai" id="tanggalSelesai"
                                   value="{{ old('tanggal_selesai') }}"
                                   min="{{ now()->toDateString() }}"
                                   onchange="hitungHariOtomatis()"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                          @error('tanggal_selesai') border-red-400 @enderror">
                            @error('tanggal_selesai')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Jumlah hari (readonly, otomatis) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Hari</label>
                        <div class="flex items-center gap-3">
                            <input type="number" name="jumlah_hari" id="jumlahHari"
                                   value="{{ old('jumlah_hari', 0) }}" readonly
                                   class="w-32 border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-700 font-semibold">
                            <span class="text-sm text-gray-500">hari (dihitung otomatis)</span>
                        </div>
                    </div>

                    {{-- Alasan --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Alasan Cuti <span class="text-red-500">*</span>
                        </label>
                        <textarea name="alasan" rows="3" maxlength="500"
                                  placeholder="Jelaskan alasan pengajuan cuti..."
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                         @error('alasan') border-red-400 @enderror">{{ old('alasan') }}</textarea>
                        @error('alasan')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Alamat selama cuti --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Alamat Selama Cuti <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="alamat_cuti"
                               value="{{ old('alamat_cuti') }}"
                               placeholder="Alamat lengkap selama cuti"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                      @error('alamat_cuti') border-red-400 @enderror">
                        @error('alamat_cuti')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- No telepon --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon Selama Cuti</label>
                        <input type="text" name="no_telepon"
                               value="{{ old('no_telepon', $pegawai->no_telepon) }}"
                               maxlength="20"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Lampiran (tampil kondisional) --}}
                    <div id="seksiLampiran" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Lampiran <span class="text-red-500">*</span>
                            <span class="text-gray-400 font-normal">(PDF/JPG/PNG, maks. 5 MB)</span>
                        </label>
                        <input type="file" name="lampiran" id="inputLampiran"
                               accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                      @error('lampiran') border-red-400 @enderror">
                        @error('lampiran')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit"
                                class="bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium px-6 py-2.5 rounded-lg transition-colors">
                            Ajukan Cuti
                        </button>
                        <a href="{{ route('cuti.index') }}"
                           class="text-sm text-gray-600 hover:text-gray-800">Batal</a>
                    </div>
                </form>
            </x-form-card>
        </div>

        {{-- ===== SIDEBAR INFO SALDO ===== --}}
        <div class="space-y-4">

            {{-- Info Pegawai --}}
            <x-form-card title="Identitas">
                <dl class="space-y-2 text-sm">
                    <div>
                        <dt class="text-gray-500 text-xs">Nama</dt>
                        <dd class="font-medium text-gray-800">{{ $pegawai->nama }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">NIP</dt>
                        <dd class="font-mono text-gray-700">{{ $pegawai->nip }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Jabatan</dt>
                        <dd class="text-gray-700">{{ $pegawai->jabatan?->nama_jabatan ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 text-xs">Unit Kerja</dt>
                        <dd class="text-gray-700">{{ $pegawai->unitKerja?->nama_unit ?? '—' }}</dd>
                    </div>
                </dl>
            </x-form-card>

            {{-- Saldo Cuti --}}
            <x-form-card title="Saldo Cuti Tahunan">
                @forelse($saldo as $s)
                    <div class="mb-3 p-3 rounded-lg border {{ $s->tahun == now()->year ? 'bg-blue-50 border-blue-200' : 'bg-gray-50 border-gray-200' }} last:mb-0">
                        <p class="text-xs font-semibold {{ $s->tahun == now()->year ? 'text-blue-900' : 'text-gray-600' }} mb-2">
                            Tahun {{ $s->tahun }}
                            @if($s->tahun == now()->year)
                                <span class="ml-1 text-xs bg-blue-200 text-blue-800 px-1.5 py-0.5 rounded">Tahun ini</span>
                            @endif
                        </p>
                        <div class="grid grid-cols-2 gap-1 text-xs">
                            <span class="text-gray-500">Hak:</span>
                            <span class="font-medium">{{ $s->hak_cuti }} hari</span>
                            <span class="text-gray-500">Carry Over:</span>
                            <span class="font-medium">{{ $s->carry_over }} hari</span>
                            <span class="text-gray-500">Terpakai:</span>
                            <span class="font-medium text-red-600">{{ $s->terpakai }} hari</span>
                            <span class="text-gray-500 font-semibold">Sisa:</span>
                            <span class="font-bold text-green-600">{{ $s->sisa }} hari</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 italic">Belum ada data saldo.</p>
                @endforelse
            </x-form-card>

            {{-- Info aturan --}}
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 text-xs text-blue-800 space-y-1.5">
                <p class="font-semibold">Aturan Pengajuan:</p>
                <p>• Cuti Tahunan wajib diajukan minimal <strong>H-3</strong> sebelum tanggal mulai.</p>
                <p>• Saldo terlama digunakan terlebih dahulu (FIFO).</p>
                <p>• Saldo dikurangi <strong>setelah persetujuan</strong> admin.</p>
                <p>• Lampiran wajib untuk jenis cuti tertentu.</p>
            </div>
        </div>
    </div>

    <script>
        // Tanggal batas dari server (format YYYY-MM-DD)
        const TODAY      = '{{ now()->toDateString() }}';
        const MAX_DATE   = '{{ now()->addDays(30)->toDateString() }}';
        const H3_MIN     = '{{ now()->addDays(3)->toDateString() }}'; // H+3 = minimal tanggal mulai CT

        function formatTanggal(ymd) {
            // YYYY-MM-DD → DD/MM/YYYY
            const [y, m, d] = ymd.split('-');
            return `${d}/${m}/${y}`;
        }

        function handleJenisCuti(select) {
            const opt  = select.options[select.selectedIndex];
            const kode = opt.dataset.kode ?? '';
            const perluLampiran = opt.dataset.perluLampiran === '1';

            const inputMulai   = document.getElementById('tanggalMulai');
            const infoMin      = document.getElementById('infoMinTanggal');
            const infoH3       = document.getElementById('infoH3');

            if (kode === 'CT') {
                // Cuti Tahunan: min = H+3 dari hari ini
                inputMulai.min = H3_MIN;
                infoH3.classList.remove('hidden');
                infoMin.textContent = `Min. ${formatTanggal(H3_MIN)} (H-3) | Maks. ${formatTanggal(MAX_DATE)}`;

                // Jika tanggal yang sudah dipilih < H3_MIN, reset
                if (inputMulai.value && inputMulai.value < H3_MIN) {
                    inputMulai.value = '';
                    document.getElementById('tanggalSelesai').value = '';
                    document.getElementById('jumlahHari').value = 0;
                }
            } else {
                // Jenis cuti lain: min = hari ini
                inputMulai.min = TODAY;
                infoH3.classList.add('hidden');
                infoMin.textContent = `Min. ${formatTanggal(TODAY)} | Maks. ${formatTanggal(MAX_DATE)}`;
            }

            // Tampilkan/sembunyikan seksi lampiran
            const seksi = document.getElementById('seksiLampiran');
            const input = document.getElementById('inputLampiran');
            seksi.classList.toggle('hidden', !perluLampiran);
            input.required = perluLampiran;

            // Hitung ulang hari jika tanggal sudah terisi
            hitungHariOtomatis();
        }

        function hitungHariOtomatis() {
            const mulai   = document.getElementById('tanggalMulai').value;
            const selesai = document.getElementById('tanggalSelesai').value;
            const output  = document.getElementById('jumlahHari');

            if (!mulai || !selesai || selesai < mulai) {
                output.value = 0;
                return;
            }

            // Panggil backend untuk hitung hari kerja (skip weekend + libur)
            fetch(`{{ route('cuti.hitung-hari') }}?tanggal_mulai=${mulai}&tanggal_selesai=${selesai}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                output.value = data.jumlah_hari ?? 0;
            })
            .catch(() => {
                // Fallback hitung hari kalender jika AJAX gagal
                const diff = Math.floor((new Date(selesai) - new Date(mulai)) / 86400000) + 1;
                output.value = diff;
            });
        }

        // Saat tanggal mulai berubah, update min tanggal selesai
        document.getElementById('tanggalMulai').addEventListener('change', function () {
            const selesaiInput = document.getElementById('tanggalSelesai');
            if (this.value) {
                selesaiInput.min = this.value;
                // Reset selesai jika sekarang < mulai
                if (selesaiInput.value && selesaiInput.value < this.value) {
                    selesaiInput.value = this.value;
                }
            }
            hitungHariOtomatis();
        });

        // Inisialisasi saat halaman load (jika ada old values)
        document.addEventListener('DOMContentLoaded', () => {
            const select = document.getElementById('jenisCuti');
            if (select.value) handleJenisCuti(select);
            hitungHariOtomatis();
        });
    </script>

</x-app-layout>
