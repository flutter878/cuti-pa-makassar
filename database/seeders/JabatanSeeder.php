<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use Illuminate\Database\Seeder;

class JabatanSeeder extends Seeder
{
    public function run(): void
    {
        // Format: [nama_jabatan => kategori]
        $jabatan = [
            'Ketua'                          => 'ketua',
            'Wakil Ketua'                    => 'wakil_ketua',
            'Hakim'                          => 'hakim',
            'Panitera'                       => 'panitera',
            'Wakil Panitera'                 => 'panitera_muda',
            'Panitera Muda Gugatan'          => 'panitera_muda',
            'Panitera Muda Permohonan'       => 'panitera_muda',
            'Panitera Muda Hukum'            => 'panitera_muda',
            'Panitera Pengganti'             => 'panitera_pengganti',
            'Jurusita'                       => 'jurusita',
            'Jurusita Pengganti'             => 'jurusita_pengganti',
            'Sekretaris'                     => 'sekretaris',
            'Kepala Sub Bagian Kepegawaian'  => 'kasubbag',
            'Kepala Sub Bagian Keuangan'     => 'kasubbag',
            'Kepala Sub Bagian Umum'         => 'kasubbag',
            'Staf'                           => 'staf',
        ];

        foreach ($jabatan as $nama => $kategori) {
            Jabatan::firstOrCreate(
                ['nama_jabatan' => $nama],
                ['kategori'     => $kategori]
            );

            // Update kategori jika jabatan sudah ada (untuk re-seed)
            Jabatan::where('nama_jabatan', $nama)
                ->whereNull('kategori')
                ->orWhere('nama_jabatan', $nama)
                ->update(['kategori' => $kategori]);
        }
    }
}
