<x-app-layout title="Detail & Verifikasi Pengajuan">

    <div class="mb-5 flex items-center justify-between flex-wrap gap-3">
        <a href="{{ route('admin-verifikasi.index') }}"
           class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar
        </a>
        <a href="{{ route('laporan.formulir', $cuti) }}" target="_blank"
           class="inline-flex items-center gap-2 border border-red-300 text-red-600 hover:bg-red-50 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Download Formulir PDF
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ── Kolom kiri: detail pengajuan ─────────────────── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Header status --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Nomor Pengajuan</p>
                        <p class="font-mono text-lg font-bold text-gray-800">{{ $cuti->nomor_pengajuan }}</p>
                        @if($cuti->nomor_surat)
                            <p class="text-xs text-gray-500 mt-1">
                                Nomor Surat: <span class="font-medium text-gray-700">{{ $cuti->nomor_surat }}</span>
                            </p>
                        @endif
                        <p class="text-xs text-gray-400 mt-1">
                            Diajukan: {{ $cuti->tanggal_pengajuan->format('d M Y, H:i') }} WIT
                        </p>
                    </div>
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $cuti->statusColor() }}">
                        {{ $cuti->statusLabel() }}
                    </span>
                </div>
            </div>

            {{-- Data Pegawai --}}
            <x-form-card title="Data Pegawai">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <dt class="text-gray-500 mb-0.5">Nama</dt>
                        <dd class="font-medium text-gray-800">{{ $cuti->pegawai->nama }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-0.5">NIP</dt>
                        <dd class="font-mono text-gray-700">{{ $cuti->pegawai->nip }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-0.5">Jabatan</dt>
                        <dd class="text-gray-700">{{ $cuti->pegawai->jabatan?->nama_jabatan ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-0.5">Unit Kerja</dt>
                        <dd class="text-gray-700">{{ $cuti->pegawai->unitKerja?->nama_unit ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-0.5">Atasan Langsung</dt>
                        <dd class="text-gray-700">
                            {{ $cuti->pegawai->atasanLangsung?->nama ?? '—' }}
                            @if($cuti->pegawai->atasanLangsung?->jabatan)
                                <span class="text-gray-400">({{ $cuti->pegawai->atasanLangsung->jabatan->nama_jabatan }})</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-0.5">Masa Kerja (diisi Admin)</dt>
                        <dd class="text-gray-700">{{ $cuti->masa_kerja ?? '—' }}</dd>
                    </div>
                </dl>
            </x-form-card>

            {{-- Data Pengajuan --}}
            <x-form-card title="Data Pengajuan Cuti">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <dt class="text-gray-500 mb-0.5">Jenis Cuti</dt>
                        <dd class="font-medium text-gray-800">{{ $cuti->jenisCuti->nama }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-0.5">Jumlah Hari Kerja</dt>
                        <dd class="font-bold text-blue-900 text-lg">{{ $cuti->jumlah_hari }} hari</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-0.5">Tanggal Mulai</dt>
                        <dd class="font-medium text-gray-800">{{ $cuti->tanggal_mulai->format('d F Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-0.5">Tanggal Selesai</dt>
                        <dd class="font-medium text-gray-800">{{ $cuti->tanggal_selesai->format('d F Y') }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-gray-500 mb-0.5">Alasan</dt>
                        <dd class="text-gray-800">{{ $cuti->alasan }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-0.5">Alamat Selama Cuti</dt>
                        <dd class="text-gray-700">{{ $cuti->alamat_cuti }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-0.5">No. Telepon</dt>
                        <dd class="text-gray-700">{{ $cuti->no_telepon ?? '—' }}</dd>
                    </div>
                </dl>
            </x-form-card>

            {{-- Riwayat persetujuan --}}
            @if($cuti->persetujuan->isNotEmpty())
                <x-form-card title="Riwayat Persetujuan">
                    <div class="space-y-3">
                        @foreach($cuti->persetujuan as $p)
                            <div class="flex items-start gap-3 text-sm">
                                <div class="mt-0.5 w-2 h-2 rounded-full flex-shrink-0 mt-1.5
                                    {{ $p->status === 'disetujui' ? 'bg-green-500' : 'bg-red-500' }}"></div>
                                <div>
                                    <p class="font-medium text-gray-800">{{ $p->user->name }}
                                        <span class="text-xs text-gray-400 font-normal">({{ $p->levelLabel() }})</span>
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $p->statusLabel() }} — {{ $p->tanggal_persetujuan?->format('d M Y, H:i') }}</p>
                                    @if($p->catatan)
                                        <p class="text-xs text-gray-500 italic mt-0.5">{{ $p->catatan }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-form-card>
            @endif
        </div>

        {{-- ── Kolom kanan: form verifikasi + lampiran ─────── --}}
        <div class="space-y-4">

            {{-- Form Verifikasi (hanya muncul jika masih menunggu) --}}
            @if($cuti->bisaDiprosesAdmin())
                <div class="bg-white rounded-lg shadow-sm border border-blue-200 p-5">
                    <h3 class="text-sm font-semibold text-blue-800 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Form Verifikasi Admin
                    </h3>

                    <form method="POST" action="{{ route('admin-verifikasi.verifikasi', $cuti) }}">
                        @csrf

                        {{-- Nomor Surat --}}
                        <div class="mb-4">
                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                Nomor Awal Surat <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <input type="text" name="nomor_awal" value="{{ old('nomor_awal') }}"
                                       placeholder="444"
                                       class="w-24 text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 @error('nomor_awal') border-red-400 @enderror">
                                <span class="text-xs text-gray-500">/KPA/SKET.KP4.3/{{ \Carbon\Carbon::now()->locale('id')->isoFormat('MMMM') }}/{{ now()->year }}</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">
                                Preview: <span id="previewNomor" class="font-medium text-gray-600">—</span>
                            </p>
                            @error('nomor_awal')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Masa Kerja --}}
                        <div class="mb-5">
                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                Masa Kerja <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="masa_kerja" value="{{ old('masa_kerja') }}"
                                   placeholder="4 Tahun 6 Bulan"
                                   class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 @error('masa_kerja') border-red-400 @enderror">
                            @error('masa_kerja')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 rounded-lg transition-colors">
                            Verifikasi & Teruskan
                        </button>
                    </form>

                    {{-- Divider --}}
                    <div class="my-3 border-t border-gray-100"></div>

                    {{-- Tombol Tolak --}}
                    <button onclick="document.getElementById('modalTolak').classList.remove('hidden')"
                            class="w-full border border-red-300 text-red-600 hover:bg-red-50 text-sm font-medium py-2 rounded-lg transition-colors">
                        Tolak Pengajuan
                    </button>
                </div>

                {{-- Modal tolak --}}
                <div id="modalTolak" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
                    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
                        <h3 class="text-base font-semibold text-gray-800 mb-3">Tolak Pengajuan</h3>
                        <form method="POST" action="{{ route('admin-verifikasi.tolak', $cuti) }}">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    Alasan Penolakan <span class="text-red-500">*</span>
                                </label>
                                <textarea name="catatan" rows="4" required minlength="3"
                                          placeholder="Tuliskan alasan penolakan..."
                                          class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-1 focus:ring-red-500 focus:border-red-500"></textarea>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit"
                                        class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 rounded-lg transition-colors">
                                    Konfirmasi Tolak
                                </button>
                                <button type="button"
                                        onclick="document.getElementById('modalTolak').classList.add('hidden')"
                                        class="flex-1 border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium py-2 rounded-lg transition-colors">
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            @elseif(!$cuti->isDitolak() && !$cuti->isDibatalkan())
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-sm text-green-700">
                    <div class="flex items-center gap-2 font-medium mb-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Sudah Diverifikasi
                    </div>
                    <p class="text-xs">Pengajuan ini sudah diverifikasi dan sedang dalam proses persetujuan.</p>
                    @if($cuti->nomor_surat)
                        <p class="text-xs mt-1 font-mono">{{ $cuti->nomor_surat }}</p>
                    @endif
                </div>
            @endif

            {{-- Detail Saldo --}}
            @if($cuti->saldoDetail->isNotEmpty())
                <x-form-card title="Rincian Saldo">
                    @foreach($cuti->saldoDetail as $detail)
                        <div class="flex justify-between items-center text-sm py-1.5 border-b border-gray-100 last:border-0">
                            <span class="text-gray-600">Saldo {{ $detail->saldoCuti->tahun }}</span>
                            <span class="font-semibold text-gray-800">{{ $detail->jumlah_digunakan }} hari</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between items-center text-sm pt-2 font-bold">
                        <span class="text-gray-700">Total</span>
                        <span class="text-blue-900">{{ $cuti->jumlah_hari }} hari</span>
                    </div>
                </x-form-card>
            @endif

            {{-- Lampiran --}}
            <x-form-card title="Lampiran">
                @forelse($cuti->dokumen as $dok)
                    <div class="flex items-center gap-3 p-2 rounded-lg border border-gray-100 hover:bg-gray-50">
                        <svg class="w-8 h-8 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-gray-700 truncate">{{ $dok->nama_file }}</p>
                            <a href="{{ Storage::url($dok->file_path) }}" target="_blank"
                               class="text-xs text-blue-600 hover:text-blue-800">Lihat / Unduh</a>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 italic">Tidak ada lampiran.</p>
                @endforelse
            </x-form-card>
        </div>
    </div>

    <script>
        // Preview nomor surat otomatis
        const bulanRomawi = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
        const bulan = bulanRomawi[{{ now()->month - 1 }}];
        const tahun = {{ now()->year }};

        document.querySelector('[name="nomor_awal"]')?.addEventListener('input', function() {
            const val = this.value.trim();
            const preview = document.getElementById('previewNomor');
            if (val && /^\d+$/.test(val)) {
                preview.textContent = `${val}/KPA/SKET.KP4.3/${bulan}/${tahun}`;
            } else {
                preview.textContent = '—';
            }
        });
    </script>

</x-app-layout>
