<x-app-layout title="Template Routing Approval">

    <div class="mb-5 flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Template Routing Approval</h2>
            <p class="text-sm text-gray-500">Konfigurasi alur persetujuan berdasarkan kategori jabatan pemohon</p>
        </div>
        <a href="{{ route('routing-template.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 text-white text-sm font-semibold px-4 py-2.5 rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Template
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Kelompokkan per kategori --}}
    @php
        $grouped = $templates->groupBy('kategori_jabatan');
    @endphp

    @if($templates->isEmpty())
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm py-16 text-center text-gray-500">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
            </svg>
            <p class="font-medium">Belum ada template routing</p>
            <p class="text-sm mt-1">Buat template agar Admin lebih mudah menentukan routing approval.</p>
            <a href="{{ route('routing-template.create') }}"
               class="mt-3 inline-flex items-center gap-1 text-sm text-blue-600 hover:underline">
                Buat template pertama →
            </a>
        </div>
    @else
        <div class="space-y-6">
            @foreach($grouped as $kategori => $list)
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                    <div class="bg-gray-50 border-b border-gray-200 px-5 py-3">
                        <h3 class="font-semibold text-gray-800 text-sm">
                            {{ \App\Models\RoutingTemplate::kategoriLabel($kategori) }}
                        </h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @foreach($list as $tmpl)
                            <div class="px-5 py-4 flex items-start justify-between gap-4 flex-wrap">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <p class="font-semibold text-gray-800 text-sm">{{ $tmpl->nama }}</p>
                                        <span class="inline-flex px-2 py-0.5 text-xs rounded-full font-medium
                                            {{ $tmpl->aktif ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                            {{ $tmpl->aktif ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>
                                    {{-- Visualisasi tahapan --}}
                                    <div class="flex items-center gap-1 flex-wrap mt-2">
                                        @foreach($tmpl->stages as $s)
                                            <div class="flex items-center gap-1">
                                                <div class="px-2.5 py-1 text-xs rounded-lg
                                                    {{ $s->jenis_tindakan === 'final_approval' ? 'bg-green-100 text-green-700 font-semibold' :
                                                       ($s->jenis_tindakan === 'mengetahui' ? 'bg-blue-50 text-blue-600' :
                                                       'bg-gray-100 text-gray-700') }}">
                                                    {{ $s->label }}
                                                    <span class="opacity-60">({{ $s->jenisTindakanLabel() }})</span>
                                                </div>
                                                @if(! $loop->last)
                                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <form action="{{ route('routing-template.toggle-aktif', $tmpl) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="text-xs px-2.5 py-1.5 rounded-lg border transition-colors
                                                {{ $tmpl->aktif
                                                    ? 'border-gray-200 text-gray-600 hover:bg-gray-50'
                                                    : 'border-green-200 text-green-700 hover:bg-green-50' }}">
                                            {{ $tmpl->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('routing-template.edit', $tmpl) }}"
                                       class="text-xs px-2.5 py-1.5 rounded-lg border border-blue-200 text-blue-600 hover:bg-blue-50 transition-colors">
                                        Edit
                                    </a>
                                    <form action="{{ route('routing-template.destroy', $tmpl) }}" method="POST"
                                          onsubmit="return confirm('Hapus template ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="text-xs px-2.5 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 transition-colors">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</x-app-layout>
