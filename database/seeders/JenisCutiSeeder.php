<?php

namespace Database\Seeders;

use App\Models\JenisCuti;
use Illuminate\Database\Seeder;

class JenisCutiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'nama'                 => 'Cuti Tahunan',
                'kode'                 => 'CT',
                'membutuhkan_lampiran' => false,
                'mengurangi_saldo'     => true,
                'batas_hari'           => 12,
                'status'               => 'aktif',
            ],
            [
                'nama'                 => 'Cuti Besar',
                'kode'                 => 'CB',
                'membutuhkan_lampiran' => false,
                'mengurangi_saldo'     => false,
                'batas_hari'           => 90,
                'status'               => 'aktif',
            ],
            [
                'nama'                 => 'Cuti Sakit',
                'kode'                 => 'CS',
                'membutuhkan_lampiran' => true,
                'mengurangi_saldo'     => false,
                'batas_hari'           => null,
                'status'               => 'aktif',
            ],
            [
                'nama'                 => 'Cuti Melahirkan',
                'kode'                 => 'CM',
                'membutuhkan_lampiran' => false,
                'mengurangi_saldo'     => false,
                'batas_hari'           => 90,
                'status'               => 'aktif',
            ],
            [
                'nama'                 => 'Cuti Karena Alasan Penting',
                'kode'                 => 'CAP',
                'membutuhkan_lampiran' => false,
                'mengurangi_saldo'     => false,
                'batas_hari'           => null,
                'status'               => 'aktif',
            ],
            [
                'nama'                 => 'Cuti di Luar Tanggungan Negara',
                'kode'                 => 'CLTN',
                'membutuhkan_lampiran' => true,
                'mengurangi_saldo'     => false,
                'batas_hari'           => null,
                'status'               => 'aktif',
            ],
        ];

        foreach ($data as $item) {
            JenisCuti::firstOrCreate(['kode' => $item['kode']], $item);
        }
    }
}
