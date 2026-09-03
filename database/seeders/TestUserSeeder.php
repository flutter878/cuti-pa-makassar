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

        // ── 1. Superadmin — password: 'password' ──
        User::firstOrCreate(
            ['email' => 'superadmin@pa-makassar.go.id'],
            [
                'role_id'  => $roleSuperadmin->id,
                'name'     => 'Superadmin',
                'password' => Hash::make('password'),
                'status'   => 'aktif',
            ]
        );

        // ── 2. Admin — password: 'password' ──
        User::firstOrCreate(
            ['email' => 'admin@pa-makassar.go.id'],
            [
                'role_id'  => $roleAdmin->id,
                'name'     => 'Admin Kepegawaian',
                'password' => Hash::make('password'),
                'status'   => 'aktif',
            ]
        );

        // ── 3. Ketua — NIP: 197001011990031001, password: NIP ──
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
        $userKetua = User::firstOrCreate(
            ['email' => 'ketua@pa-makassar.go.id'],
            [
                'role_id'    => $rolePegawai->id,
                'pegawai_id' => $pegawaiKetua->id,
                'name'       => $pegawaiKetua->nama,
                'password'   => Hash::make('197001011990031001'),
                'status'     => 'aktif',
            ]
        );
        // Update password jika akun sudah ada (untuk re-seed)
        $userKetua->update(['password' => Hash::make('197001011990031001'), 'pegawai_id' => $pegawaiKetua->id]);

        // ── 4. Panitera — NIP: 197205151995031002, password: NIP ──
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
        $userPanitera = User::firstOrCreate(
            ['email' => 'panitera@pa-makassar.go.id'],
            [
                'role_id'    => $rolePegawai->id,
                'pegawai_id' => $pegawaiPanitera->id,
                'name'       => $pegawaiPanitera->nama,
                'password'   => Hash::make('197205151995031002'),
                'status'     => 'aktif',
            ]
        );
        $userPanitera->update(['password' => Hash::make('197205151995031002'), 'pegawai_id' => $pegawaiPanitera->id]);

        // ── 5. Sekretaris — NIP: 197408201999031003, password: NIP ──
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
        $userSekretaris = User::firstOrCreate(
            ['email' => 'sekretaris@pa-makassar.go.id'],
            [
                'role_id'    => $rolePegawai->id,
                'pegawai_id' => $pegawaiSekretaris->id,
                'name'       => $pegawaiSekretaris->nama,
                'password'   => Hash::make('197408201999031003'),
                'status'     => 'aktif',
            ]
        );
        $userSekretaris->update(['password' => Hash::make('197408201999031003'), 'pegawai_id' => $pegawaiSekretaris->id]);

        // ── 6. Pegawai Staf — NIP: 198503102010011004, password: NIP, atasan: Panitera ──
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
        // Pastikan atasan langsung terupdate
        $pegawaiBiasa->update(['atasan_langsung_id' => $pegawaiPanitera->id]);
        SaldoCuti::inisialisasi($pegawaiBiasa->id);
        $userBiasa = User::firstOrCreate(
            ['email' => 'pegawai@pa-makassar.go.id'],
            [
                'role_id'    => $rolePegawai->id,
                'pegawai_id' => $pegawaiBiasa->id,
                'name'       => $pegawaiBiasa->nama,
                'password'   => Hash::make('198503102010011004'),
                'status'     => 'aktif',
            ]
        );
        $userBiasa->update(['password' => Hash::make('198503102010011004'), 'pegawai_id' => $pegawaiBiasa->id]);
    }
}
