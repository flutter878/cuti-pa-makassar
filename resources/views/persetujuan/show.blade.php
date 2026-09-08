<x-app-layout title="Detail Pengajuan Cuti">
    <div class="mb-5 flex items-center justify-between flex-wrap gap-3">
        <a href="{{ route('persetujuan.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar Pengajuan
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

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700 flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Progress bar alur persetujuan --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mb-5">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-4">Alur Persetujuan</p>
        <div class="flex items-center gap-2 flex-wrap">
            @php
                $steps = [
                    ['key' => 'menunggu_persetujuan_atasan', 'label' => 'Atasan Langsung', 'sub' => 'Panitera / Sekretaris'],
                    ['key' => 'menunggu_persetujuan_ketua',  'label' => 'Ketua',            'sub' => 'Persetujuan Final'],
                    ['key' => 'disetujui',                   'label' => 'Disetujui',        'sub' => 'Saldo Dikurangi'],
                ];
                $statusOrder = ['menunggu_persetujuan_atasan' => 0, 'menunggu_persetujuan_ketua' => 1, 'disetujui' => 2];
                $currentIdx  = $statusOrder[$cuti->status] ?? -1;
                $isTerminal  = in_array($cuti->status, ['ditolak', 'dibatalkan']);
            @endphp

            @if($isTerminal)
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center {{ $cuti->status === 'ditolak' ? 'bg-red-500' : 'bg-gray-400' }} text-white text-xs font-bold">✕</div>
                    <div>
                        <p class="text-sm font-semibold {{ $cuti->status === 'ditolak' ? 'text-red-600' : 'text-gray-500' }}">
                            {{ $cuti->statusLabel() }}
                        </p>
                    </div>
                </div>
            @else
                @foreach($steps as $i => $step)
                    @php
                        $idx     = $i;
                        $isDone  = $currentIdx > $idx;
                        $isNow   = $currentIdx === $idx;
                    @endphp
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                                {{ $isDone ? 'bg-green-500 text-white' : ($isNow ? 'bg-blue-900 text-white' : 'bg-gray-100 text-gray-400') }}">
                                {{ $isDone ? '✓' : ($i + 1) }}
                            </div>
                            <div>
                                <p class="text-xs font-semibold {{ $isDone ? 'text-green-600' : ($isNow ? 'text-blue-900' : 'text-gray-400') }}">
                                    {{ $step['label'] }}
                                </p>
                                <p class="text-[10px] {{ $isDone ? 'text-green-500' : ($isNow ? 'text-blue-600' : 'text-gray-300') }}">
                                    {{ $step['sub'] }}
                                </p>
                            </div>
                        </div>
                        @if(!$loop->last)
                            <svg class="w-5 h-5 {{ $isDone ? 'text-green-400' : 'text-gray-200' }} mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Kolom kiri: detail --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Header --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Nomor Pengajuan</p>
                        <p class="font-mono text-lg font-bold text-gray-800">{{ $cuti->nomor_pengajuan }}</p>
                        <p class="text-sm text-gray-500 mt-1.5">
                            Diajukan {{ $cuti->tanggal_pengajuan->format('d M Y, H:i') }}
                        </p>
                    </div>
                    <span class="self-start inline-flex px-3 py-1.5 rounded-full text-sm font-semibold {{ $cuti->statusColor() }}">
                        {{ $cuti->statusLabel() }}
                    </span>
                </div>
            </div>

            {{-- Data pengajuan --}}
            <x-form-card title="Data Pengajuan">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Jenis Cuti</dt>
                        <dd class="font-medium text-gray-800 mt-1">{{ $cuti->jenisCuti->nama }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Jumlah Hari</dt>
                        <dd class="font-bold text-blue-900 mt-1 text-lg">{{ $cuti->jumlah_hari }} hari</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Tanggal Mulai</dt>
                        <dd class="font-medium text-gray-800 mt-1">{{ $cuti->tanggal_mulai->format('d F Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Tanggal Selesai</dt>
                        <dd class="font-medium text-gray-800 mt-1">{{ $cuti->tanggal_selesai->format('d F Y') }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-gray-500">Alasan</dt>
                        <dd class="text-gray-800 mt-1">{{ $cuti->alasan }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-gray-500">Alamat Selama Cuti</dt>
                        <dd class="text-gray-800 mt-1">{{ $cuti->alamat_cuti }}</dd>
                    </div>
                    @if($cuti->no_telepon)
                        <div>
                            <dt class="text-gray-500">No. Telepon</dt>
                            <dd class="text-gray-800 mt-1">{{ $cuti->no_telepon }}</dd>
                        </div>
                    @endif
                </dl>
            </x-form-card>

            {{-- Identitas Pegawai --}}
            <x-form-card title="Identitas Pegawai">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div>
                        <dt class="text-gray-500">Nama</dt>
                        <dd class="font-medium text-gray-800 mt-1">{{ $cuti->pegawai->nama }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">NIP</dt>
                        <dd class="font-mono text-gray-700 mt-1">{{ $cuti->pegawai->nip }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Jabatan</dt>
                        <dd class="text-gray-700 mt-1">{{ $cuti->pegawai->jabatan?->nama_jabatan ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Unit Kerja</dt>
                        <dd class="text-gray-700 mt-1">{{ $cuti->pegawai->unitKerja?->nama_unit ?? '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-gray-500">Atasan Langsung</dt>
                        <dd class="mt-1">
                            @if($cuti->pegawai->atasanLangsung)
                                <span class="font-medium text-gray-800">{{ $cuti->pegawai->atasanLangsung->nama }}</span>
                                <span class="text-xs text-gray-400 ml-1">({{ $cuti->pegawai->atasanLangsung->jabatan?->nama_jabatan }})</span>
                            @else
                                <span class="text-gray-400 italic text-xs">Tidak ada atasan langsung</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </x-form-card>

            {{-- Dokumen lampiran --}}
            @if($cuti->dokumen->isNotEmpty())
                <x-form-card title="Lampiran Dokumen">
                    <div class="space-y-2">
                        @foreach($cuti->dokumen as $dok)
                            <div class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                    <span class="text-sm text-gray-700 truncate max-w-[200px]">{{ $dok->nama_file }}</span>
                                </div>
                                <a href="{{ Storage::url($dok->file_path) }}" target="_blank"
                                   class="text-xs text-blue-600 hover:text-blue-800 font-medium flex-shrink-0 ml-2">Unduh</a>
                            </div>
                        @endforeach
                    </div>
                </x-form-card>
            @endif
        </div>

        {{-- Kolom kanan: tindakan + riwayat --}}
        <div class="space-y-5">

            {{-- ═══ PANEL ATASAN LANGSUNG ═══ --}}
        @if($bisaApproveAtasan)
                <div class="bg-white rounded-xl border-2 border-yellow-200 shadow-sm p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-7 h-7 rounded-full bg-yellow-100 flex items-center justify-center">
                            <span class="text-yellow-700 font-bold text-xs">1</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">Persetujuan Atasan Langsung</h3>
                            <p class="text-xs text-gray-400">{{ $pegawaiLogin->jabatan?->nama_jabatan }}</p>
                        </div>
                    </div>

                    {{-- Setujui --}}
                    <form method="POST" action="{{ route('persetujuan.atasan.setujui', $cuti) }}" class="mb-3">
                        @csrf
                        <div class="mb-2">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Catatan (opsional)</label>
                            <textarea name="catatan" rows="2"
                                      class="w-full border-gray-200 rounded-lg text-xs focus:border-blue-500 focus:ring-blue-500"
                                      placeholder="Catatan persetujuan..."></textarea>
                        </div>
                        <button type="button"
                                onclick="bukaModal('modalSetujuiAtasan')"
                                style="width:100%; background-color:#16a34a; color:#ffffff; padding:10px 16px; border-radius:8px; font-size:14px; font-weight:600; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;">
                            <svg style="width:16px;height:16px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Setujui → Teruskan ke Ketua
                        </button>
                    </form>

                    <hr class="border-gray-100 mb-3">

                    {{-- Tolak --}}
                    <form method="POST" action="{{ route('persetujuan.atasan.tolak', $cuti) }}">
                        @csrf
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Alasan penolakan <span class="text-red-500">*</span>
                        </label>
                        <textarea name="catatan" rows="3" required
                                  class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500"
                                  placeholder="Jelaskan alasan penolakan...">{{ old('catatan') }}</textarea>
                        @error('catatan')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                        <button type="submit"
                                class="w-full mt-2 inline-flex justify-center items-center gap-2 border border-red-300
                                       text-red-600 hover:bg-red-50 text-sm font-semibold px-4 py-2.5 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Tolak Pengajuan
                        </button>
                    </form>
                </div>
            @endif

            {{-- ═══ PANEL KETUA ═══ --}}
            @if($bisaApproveKetua)
                <div class="bg-white rounded-xl border-2 border-orange-200 shadow-sm p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-7 h-7 rounded-full bg-orange-100 flex items-center justify-center">
                            <span class="text-orange-700 font-bold text-xs">2</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">Persetujuan Ketua</h3>
                            <p class="text-xs text-gray-400">Persetujuan final — saldo akan dikurangi</p>
                        </div>
                    </div>

                    {{-- Setujui Final --}}
                    <form method="POST" action="{{ route('persetujuan.ketua.setujui', $cuti) }}" class="mb-3">
                        @csrf
                        <div class="mb-2">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Catatan (opsional)</label>
                            <textarea name="catatan" rows="2"
                                      class="w-full border-gray-200 rounded-lg text-xs focus:border-blue-500 focus:ring-blue-500"
                                      placeholder="Catatan persetujuan..."></textarea>
                        </div>
                        <button type="button"
                                onclick="bukaModal('modalSetujuiKetua')"
                                style="width:100%; background-color:#1e3a5f; color:#ffffff; padding:10px 16px; border-radius:8px; font-size:14px; font-weight:600; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;">
                            <svg style="width:16px;height:16px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Setujui Final
                        </button>
                    </form>

                    <hr class="border-gray-100 mb-3">

                    {{-- Tolak --}}
                    <form method="POST" action="{{ route('persetujuan.ketua.tolak', $cuti) }}">
                        @csrf
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Alasan penolakan <span class="text-red-500">*</span>
                        </label>
                        <textarea name="catatan" rows="3" required
                                  class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500"
                                  placeholder="Jelaskan alasan penolakan...">{{ old('catatan') }}</textarea>
                        @error('catatan')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                        <button type="submit"
                                class="w-full mt-2 inline-flex justify-center items-center gap-2 border border-red-300
                                       text-red-600 hover:bg-red-50 text-sm font-semibold px-4 py-2.5 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Tolak Pengajuan
                        </button>
                    </form>
                </div>
            @endif

            {{-- Info jika sudah final --}}
            @if($cuti->status === 'ditolak' && $cuti->catatan)
                <div class="bg-red-50 rounded-xl border border-red-100 p-4">
                    <p class="text-xs font-semibold text-red-500 uppercase tracking-wide mb-2">Alasan Penolakan</p>
                    <p class="text-sm text-red-700">{{ $cuti->catatan }}</p>
                </div>
            @endif

            @if($cuti->status === 'disetujui' && $cuti->saldoDetail->isNotEmpty())
                <x-form-card title="Saldo Digunakan">
                    <div class="space-y-2 text-sm">
                        @foreach($cuti->saldoDetail as $detail)
                            <div class="flex justify-between items-center py-1.5 px-3 bg-blue-50 rounded-lg">
                                <span class="text-gray-600">Saldo {{ $detail->saldoCuti->tahun }}</span>
                                <span class="font-bold text-blue-900">{{ $detail->jumlah_digunakan }} hari</span>
                            </div>
                        @endforeach
                    </div>
                </x-form-card>
            @endif

            {{-- Riwayat tindakan --}}
            @if($cuti->persetujuan->isNotEmpty())
                <x-form-card title="Riwayat Persetujuan">
                    <div class="space-y-3">
                        @foreach($cuti->persetujuan->sortByDesc('tanggal_persetujuan') as $p)
                            <div class="text-xs pb-3 border-b border-gray-100 last:border-0 last:pb-0">
                                <div class="flex justify-between items-start gap-2">
                                    <div>
                                        <p class="font-semibold text-gray-700">{{ $p->user->name }}</p>
                                        <p class="text-gray-400">{{ $p->user->pegawai?->jabatan?->nama_jabatan }}</p>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-semibold
                                            {{ $p->level === 'ketua' ? 'bg-orange-100 text-orange-700' : 'bg-yellow-100 text-yellow-700' }}">
                                            {{ $p->levelLabel() }}
                                        </span>
                                        <p class="font-semibold mt-0.5 {{ $p->statusColor() }}">{{ $p->statusLabel() }}</p>
                                    </div>
                                </div>
                                <p class="text-gray-400 mt-1">{{ $p->tanggal_persetujuan?->format('d M Y, H:i') }}</p>
                                @if($p->catatan)
                                    <p class="text-gray-500 italic mt-1 bg-gray-50 rounded px-2 py-1">{{ $p->catatan }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </x-form-card>
            @endif

            {{-- Info untuk admin (monitoring) --}}
            @if($isAdmin && !$bisaApproveAtasan && !$bisaApproveKetua && in_array($cuti->status, ['menunggu_persetujuan_atasan','menunggu_persetujuan_ketua']))
                <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Mode Monitor</p>
                    <p class="text-xs text-gray-400">
                        @if($cuti->status === 'menunggu_persetujuan_atasan')
                            Menunggu persetujuan dari
                            <strong>{{ $cuti->pegawai->atasanLangsung?->nama ?? 'atasan langsung' }}</strong>.
                        @else
                            Menunggu persetujuan final dari <strong>Ketua</strong>.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>

{{-- ═══ MODAL KONFIRMASI SETUJUI ATASAN ═══ --}}
<div id="modalSetujuiAtasan" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:16px;">
    <div style="background:#fff; border-radius:16px; padding:28px; max-width:420px; width:100%; box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
            <div style="width:44px; height:44px; background:#dcfce7; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg style="width:22px;height:22px;" fill="none" stroke="#16a34a" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div>
                <p style="font-size:16px; font-weight:700; color:#111827; margin:0;">Konfirmasi Persetujuan</p>
                <p style="font-size:13px; color:#6b7280; margin:0;">Atasan Langsung</p>
            </div>
        </div>
        <p style="font-size:14px; color:#374151; margin-bottom:8px;">Anda akan menyetujui pengajuan cuti:</p>
        <div style="background:#f9fafb; border-radius:8px; padding:12px; margin-bottom:12px; font-size:13px; color:#374151;">
            <strong>{{ $cuti->pegawai->nama }}</strong><br>
            {{ $cuti->jenisCuti->nama }} — {{ $cuti->jumlah_hari }} hari<br>
            {{ $cuti->tanggal_mulai->format('d M Y') }} s/d {{ $cuti->tanggal_selesai->format('d M Y') }}
        </div>
        <p style="font-size:13px; color:#6b7280; margin-bottom:20px;">Pengajuan akan diteruskan ke <strong>Ketua</strong> untuk persetujuan final.</p>
        <div style="display:flex; gap:10px;">
            <form method="POST" action="{{ route('persetujuan.atasan.setujui', $cuti) }}" style="flex:1;">
                @csrf
                <button type="submit"
                        style="width:100%; background-color:#16a34a; color:#fff; padding:10px; border-radius:8px; font-size:14px; font-weight:600; border:none; cursor:pointer;">
                    ✓ Ya, Setujui
                </button>
            </form>
            <button type="button" onclick="tutupModal('modalSetujuiAtasan')"
                    style="flex:1; background:#f3f4f6; color:#374151; padding:10px; border-radius:8px; font-size:14px; font-weight:600; border:none; cursor:pointer;">
                Batal
            </button>
        </div>
    </div>
</div>

{{-- ═══ MODAL KONFIRMASI SETUJUI KETUA ═══ --}}
<div id="modalSetujuiKetua" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:16px;">
    <div style="background:#fff; border-radius:16px; padding:28px; max-width:420px; width:100%; box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
            <div style="width:44px; height:44px; background:#dbeafe; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg style="width:22px;height:22px;" fill="none" stroke="#1d4ed8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p style="font-size:16px; font-weight:700; color:#111827; margin:0;">Konfirmasi Persetujuan Final</p>
                <p style="font-size:13px; color:#6b7280; margin:0;">Ketua</p>
            </div>
        </div>
        <p style="font-size:14px; color:#374151; margin-bottom:8px;">Anda akan menyetujui secara final pengajuan cuti:</p>
        <div style="background:#f9fafb; border-radius:8px; padding:12px; margin-bottom:12px; font-size:13px; color:#374151;">
            <strong>{{ $cuti->pegawai->nama }}</strong><br>
            {{ $cuti->jenisCuti->nama }} — {{ $cuti->jumlah_hari }} hari<br>
            {{ $cuti->tanggal_mulai->format('d M Y') }} s/d {{ $cuti->tanggal_selesai->format('d M Y') }}
        </div>
        <div style="background:#fef9c3; border:1px solid #fde68a; border-radius:8px; padding:10px; margin-bottom:20px; font-size:13px; color:#92400e;">
            ⚠️ Saldo cuti akan <strong>langsung dikurangi {{ $cuti->jumlah_hari }} hari</strong> setelah disetujui.
        </div>
        <div style="display:flex; gap:10px;">
            <form method="POST" action="{{ route('persetujuan.ketua.setujui', $cuti) }}" style="flex:1;">
                @csrf
                <button type="submit"
                        style="width:100%; background-color:#1e3a5f; color:#fff; padding:10px; border-radius:8px; font-size:14px; font-weight:600; border:none; cursor:pointer;">
                    ✓ Ya, Setujui Final
                </button>
            </form>
            <button type="button" onclick="tutupModal('modalSetujuiKetua')"
                    style="flex:1; background:#f3f4f6; color:#374151; padding:10px; border-radius:8px; font-size:14px; font-weight:600; border:none; cursor:pointer;">
                Batal
            </button>
        </div>
    </div>
</div>

<script>
    function bukaModal(id) {
        const el = document.getElementById(id);
        el.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    function tutupModal(id) {
        document.getElementById(id).style.display = 'none';
        document.body.style.overflow = '';
    }
    // Tutup modal klik backdrop
    ['modalSetujuiAtasan','modalSetujuiKetua'].forEach(function(id) {
        const el = document.getElementById(id);
        if (el) el.addEventListener('click', function(e) {
            if (e.target === this) tutupModal(id);
        });
    });
</script>

</x-app-layout>
