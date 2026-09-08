<x-app-layout title="Edit Template Routing">

    <div class="mb-5 flex items-center gap-2 text-sm">
        <a href="{{ route('routing-template.index') }}" class="text-blue-600 hover:text-blue-800">Template Routing</a>
        <span class="text-gray-400">/</span>
        <span class="text-gray-700 font-medium">Edit: {{ $routingTemplate->nama }}</span>
    </div>

    <form action="{{ route('routing-template.update', $routingTemplate) }}" method="POST" id="formTemplate">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            <div class="space-y-5">
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-5">
                    <h3 class="font-semibold text-gray-800 mb-4 text-sm">Informasi Template</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nama Template <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nama" value="{{ old('nama', $routingTemplate->nama) }}" required
                                   class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nama') border-red-400 @enderror">
                            @error('nama') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Kategori Jabatan Pemohon <span class="text-red-500">*</span>
                            </label>
                            <select name="kategori_jabatan" required
                                    class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach($kategoriOptions as $val => $label)
                                    <option value="{{ $val }}"
                                        {{ old('kategori_jabatan', $routingTemplate->kategori_jabatan) === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="aktif" value="1" id="aktif"
                                   {{ old('aktif', $routingTemplate->aktif) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-gray-300 text-blue-600">
                            <label for="aktif" class="text-sm text-gray-700">Template aktif</label>
                        </div>
                    </div>
                </div>

                @if($errors->any())
                    <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <div class="flex gap-3">
                    <button type="submit"
                            class="flex-1 bg-blue-600 text-white text-sm font-semibold py-2.5 rounded-lg hover:bg-blue-700 transition-colors">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('routing-template.index') }}"
                       class="px-4 py-2.5 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        Batal
                    </a>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-800 text-sm">Tahapan Approval</h3>
                        <button type="button" onclick="tambahTahap()"
                                class="inline-flex items-center gap-1.5 text-sm bg-blue-600 text-white px-3 py-1.5 rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tambah Tahap
                        </button>
                    </div>
                    <div id="stageContainer" class="p-5 space-y-4">
                        <p id="emptyMsg" class="text-sm text-gray-400 text-center py-6 hidden">
                            Belum ada tahap.
                        </p>
                    </div>
                    <div class="px-5 pb-5">
                        <p class="text-xs text-gray-500 bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-2">
                            <strong>Perhatian:</strong> Tahap terakhir harus berjenis <strong>Menyetujui (Final)</strong>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        const kategoriOptions      = @json($kategoriOptions);
        const jenisTindakanOptions = @json($jenisTindakanOptions);
        const existingStages       = @json($routingTemplate->stages);
        let stageCount = 0;

        function updateEmptyMsg() {
            const container = document.getElementById('stageContainer');
            const msg       = document.getElementById('emptyMsg');
            msg.style.display = container.querySelectorAll('.stage-card').length === 0 ? 'block' : 'none';
        }

        function tambahTahap(prefill = {}) {
            const container = document.getElementById('stageContainer');
            const idx       = stageCount++;
            const urutan    = container.querySelectorAll('.stage-card').length + 1;

            const card = document.createElement('div');
            card.className = 'stage-card border border-gray-200 rounded-lg p-4 bg-gray-50';

            const kategoriHtml = Object.entries(kategoriOptions).map(([val, lbl]) =>
                `<option value="${val}" ${(prefill.kategori_approver || '') === val ? 'selected' : ''}>${lbl}</option>`
            ).join('');

            const jenisTindakanHtml = Object.entries(jenisTindakanOptions).map(([val, lbl]) =>
                `<option value="${val}" ${(prefill.jenis_tindakan || 'approval') === val ? 'selected' : ''}>${lbl}</option>`
            ).join('');

            card.innerHTML = `
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-blue-700 bg-blue-100 px-2 py-0.5 rounded-full">
                        Tahap <span class="urutan-label">${urutan}</span>
                    </span>
                    <button type="button" onclick="hapusTahap(this)" class="text-gray-400 hover:text-red-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Label Tahap *</label>
                        <input type="text" name="stages[${idx}][label]"
                               value="${prefill.label || ''}" required
                               class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kategori Approver *</label>
                        <select name="stages[${idx}][kategori_approver]" required
                                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">— Pilih —</option>
                            ${kategoriHtml}
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jenis Tindakan *</label>
                        <select name="stages[${idx}][jenis_tindakan]" required
                                class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            ${jenisTindakanHtml}
                        </select>
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-2 text-xs text-gray-600">
                            <input type="checkbox" name="stages[${idx}][perlu_ttd]" value="1"
                                   ${(prefill.perlu_ttd !== false) ? 'checked' : ''}
                                   class="w-4 h-4 rounded border-gray-300 text-blue-600">
                            Perlu tanda tangan
                        </label>
                    </div>
                </div>
            `;

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
            document.querySelectorAll('.urutan-label').forEach((el, i) => el.textContent = i + 1);
        }

        // Load existing stages
        document.addEventListener('DOMContentLoaded', function() {
            existingStages.forEach(s => tambahTahap(s));
        });
    </script>

</x-app-layout>
