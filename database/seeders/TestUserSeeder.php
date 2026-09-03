<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\Role;
use App\Models\SaldoCuti;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        $rolePegawai    = Role::where('slug', 'pegawai')->first();
        $roleAdmin      = Role::where('slug', 'admin')->first();
        $roleSuperadmin = Role::where('slug', 'superadmin')->first();

        $jabatanKetua      = Jabatan::where('nama_jabatan', 'Ketua')->first();
        $jabatanPanitera   = Jabatan::where('nama_jabatan', 'Panitera')->first();
        $jabatanSekretaris = Jabatan::where('nama_jabatan', 'Sekretaris')->first();
        $jabatanStaf       = Jabatan::where('nama_jabatan', 'Staf')->first();
        $unitKerja         = UnitKerja::first();

        // ── 1. Superadmin (sudah ada dari SuperadminSeeder, pastikan ada) ──
        User::firstOrCreate(
            ['email' => 'superadmin@pa-makassar.go.id'],
            [
                'role_id'  => $roleSuperadmin->id,
                'name'     => 'Superadmin',
                'password' => Hash::make('password'),
                'status'   => 'aktif',
            ]
        );

        // ── 2. Admin ──
        User::firstOrCreate(
            ['email' => 'admin@pa-makassar.go.id'],
            [
                'role_id'  => $roleAdmin->id,
                'name'     => 'Admin Kepegawaian',
                'password' => Hash::make('password'),
                'status'   => 'aktif',
            ]
        );

        // ── 3. Ketua (role pegawai, jabatan Ketua) ──
        $pegawaiKetua = Pegawai::firstOrCreate(
            ['nip' => '197001011990031001'],
            [
                'nama'          => 'Dr. H. Ahmad Fauzi, S.H., M.H.',
                'email'         => 'ketua@pa-makassar.go.id',
                'jabatan_id'    => $jabatanKetua?->id,
                'unit_kerja_id' => $unitKerja?->id,
                'status'        => 'aktif',
            ]
        );
        SaldoCuti::inisialisasi($pegawaiKetua->id);
        User::firstOrCreate(
            ['email' => 'ketua@pa-makassar.go.id'],
            [
                'role_id'    => $rolePegawai->id,
                'pegawai_id' => $pegawaiKetua->id,
                'name'       => $pegawaiKetua->nama,
                'password'   => Hash::make('password'),
                'status'     => 'aktif',
            ]
        );

        // ── 4. Panitera (role pegawai, jabatan Panitera) ──
        $pegawaiPanitera = Pegawai::firstOrCreate(
            ['nip' => '197205151995031002'],
            [
                'nama'          => 'Hj. Siti Rahmawati, S.H.',
                'email'         => 'panitera@pa-makassar.go.id',
                'jabatan_id'    => $jabatanPanitera?->id,
                'unit_kerja_id' => $unitKerja?->id,
                'status'        => 'aktif',
            ]
        );
        SaldoCuti::inisialisasi($pegawaiPanitera->id);
        User::firstOrCreate(
            ['email' => 'panitera@pa-makassar.go.id'],
            [
                'role_id'    => $rolePegawai->id,
                'pegawai_id' => $pegawaiPanitera->id,
                'name'       => $pegawaiPanitera->nama,
                'password'   => Hash::make('password'),
                'status'     => 'aktif',
            ]
        );

        // ── 5. Sekretaris (role pegawai, jabatan Sekretaris) ──
        $pegawaiSekretaris = Pegawai::firstOrCreate(
            ['nip' => '197408201999031003'],
            [
                'nama'          => 'Drs. Muh. Saleh, S.H.',
                'email'         => 'sekretaris@pa-makassar.go.id',
                'jabatan_id'    => $jabatanSekretaris?->id,
                'unit_kerja_id' => $unitKerja?->id,
                'status'        => 'aktif',
            ]
        );
        SaldoCuti::inisialisasi($pegawaiSekretaris->id);
        User::firstOrCreate(
            ['email' => 'sekretaris@pa-makassar.go.id'],
            [
                'role_id'    => $rolePegawai->id,
                'pegawai_id' => $pegawaiSekretaris->id,
                'name'       => $pegawaiSekretaris->nama,
                'password'   => Hash::make('password'),
                'status'     => 'aktif',
            ]
        );

        // ── 6. Pegawai biasa (bawahan Panitera) ──
        $pegawaiBiasa = Pegawai::firstOrCreate(
            ['nip' => '198503102010011004'],
            [
                'nama'               => 'Budi Santoso, S.H.',
                'email'              => 'pegawai@pa-makassar.go.id',
                'jabatan_id'         => $jabatanStaf?->id,
                'unit_kerja_id'      => $unitKerja?->id,
                'atasan_langsung_id' => $pegawaiPanitera->id,
                'status'             => 'aktif',
            ]
        );
        SaldoCuti::inisialisasi($pegawaiBiasa->id);
        User::firstOrCreate(
            ['email' => 'pegawai@pa-makassar.go.id'],
            [
                'role_id'    => $rolePegawai->id,
                'pegawai_id' => $pegawaiBiasa->id,
                'name'       => $pegawaiBiasa->nama,
                'password'   => Hash::make('password'),
                'status'     => 'aktif',
            ]
        );
    }
}
