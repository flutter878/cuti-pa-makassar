<x-app-layout title="Detail Pegawai">

    <div class="mb-4 flex items-center justify-between">
        <a href="{{ route('pegawai.index') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar Pegawai
        </a>
        <a href="{{ route('pegawai.edit', $pegawai) }}"
           class="inline-flex items-center gap-2 bg-blue-900 hover:bg-blue-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            Edit Data
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Info Utama --}}
        <div class="lg:col-span-2 space-y-5">
            <x-form-card title="Informasi Pegawai">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500 mb-0.5">NIP</dt>
                        <dd class="font-mono font-medium text-gray-800">{{ $pegawai->nip }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-0.5">Nama Lengkap</dt>
                        <dd class="font-medium text-gray-800">{{ $pegawai->nama }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-0.5">Email</dt>
                        <dd class="text-gray-800">{{ $pegawai->email ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-0.5">No. Telepon</dt>
                        <dd class="text-gray-800">{{ $pegawai->no_telepon ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-0.5">Jabatan</dt>
                        <dd class="text-gray-800">{{ $pegawai->jabatan?->nama_jabatan ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-0.5">Unit Kerja</dt>
                        <dd class="text-gray-800">{{ $pegawai->unitKerja?->nama_unit ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-0.5">Status</dt>
                        <dd>
                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full
                                {{ $pegawai->status === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ ucfirst($pegawai->status) }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </x-form-card>

            {{-- Akun User --}}
            <x-form-card title="Akun Login">
                @if($pegawai->user)
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div>
                            <dt class="text-gray-500 mb-0.5">Email Login</dt>
                            <dd class="font-medium text-gray-800">{{ $pegawai->user->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 mb-0.5">Role</dt>
                            <dd class="text-gray-800">{{ $pegawai->user->role?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 mb-0.5">Status Akun</dt>
                            <dd>
                                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full
                                    {{ $pegawai->user->status === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ ucfirst($pegawai->user->status) }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                @else
                    <p class="text-sm text-gray-400 italic">Pegawai ini belum memiliki akun login.</p>
                @endif
            </x-form-card>
        </div>

        {{-- Saldo Cuti --}}
        <div>
            <x-form-card title="Saldo Cuti">
                @forelse($saldo as $s)
                    <div class="mb-3 p-3 bg-gray-50 rounded-lg border border-gray-100 last:mb-0">
                        <p class="text-xs font-semibold text-blue-900 mb-2">Tahun {{ $s->tahun }}</p>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <p class="text-gray-500">Hak Cuti</p>
                                <p class="font-semibold text-gray-800">{{ $s->hak_cuti }} hari</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Carry Over</p>
                                <p class="font-semibold text-gray-800">{{ $s->carry_over }} hari</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Terpakai</p>
                                <p class="font-semibold text-red-600">{{ $s->terpakai }} hari</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Sisa</p>
                                <p class="font-semibold text-green-600">{{ $s->sisa }} hari</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 italic">Belum ada data saldo.</p>
                @endforelse

                <div class="mt-3">
                    <a href="{{ route('saldo-cuti.edit', $pegawai) }}"
                       class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                        Edit Saldo Cuti →
                    </a>
                </div>
            </x-form-card>
        </div>
    </div>

</x-app-layout>
