<x-app-layout title="Detail Pengajuan">

    <div class="mb-5 flex items-center justify-between flex-wrap gap-3">
        <a href="{{ route('approval.index') }}"
           class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
        </a>
        <a href="{{ route('cuti.formulir', $cuti) }}" target="_blank"
           class="inline-flex items-center gap-2 border border-red-300 text-red-600 hover:bg-red-50 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Download Formulir
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ── Detail Pengajuan ──────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Header --}}
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Nomor Pengajuan</p>
                        <p class="font-mono text-lg font-bold text-gray-800">{{ $cuti->nomor_pengajuan }}</p>
                        @if($cuti->nomor_surat)
                            <p class="text-xs text-gray-500 mt-1">
                                Nomor Surat: <span class="font-medium">{{ $cuti->nomor_surat }}</span>
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

                @if($cuti->catatan)
                    <div class="mt-4 px-4 py-3 rounded-lg bg-orange-50 border border-orange-200 text-sm text-orange-800">
                        <p class="font-semibold mb-0.5">Catatan:</p>
                        <p>{{ $cuti->catatan }}</p>
                    </div>
                @endif
            </div>

            {{-- Data Pegawai --}}
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-5">
                <h3 class="font-semibold text-gray-800 mb-4 text-sm uppercase tracking-wide">Data Pegawai</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-gray-500">Nama</dt>
                        <dd class="font-medium text-gray-800">{{ $cuti->pegawai?->nama ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">NIP</dt>
                        <dd class="font-mono text-gray-700">{{ $cuti->pegawai?->nip ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Jabatan</dt>
                        <dd class="text-gray-700">{{ $cuti->pegawai?->jabatan?->nama_jabatan ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Unit Kerja</dt>
                        <dd class="text-gray-700">{{ $cuti->pegawai?->unitKerja?->nama_unit ?? '-' }}</dd>
                    </div>
                    @if($cuti->masa_kerja)
                        <div>
                            <dt class="text-xs text-gray-500">Masa Kerja</dt>
                            <dd class="text-gray-700">{{ $cuti->masa_kerja }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Detail Cuti --}}
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-5">
                <h3 class="font-semibold text-gray-800 mb-4 text-sm uppercase tracking-wide">Detail Cuti</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-gray-500">Jenis Cuti</dt>
                        <dd class="font-medium text-gray-800">{{ $cuti->jenisCuti?->nama ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Lama Cuti</dt>
                        <dd class="font-medium text-gray-800">{{ $cuti->jumlah_hari }} hari kerja</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Tanggal Mulai</dt>
                        <dd class="text-gray-700">{{ $cuti->tanggal_mulai->isoFormat('dddd, D MMMM Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Tanggal Selesai</dt>
                        <dd class="text-gray-700">{{ $cuti->tanggal_selesai->isoFormat('dddd, D MMMM Y') }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs text-gray-500">Alasan</dt>
                        <dd class="text-gray-700">{{ $cuti->alasan }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Alamat Selama Cuti</dt>
                        <dd class="text-gray-700">{{ $cuti->alamat_cuti }}</dd>
                    </div>
                    @if($cuti->no_telepon)
                        <div>
                            <dt class="text-xs text-gray-500">No. Telepon</dt>
                            <dd class="text-gray-700">{{ $cuti->no_telepon }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Progress Approval --}}
            @if($cuti->approvalStages->count())
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-5">
                    <h3 class="font-semibold text-gray-800 mb-4 text-sm uppercase tracking-wide">Alur Approval</h3>
                    <div class="space-y-3">
                        @foreach($cuti->approvalStages as $stage)
                            <div class="flex gap-4 items-start">
                                {{-- Status icon --}}
                                <div class="w-8 h-8 flex-shrink-0 flex items-center justify-center rounded-full
                                    {{ $stage->isApproved() ? 'bg-green-100 text-green-600' :
                                       ($stage->isRejected() ? 'bg-red-100 text-red-600' :
                                       ($stage->isReturned() ? 'bg-orange-100 text-orange-600' :
                                       ($stage->isSkipped() ? 'bg-gray-100 text-gray-400' :
                                       'bg-yellow-100 text-yellow-600'))) }}">
                                    @if($stage->isApproved())
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    @elseif($stage->isRejected())
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    @elseif($stage->isReturned())
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    @elseif($stage->isSkipped())
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    @else
                                        <span class="text-xs font-bold">{{ $stage->urutan }}</span>
                                    @endif
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between flex-wrap gap-2">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800">{{ $stage->label_tahap }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ $stage->nama_approver }} &mdash; {{ $stage->jabatan_approver }}
                                            </p>
                                            <p class="text-xs text-gray-400">{{ $stage->jenisTindakanLabel() }}</p>
                                        </div>
                                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $stage->statusColor() }}">
                                            {{ $stage->statusLabel() }}
                                        </span>
                                    </div>
                                    @if($stage->catatan)
                                        <p class="text-xs text-gray-600 mt-1.5 bg-gray-50 rounded px-2 py-1.5">
                                            {{ $stage->catatan }}
                                        </p>
                                    @endif
                                    @if($stage->tanggal_tindakan)
                                        <p class="text-xs text-gray-400 mt-1">
                                            {{ $stage->tanggal_tindakan->format('d M Y, H:i') }} WIT
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        {{-- ── Kolom kanan: Aksi & Audit Trail ─────────────── --}}
        <div class="space-y-5">

            {{-- Panel Aksi --}}
            @if($tahapAktif)
                <div class="bg-white rounded-lg border border-yellow-300 shadow-sm overflow-hidden">
                    <div class="bg-yellow-50 border-b border-yellow-200 px-5 py-3">
                        <p class="text-sm font-bold text-yellow-800">Tindakan Diperlukan</p>
                        <p class="text-xs text-yellow-700 mt-0.5">
                            Tahap {{ $tahapAktif->urutan }}: {{ $tahapAktif->label_tahap }}
                            ({{ $tahapAktif->jenisTindakanLabel() }})
                        </p>
                    </div>

                    <div class="p-5 space-y-3">
                        {{-- Setujui/Mengetahui --}}
                        <form action="{{ route('approval.setujui', [$cuti, $tahapAktif]) }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                    Catatan (opsional)
                                </label>
                                <textarea name="catatan" rows="2"
                                          class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                          placeholder="Catatan persetujuan..."></textarea>
                            </div>
                            <button type="submit"
                                    class="w-full bg-green-600 text-white text-sm font-semibold py-2.5 rounded-lg hover:bg-green-700 transition-colors">
                                ✓ {{ $tahapAktif->jenisTindakanLabel() }}
                            </button>
                        </form>

                        <hr class="border-gray-100">

                        {{-- Kembalikan --}}
                        <form action="{{ route('approval.kembalikan', [$cuti, $tahapAktif]) }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                    Catatan pengembalian <span class="text-red-500">*</span>
                                </label>
                                <textarea name="catatan" rows="2" required
                                          class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
                                          placeholder="Alasan dikembalikan..."></textarea>
                            </div>
                            <button type="submit"
                                    class="w-full border border-orange-300 text-orange-700 text-sm font-semibold py-2.5 rounded-lg hover:bg-orange-50 transition-colors">
                                ↩ Kembalikan ke Pemohon
                            </button>
                        </form>

                        <hr class="border-gray-100">

                        {{-- Tolak --}}
                        <details class="group">
                            <summary class="cursor-pointer text-sm text-red-600 font-medium hover:text-red-800">
                                ✕ Tolak Pengajuan Ini
                            </summary>
                            <form action="{{ route('approval.tolak', [$cuti, $tahapAktif]) }}" method="POST" class="mt-3">
                                @csrf
                                <div class="mb-2">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">
                                        Alasan penolakan <span class="text-red-500">*</span>
                                    </label>
                                    <textarea name="catatan" rows="3" required
                                              class="w-full text-sm border border-red-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                                              placeholder="Jelaskan alasan penolakan..."></textarea>
                                </div>
                                <button type="submit"
                                        onclick="return confirm('Penolakan akan menghentikan workflow. Yakin?')"
                                        class="w-full bg-red-600 text-white text-sm font-semibold py-2.5 rounded-lg hover:bg-red-700 transition-colors">
                                    Konfirmasi Tolak
                                </button>
                            </form>
                        </details>
                    </div>
                </div>
            @elseif($cuti->isDitolak())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-800">
                    <p class="font-bold mb-1">Pengajuan Ditolak</p>
                    @if($cuti->catatan)
                        <p>{{ $cuti->catatan }}</p>
                    @endif
                </div>
            @elseif($cuti->isDisetujui())
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-sm text-green-800">
                    <p class="font-bold">✓ Pengajuan Disetujui</p>
                </div>
            @elseif($cuti->isDikembalikan())
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 text-sm text-orange-800">
                    <p class="font-bold">Dikembalikan ke Pemohon</p>
                    @if($cuti->catatan)
                        <p class="mt-1">{{ $cuti->catatan }}</p>
                    @endif
                </div>
            @endif

            {{-- Audit Trail --}}
            @if($cuti->auditTrail->count())
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-800 text-sm">Riwayat Tindakan</h3>
                    </div>
                    <div class="p-4 max-h-80 overflow-y-auto">
                        <div class="space-y-3">
                            @foreach($cuti->auditTrail->sortByDesc('created_at') as $trail)
                                <div class="flex gap-3 text-xs">
                                    <div class="w-1.5 h-1.5 rounded-full bg-blue-400 mt-1.5 flex-shrink-0"></div>
                                    <div>
                                        <p class="font-semibold text-gray-700">{{ $trail->aktor }}</p>
                                        <p class="text-gray-600">{{ $trail->aksiLabel() }}</p>
                                        @if($trail->keterangan)
                                            <p class="text-gray-500 mt-0.5">{{ $trail->keterangan }}</p>
                                        @endif
                                        <p class="text-gray-400 mt-0.5">
                                            {{ $trail->created_at->format('d M Y, H:i') }} WIT
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>

</x-app-layout>
