<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use Illuminate\Database\Seeder;

class JabatanSeeder extends Seeder
{
    public function run(): void
    {
        $jabatan = [
            'Ketua',
            'Wakil Ketua',
            'Hakim',
            'Panitera',
            'Wakil Panitera',
            'Panitera Muda Gugatan',
            'Panitera Muda Permohonan',
            'Panitera Muda Hukum',
            'Panitera Pengganti',
            'Jurusita',
            'Jurusita Pengganti',
            'Sekretaris',
            'Kepala Sub Bagian Kepegawaian',
            'Kepala Sub Bagian Keuangan',
            'Kepala Sub Bagian Umum',
            'Staf',
        ];

        foreach ($jabatan as $nama) {
            Jabatan::firstOrCreate(['nama_jabatan' => $nama]);
        }
    }
}
