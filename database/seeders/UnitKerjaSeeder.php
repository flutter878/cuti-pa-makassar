<?php

namespace Database\Seeders;

use App\Models\UnitKerja;
use Illuminate\Database\Seeder;

class UnitKerjaSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            'Kepaniteraan Gugatan',
            'Kepaniteraan Permohonan',
            'Kepaniteraan Hukum',
            'Sub Bagian Kepegawaian dan Ortala',
            'Sub Bagian Keuangan dan Pelaporan',
            'Sub Bagian Umum dan Teknologi Informasi',
            'Hakim',
        ];

        foreach ($units as $nama) {
            UnitKerja::firstOrCreate(['nama_unit' => $nama]);
        }
    }
}
