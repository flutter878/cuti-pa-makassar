<x-app-layout title="Detail Pengajuan Cuti">

    <div class="mb-4 flex items-center justify-between flex-wrap gap-3">
        <a href="{{ route('cuti.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Riwayat
        </a>

        <div class="flex items-center gap-2">
            {{-- Tombol Download PDF --}}
            <a href="{{ route('cuti.formulir', $cuti) }}" target="_blank"
               class="inline-flex items-center gap-2 border border-red-300 text-red-600 hover:bg-red-50 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download Formulir
            </a>

            {{-- Tombol batal --}}
            @if($cuti->bisaDibatalkan())
                <form method="POST" action="{{ route('cuti.destroy', $cuti) }}"
                      onsubmit="return confirm('Yakin ingin membatalkan pengajuan ini?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-2 border border-gray-300 text-gray-600 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                        Batalkan Pengajuan
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ===== DETAIL UTAMA ===== --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Header nomor + status --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Nomor Pengajuan</p>
                        <p class="font-mono text-lg font-bold text-gray-800">{{ $cuti->nomor_pengajuan }}</p>
                        <p class="text-xs text-gray-400 mt-1">
                            Diajukan: {{ $cuti->tanggal_pengajuan->format('d M Y, H:i') }} WIT
                        </p>
                    </div>
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $cuti->statusColor() }}">
                        {{ $cuti->statusLabel() }}
                    </span>
                </div>
            </div>

            {{-- Data pengajuan --}}
            <x-form-card title="Data Pengajuan">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500 mb-0.5">Jenis Cuti</dt>
                        <dd class="font-medium text-gray-800">{{ $cuti->jenisCuti->nama }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-0.5">Jumlah Hari</dt>
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
                        <dd class="text-gray-800">{{ $cuti->alamat_cuti }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-0.5">No. Telepon</dt>
                        <dd class="text-gray-800">{{ $cuti->no_telepon ?? '—' }}</dd>
                    </div>
                    @if($cuti->catatan)
                        <div class="sm:col-span-2">
                            <dt class="text-gray-500 mb-0.5">Catatan Admin</dt>
                            <dd class="text-gray-800 italic">{{ $cuti->catatan }}</dd>
                        </div>
                    @endif
                </dl>
            </x-form-card>

            {{-- Identitas pegawai --}}
            <x-form-card title="Identitas Pegawai">
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
                </dl>
            </x-form-card>
        </div>

        {{-- ===== SIDEBAR ===== --}}
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

            {{-- Detail pemakaian saldo --}}
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

            {{-- Riwayat persetujuan --}}
            @if($cuti->persetujuan->isNotEmpty())
                <x-form-card title="Riwayat Persetujuan">
                    <div class="space-y-3">
                        @foreach($cuti->persetujuan as $p)
                            <div class="text-xs">
                                <div class="flex items-center justify-between mb-0.5">
                                    <span class="font-medium text-gray-700">{{ $p->user->name }}</span>
                                    <span class="inline-flex px-1.5 py-0.5 rounded-full text-xs font-semibold
                                        {{ $p->status === 'disetujui' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                                        {{ ucfirst($p->status) }}
                                    </span>
                                </div>
                                @if($p->catatan)
                                    <p class="text-gray-500 italic">{{ $p->catatan }}</p>
                                @endif
                                <p class="text-gray-400">{{ $p->tanggal_persetujuan?->format('d M Y, H:i') }}</p>
                            </div>
                        @endforeach
                    </div>
                </x-form-card>
            @endif

        </div>
    </div>

</x-app-layout>
