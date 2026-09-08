@php
    $kategoriLabels = \App\Models\Jabatan::daftarKategori();

    // Siapkan data templates sebagai array biasa untuk @json (hindari arrow fn di @json)
    $templatesJs = $templates->map(function ($t) {
        return [
            'id'     => $t->id,
            'nama'   => $t->nama,
            'stages' => $t->stages->map(function ($s) {
                return [
                    'label'             => $s->label,
                    'jenis_tindakan'    => $s->jenis_tindakan,
                    'perlu_ttd'         => $s->perlu_ttd,
                    'kategori_approver' => $s->kategori_approver,
                ];
            })->values()->all(),
        ];
    })->values()->all();
@endphp

<x-app-layout title="Tentukan Routing Approval">

    {{-- Breadcrumb --}}
    <div class="mb-5 flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('admin-verifikasi.index') }}" class="hover:text-blue-600">Verifikasi Cuti</a>
        <span>/</span>
        <a href="{{ route('admin-verifikasi.show', $cuti) }}" class="hover:text-blue-600">{{ $cuti->nomor_pengajuan }}</a>
        <span>/</span>
        <span class="text-gray-800 font-medium">Tentukan Routing</span>
    </div>

    {{-- Info Pemohon --}}
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-5 mb-5">
        <div class="flex items-start justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-base font-semibold text-gray-800 mb-1">
                    {{ $cuti->pegawai?->nama ?? '-' }}
                </h2>
                <p class="text-sm text-gray-500">
                    {{ $cuti->pegawai?->jabatan?->nama_jabatan ?? '-' }} &mdash;
                    {{ $cuti->pegawai?->unitKerja?->nama_unit ?? '-' }}
                </p>
                <p class="text-xs text-gray-400 mt-1">
                    NIP: {{ $cuti->pegawai?->nip ?? '-' }}
                </p>
            </div>
            <div class="text-right">
                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-700">
                    Menunggu Routing
                </span>
                <p class="text-xs text-gray-500 mt-1 font-mono">{{ $cuti->nomor_surat }}</p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm border-t border-gray-100 pt-4">
            <div>
                <p class="text-xs text-gray-500">Jenis Cuti</p>
                <p class="font-medium text-gray-800">{{ $cuti->jenisCuti?->nama ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Tanggal</p>
                <p class="font-medium text-gray-800">
                    {{ $cuti->tanggal_mulai->format('d M') }} – {{ $cuti->tanggal_selesai->format('d M Y') }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Lama Cuti</p>
                <p class="font-medium text-gray-800">{{ $cuti->jumlah_hari }} hari kerja</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Status Kuota</p>
                @if($kuota['penuh'])
                    <p class="font-semibold text-red-600">
                        PENUH ({{ $kuota['terpakai'] }}/{{ $kuota['kuota'] }})
                    </p>
                @else
                    <p class="font-semibold text-green-600">
                        TERSEDIA (sisa {{ $kuota['sisa'] }})
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- Peringatan kuota penuh --}}
    @if($kuota['penuh'])
        <div class="mb-5 flex items-start gap-3 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
            <svg class="w-5 h-5 flex-shrink-0 text-red-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <span>
                <strong>Kuota cuti penuh</strong> pada tanggal yang diajukan.
                Sudah ada {{ $kuota['terpakai'] }} pegawai yang cuti. Pertimbangkan sebelum meneruskan.
            </span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ── Kolom kiri: Form Routing ──────────────────────── --}}
        <div class="lg:col-span-2">
            <form action="{{ route('admin-verifikasi.teruskan', $cuti) }}" method="POST" id="formRouting">
                @csrf

                <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-800">Tahapan Approval</h3>
                        <button type="button" onclick="tambahTahap()"
                                class="inline-flex items-center gap-1.5 text-sm bg-blue-600 text-white px-3 py-1.5 rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tambah Tahap
                        </button>
                    </div>

                    @if($errors->any())
                        <div class="mx-5 mt-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
                            <ul class="list-disc list-inside space-y-0.5">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mx-5 mt-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- Daftar tahap approval --}}
                    <div id="stageContainer" class="p-5 space-y-4">
                        <p id="emptyMsg" class="text-sm text-gray-400 text-center py-6">
                            Belum ada tahap. Klik "Tambah Tahap" atau pilih template di sebelah kanan.
                        </p>
                    </div>

                    <div class="px-5 pb-5 flex items-center gap-3 border-t border-gray-100 pt-4">
                        <button type="submit"
                                class="inline-flex items-center gap-2 bg-green-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-green-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Teruskan Pengajuan
                        </button>
                        <a href="{{ route('admin-verifikasi.show', $cuti) }}"
                           class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                            Kembali
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- ── Kolom kanan: Template & Daftar Pejabat ───────── --}}
        <div class="space-y-5">

            {{-- Template routing --}}
            @if($templates->count())
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-800 text-sm">Template Routing</h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Berdasarkan jabatan:
                            <strong>{{ $kategoriLabels[$kategoriPemohon] ?? ucfirst(str_replace('_', ' ', $kategoriPemohon)) }}</strong>
                        </p>
                    </div>
                    <div class="p-4 space-y-2">
                        @foreach($templates as $tmpl)
                            <button type="button"
                                    onclick="pakaiTemplate({{ $tmpl->id }})"
                                    class="w-full text-left px-3 py-2.5 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-colors text-sm">
                                <p class="font-medium text-gray-800">{{ $tmpl->nama }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $tmpl->stages->count() }} tahap:
                                    {{ $tmpl->stages->pluck('label')->join(' → ') }}
                                </p>
                            </button>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-800">
                    <p class="font-medium">Belum ada template routing</p>
                    <p class="text-xs mt-1">
                        untuk kategori "{{ $kategoriLabels[$kategoriPemohon] ?? ucfirst(str_replace('_', ' ', $kategoriPemohon)) }}".
                    </p>
                    <a href="{{ route('routing-template.create') }}" target="_blank"
                       class="text-blue-600 hover:underline text-xs mt-1 block">
                        Buat template baru →
                    </a>
                </div>
            @endif

            {{-- Daftar pejabat tersedia --}}
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800 text-sm">Daftar Pejabat Tersedia</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Klik nama untuk mengisi ke tahap yang kosong</p>
                    <input type="text" id="cariPejabat" placeholder="Cari nama..."
                           oninput="filterPejabat()"
                           class="mt-2 w-full text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="p-2 max-h-80 overflow-y-auto" id="daftarPejabat">
                    @forelse($daftarPegawai as $kategori => $pegawaiList)
                        <div class="mb-2">
                            <p class="px-3 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50 rounded">
                                {{ $kategoriLabels[$kategori] ?? ucfirst(str_replace('_', ' ', $kategori)) }}
                            </p>
                            @foreach($pegawaiList as $p)
                                <button type="button"
                                        data-user-id="{{ $p->user->id }}"
                                        data-nama="{{ $p->nama }}"
                                        data-jabatan="{{ $p->jabatan?->nama_jabatan ?? '-' }}"
                                        data-kategori="{{ $kategori }}"
                                        onclick="tambahPejabat(this)"
                                        class="pejabat-item w-full text-left px-3 py-2 rounded hover:bg-blue-50 transition-colors">
                                    <p class="text-sm font-medium text-gray-800">{{ $p->nama }}</p>
                                    <p class="text-xs text-gray-500">{{ $p->jabatan?->nama_jabatan ?? '-' }}</p>
                                </button>
                            @endforeach
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 text-center py-4">Tidak ada pejabat tersedia.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    {{-- Data untuk JS --}}
    <script>
        const templates           = @json($templatesJs);
        const jenisTindakanOptions = @json($jenisTindakanOptions);

        let stageCount = 0;

        function updateEmptyMsg() {
            const container = document.getElementById('stageContainer');
            const msg       = document.getElementById('emptyMsg');
            const cards     = container.querySelectorAll('.stage-card');
            msg.style.display = cards.length === 0 ? 'block' : 'none';
        }

        function tambahTahap(prefill) {
            prefill = prefill || {};
            const container = document.getElementById('stageContainer');
            const idx       = stageCount++;

            const card = document.createElement('div');
            card.className = 'stage-card border border-gray-200 rounded-lg p-4 relative bg-gray-50';
            card.dataset.idx = idx;

            const urutan = container.querySelectorAll('.stage-card').length + 1;

            let jenisTindakanHtml = Object.entries(jenisTindakanOptions).map(function(entry) {
                const val   = entry[0];
                const label = entry[1];
                const selected = (prefill.jenis_tindakan || 'approval') === val ? 'selected' : '';
                return '<option value="' + val + '" ' + selected + '>' + label + '</option>';
            }).join('');

            const namaVal   = prefill.nama    ? prefill.nama.replace(/"/g, '&quot;')    : '';
            const labelVal  = prefill.label   ? prefill.label.replace(/"/g, '&quot;')   : '';
            const uidVal    = prefill.user_id ? prefill.user_id                          : '';
            const ttdCheck  = (prefill.perlu_ttd !== false) ? 'checked' : '';
            const jabatanEl = prefill.jabatan
                ? '<p class="text-xs text-gray-500 mt-1">' + prefill.jabatan + '</p>'
                : '';

            card.innerHTML =
                '<div class="flex items-center justify-between mb-3">' +
                    '<span class="text-xs font-bold text-blue-700 bg-blue-100 px-2 py-0.5 rounded-full">' +
                        'Tahap <span class="urutan-label">' + urutan + '</span>' +
                    '</span>' +
                    '<button type="button" onclick="hapusTahap(this)" class="text-gray-400 hover:text-red-500 transition-colors">' +
                        '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>' +
                        '</svg>' +
                    '</button>' +
                '</div>' +
                '<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">' +
                    '<div>' +
                        '<label class="block text-xs font-medium text-gray-600 mb-1">Label Tahap *</label>' +
                        '<input type="text" name="stages[' + idx + '][label_tahap]"' +
                               ' value="' + labelVal + '"' +
                               ' placeholder="Contoh: Atasan Langsung"' +
                               ' required' +
                               ' class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">' +
                    '</div>' +
                    '<div>' +
                        '<label class="block text-xs font-medium text-gray-600 mb-1">Jenis Tindakan *</label>' +
                        '<select name="stages[' + idx + '][jenis_tindakan]" required' +
                                ' class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">' +
                            jenisTindakanHtml +
                        '</select>' +
                    '</div>' +
                    '<div class="sm:col-span-2">' +
                        '<label class="block text-xs font-medium text-gray-600 mb-1">Pejabat *</label>' +
                        '<div class="flex gap-2">' +
                            '<input type="text" readonly' +
                                   ' id="nama_' + idx + '"' +
                                   ' value="' + namaVal + '"' +
                                   ' placeholder="Klik nama pejabat dari daftar kanan"' +
                                   ' class="flex-1 text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer"' +
                                   ' onclick="document.getElementById(\'daftarPejabat\').scrollIntoView({behavior:\'smooth\'})">' +
                            '<input type="hidden" name="stages[' + idx + '][user_id]"' +
                                   ' id="uid_' + idx + '" value="' + uidVal + '">' +
                        '</div>' +
                        jabatanEl +
                    '</div>' +
                    '<div class="sm:col-span-2 flex items-center gap-2">' +
                        '<input type="checkbox" name="stages[' + idx + '][perlu_ttd]" value="1"' +
                               ' id="ttd_' + idx + '" ' + ttdCheck +
                               ' class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">' +
                        '<label for="ttd_' + idx + '" class="text-xs text-gray-600">Area tanda tangan diperlukan</label>' +
                    '</div>' +
                '</div>' +
                '<input type="hidden" class="current-stage-idx" value="' + idx + '">';

            container.appendChild(card);
            updateEmptyMsg();
            updateUrutanLabels();
        }

        function hapusTahap(btn) {
            btn.closest('.stage-card').remove();
            updateEmptyMsg();
            updateUrutanLabels();
        }

        function updateUrutanLabels() {
            const cards = document.querySelectorAll('.stage-card');
            cards.forEach(function(card, i) {
                const label = card.querySelector('.urutan-label');
                if (label) label.textContent = i + 1;
            });
        }

        function tambahPejabat(btn) {
            const userId  = btn.dataset.userId;
            const nama    = btn.dataset.nama;
            const jabatan = btn.dataset.jabatan;

            const cards = document.querySelectorAll('.stage-card');
            if (cards.length === 0) {
                tambahTahap({ nama: nama, jabatan: jabatan, user_id: userId });
                return;
            }

            // Isi ke stage pertama yang belum ada pejabat
            let targetCard = null;
            for (let i = 0; i < cards.length; i++) {
                const uid = cards[i].querySelector('[id^="uid_"]');
                if (uid && !uid.value) {
                    targetCard = cards[i];
                    break;
                }
            }
            if (!targetCard) targetCard = cards[cards.length - 1];

            const idxEl = targetCard.querySelector('.current-stage-idx');
            const idx   = idxEl.value;
            document.getElementById('uid_'  + idx).value = userId;
            document.getElementById('nama_' + idx).value = nama + ' (' + jabatan + ')';

            // Highlight singkat
            targetCard.style.transition = 'background 0.3s';
            targetCard.style.background = '#eff6ff';
            setTimeout(function() { targetCard.style.background = ''; }, 800);
        }

        function pakaiTemplate(templateId) {
            const tmpl = templates.find(function(t) { return t.id === templateId; });
            if (!tmpl) return;

            document.querySelectorAll('.stage-card').forEach(function(c) { c.remove(); });
            stageCount = 0;
            updateEmptyMsg();

            tmpl.stages.forEach(function(s) {
                tambahTahap({
                    label:          s.label,
                    jenis_tindakan: s.jenis_tindakan,
                    perlu_ttd:      s.perlu_ttd,
                });
            });
        }

        function filterPejabat() {
            const cari  = document.getElementById('cariPejabat').value.toLowerCase();
            const items = document.querySelectorAll('.pejabat-item');
            items.forEach(function(item) {
                const nama = item.querySelector('p') ? item.querySelector('p').textContent.toLowerCase() : '';
                item.style.display = nama.includes(cari) ? '' : 'none';
            });
        }

        // Validasi form sebelum submit
        document.getElementById('formRouting').addEventListener('submit', function(e) {
            const cards = document.querySelectorAll('.stage-card');
            if (cards.length === 0) {
                e.preventDefault();
                alert('Minimal harus ada satu tahap approval.');
                return;
            }

            // Validasi setiap stage harus ada pejabat
            let valid = true;
            cards.forEach(function(card, i) {
                const uid = card.querySelector('[id^="uid_"]');
                if (!uid || !uid.value) {
                    valid = false;
                    card.style.border = '2px solid #ef4444';
                    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    card.style.border = '';
                }
            });

            if (!valid) {
                e.preventDefault();
                alert('Semua tahap harus memiliki pejabat yang dipilih.');
            }
        });
    </script>

</x-app-layout>
