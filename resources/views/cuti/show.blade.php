<x-app-layout title="Detail Pengajuan Cuti">

    <div class="mb-4 flex items-center justify-between flex-wrap gap-3">
        <a href="{{ route('cuti.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Riwayat
        </a>

        <div class="flex items-center gap-2 flex-wrap">
            {{-- Edit (hanya saat dikembalikan) --}}
            @if($cuti->bisaDiedit())
                <a href="{{ route('cuti.edit', $cuti) }}"
                   class="inline-flex items-center gap-2 bg-orange-500 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-orange-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Perbaiki & Ajukan Ulang
                </a>
            @endif

            {{-- Download PDF --}}
            <a href="{{ route('cuti.formulir', $cuti) }}" target="_blank"
               class="inline-flex items-center gap-2 border border-red-300 text-red-600 hover:bg-red-50 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download Formulir
            </a>

            {{-- Batal --}}
            @if($cuti->bisaDibatalkan())
                <form method="POST" action="{{ route('cuti.destroy', $cuti) }}"
                      onsubmit="return confirm('Yakin ingin membatalkan pengajuan ini?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-2 border border-gray-300 text-gray-600 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                        Batalkan
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Notifikasi dikembalikan --}}
    @if($cuti->isDikembalikan())
        <div class="mb-5 rounded-lg bg-orange-50 border border-orange-300 px-5 py-4 text-sm text-orange-900">
            <p class="font-bold text-base mb-1">⚠ Pengajuan Dikembalikan</p>
            <p>Pengajuan Anda dikembalikan untuk diperbaiki. Silakan klik <strong>Perbaiki & Ajukan Ulang</strong> di atas.</p>
            @if($cuti->catatan)
                <p class="mt-2 text-orange-800 font-medium">Catatan: {{ $cuti->catatan }}</p>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ── Detail Utama ──────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Header status --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
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
            </div>

            {{-- Data Pengajuan --}}
            <x-form-card title="Data Pengajuan">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500 mb-0.5">Jenis Cuti</dt>
                        <dd class="font-medium text-gray-800">{{ $cuti->jenisCuti?->nama ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-0.5">Jumlah Hari</dt>
                        <dd class="font-bold text-blue-900 text-lg">{{ $cuti->jumlah_hari }} hari kerja</dd>
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
                        <dd class="text-gray-800">{{ $cuti->alamat_cuti }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-0.5">No. Telepon</dt>
                        <dd class="text-gray-800">{{ $cuti->no_telepon ?? '—' }}</dd>
                    </div>
                </dl>
            </x-form-card>

            {{-- Identitas Pegawai --}}
            <x-form-card title="Identitas Pegawai">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <dt class="text-gray-500 mb-0.5">Nama</dt>
                        <dd class="font-medium text-gray-800">{{ $cuti->pegawai?->nama ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-0.5">NIP</dt>
                        <dd class="font-mono text-gray-700">{{ $cuti->pegawai?->nip ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-0.5">Jabatan</dt>
                        <dd class="text-gray-700">{{ $cuti->pegawai?->jabatan?->nama_jabatan ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-0.5">Unit Kerja</dt>
                        <dd class="text-gray-700">{{ $cuti->pegawai?->unitKerja?->nama_unit ?? '—' }}</dd>
                    </div>
                </dl>
            </x-form-card>

            {{-- Alur Approval --}}
            @if($cuti->approvalStages->count())
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                    <h3 class="font-semibold text-gray-800 mb-4 text-sm uppercase tracking-wide">Alur Persetujuan</h3>
                    <div class="space-y-3">
                        @foreach($cuti->approvalStages as $stage)
                            <div class="flex gap-4 items-start">
                                <div class="w-7 h-7 flex-shrink-0 rounded-full flex items-center justify-center text-xs font-bold
                                    {{ $stage->isApproved() ? 'bg-green-100 text-green-700' :
                                       ($stage->isRejected() ? 'bg-red-100 text-red-700' :
                                       ($stage->isReturned() ? 'bg-orange-100 text-orange-700' :
                                       ($stage->isSkipped() ? 'bg-gray-100 text-gray-500' :
                                       'bg-yellow-100 text-yellow-700'))) }}">
                                    {{ $stage->urutan }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between flex-wrap gap-2">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800">{{ $stage->label_tahap }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ $stage->nama_approver }} — {{ $stage->jabatan_approver }}
                                            </p>
                                            <p class="text-xs text-gray-400">{{ $stage->jenisTindakanLabel() }}</p>
                                        </div>
                                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $stage->statusColor() }}">
                                            {{ $stage->statusLabel() }}
                                        </span>
                                    </div>
                                    @if($stage->catatan)
                                        <p class="text-xs text-gray-600 mt-1 bg-gray-50 rounded px-2 py-1">
                                            {{ $stage->catatan }}
                                        </p>
                                    @endif
                                    @if($stage->tanggal_tindakan)
                                        <p class="text-xs text-gray-400 mt-0.5">
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

        {{-- ── Sidebar ────────────────────────────────────────── --}}
        <div class="space-y-4">

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
                            <a href="{{ route('cuti.download', [$cuti, $dok->id]) }}"
                               class="text-xs text-blue-600 hover:text-blue-800">Unduh</a>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 italic">Tidak ada lampiran.</p>
                @endforelse
            </x-form-card>

            {{-- Rincian saldo --}}
            @if($cuti->saldoDetail->isNotEmpty())
                <x-form-card title="Rincian Saldo">
                    @foreach($cuti->saldoDetail as $detail)
                        <div class="flex justify-between text-sm py-1.5 border-b border-gray-100 last:border-0">
                            <span class="text-gray-600">Saldo {{ $detail->saldoCuti?->tahun }}</span>
                            <span class="font-semibold text-gray-800">{{ $detail->jumlah_digunakan }} hari</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between text-sm pt-2 font-bold">
                        <span class="text-gray-700">Total</span>
                        <span class="text-blue-900">{{ $cuti->jumlah_hari }} hari</span>
                    </div>
                </x-form-card>
            @endif

            {{-- Audit Trail --}}
            @if($cuti->auditTrail->count())
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Riwayat Tindakan</h4>
                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        @foreach($cuti->auditTrail->sortByDesc('created_at') as $trail)
                            <div class="flex gap-2 text-xs">
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
            @endif

        </div>
    </div>

</x-app-layout>
