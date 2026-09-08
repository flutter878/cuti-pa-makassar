<?php

namespace Tests\Traits;

use App\Models\Cuti;
use App\Models\CutiSaldoDetail;
use App\Models\Jabatan;
use App\Models\JenisCuti;
use App\Models\Pegawai;
use App\Models\PersetujuanCuti;
use App\Models\Role;
use App\Models\SaldoCuti;
use App\Models\UnitKerja;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Trait CutiTestHelper
 *
 * Menyediakan helper untuk membuat data testing:
 * role, jabatan, unit kerja, pegawai, user, jenis cuti, saldo, dan cuti.
 */
trait CutiTestHelper
{
    // ─── Role ──────────────────────────────────────────────────────────

    protected function buatRole(): array
    {
        $rolePegawai    = Role::firstOrCreate(['slug' => 'pegawai'],    ['nama' => 'Pegawai']);
        $roleAdmin      = Role::firstOrCreate(['slug' => 'admin'],      ['nama' => 'Admin']);
        $roleSuperadmin = Role::firstOrCreate(['slug' => 'superadmin'], ['nama' => 'Superadmin']);

        return [
            'pegawai'    => $rolePegawai,
            'admin'      => $roleAdmin,
            'superadmin' => $roleSuperadmin,
        ];
    }

    // ─── Jabatan ───────────────────────────────────────────────────────

    protected function buatJabatan(): array
    {
        return [
            'ketua'      => Jabatan::firstOrCreate(['nama_jabatan' => 'Ketua']),
            'panitera'   => Jabatan::firstOrCreate(['nama_jabatan' => 'Panitera']),
            'sekretaris' => Jabatan::firstOrCreate(['nama_jabatan' => 'Sekretaris']),
            'staf'       => Jabatan::firstOrCreate(['nama_jabatan' => 'Staf']),
        ];
    }

    // ─── Unit Kerja ────────────────────────────────────────────────────

    protected function buatUnitKerja(): UnitKerja
    {
        return UnitKerja::firstOrCreate(['nama_unit' => 'Kepaniteraan Hukum']);
    }

    // ─── Jenis Cuti ────────────────────────────────────────────────────

    protected function buatJenisCuti(): array
    {
        return [
            'tahunan' => JenisCuti::firstOrCreate(
                ['kode' => 'CT'],
                [
                    'nama'              => 'Cuti Tahunan',
                    'mengurangi_saldo'  => true,
                    'membutuhkan_lampiran' => false,
                    'aktif'             => true,
                ]
            ),
            'sakit' => JenisCuti::firstOrCreate(
                ['kode' => 'CS'],
                [
                    'nama'              => 'Cuti Sakit',
                    'mengurangi_saldo'  => false,
                    'membutuhkan_lampiran' => true,
                    'aktif'             => true,
                ]
            ),
            'besar' => JenisCuti::firstOrCreate(
                ['kode' => 'CB'],
                [
                    'nama'              => 'Cuti Besar',
                    'mengurangi_saldo'  => true,
                    'membutuhkan_lampiran' => false,
                    'aktif'             => true,
                ]
            ),
        ];
    }

    // ─── Pegawai + User ────────────────────────────────────────────────

    protected function buatUserSuperadmin(): User
    {
        $roles = $this->buatRole();

        return User::firstOrCreate(
            ['email' => 'superadmin@test.go.id'],
            [
                'role_id'  => $roles['superadmin']->id,
                'name'     => 'Superadmin Test',
                'password' => Hash::make('password'),
                'status'   => 'aktif',
            ]
        );
    }

    protected function buatUserAdmin(): User
    {
        $roles = $this->buatRole();

        return User::firstOrCreate(
            ['email' => 'admin@test.go.id'],
            [
                'role_id'  => $roles['admin']->id,
                'name'     => 'Admin Test',
                'password' => Hash::make('password'),
                'status'   => 'aktif',
            ]
        );
    }

    /**
     * Buat pegawai Ketua beserta user-nya
     */
    protected function buatPegawaiKetua(): array
    {
        $roles    = $this->buatRole();
        $jabatan  = $this->buatJabatan();
        $unitKerja = $this->buatUnitKerja();

        $pegawai = Pegawai::firstOrCreate(
            ['nip' => '197001011990031901'],
            [
                'nama'          => 'Ketua Test',
                'email'         => 'ketua@test.go.id',
                'jabatan_id'    => $jabatan['ketua']->id,
                'unit_kerja_id' => $unitKerja->id,
                'status'        => 'aktif',
            ]
        );
        SaldoCuti::inisialisasi($pegawai->id);

        $user = User::firstOrCreate(
            ['email' => 'ketua@test.go.id'],
            [
                'role_id'    => $roles['pegawai']->id,
                'pegawai_id' => $pegawai->id,
                'name'       => $pegawai->nama,
                'password'   => Hash::make('password'),
                'status'     => 'aktif',
            ]
        );

        return compact('pegawai', 'user');
    }

    /**
     * Buat pegawai Panitera (Atasan Langsung) beserta user-nya
     */
    protected function buatPegawaiPanitera(): array
    {
        $roles    = $this->buatRole();
        $jabatan  = $this->buatJabatan();
        $unitKerja = $this->buatUnitKerja();

        $pegawai = Pegawai::firstOrCreate(
            ['nip' => '197205151995031902'],
            [
                'nama'          => 'Panitera Test',
                'email'         => 'panitera@test.go.id',
                'jabatan_id'    => $jabatan['panitera']->id,
                'unit_kerja_id' => $unitKerja->id,
                'status'        => 'aktif',
            ]
        );
        SaldoCuti::inisialisasi($pegawai->id);

        $user = User::firstOrCreate(
            ['email' => 'panitera@test.go.id'],
            [
                'role_id'    => $roles['pegawai']->id,
                'pegawai_id' => $pegawai->id,
                'name'       => $pegawai->nama,
                'password'   => Hash::make('password'),
                'status'     => 'aktif',
            ]
        );

        return compact('pegawai', 'user');
    }

    /**
     * Buat pegawai biasa (Staf) dengan atasan langsung Panitera
     */
    protected function buatPegawaiStaf(?Pegawai $atasan = null): array
    {
        $roles    = $this->buatRole();
        $jabatan  = $this->buatJabatan();
        $unitKerja = $this->buatUnitKerja();

        // Pakai NIP acak supaya tidak tabrakan antar test
        $nip = '19850310' . rand(10000, 99999);

        $pegawai = Pegawai::create([
            'nip'                => $nip,
            'nama'               => 'Staf Test ' . $nip,
            'email'              => "staf_{$nip}@test.go.id",
            'jabatan_id'         => $jabatan['staf']->id,
            'unit_kerja_id'      => $unitKerja->id,
            'atasan_langsung_id' => $atasan?->id,
            'status'             => 'aktif',
        ]);
        SaldoCuti::inisialisasi($pegawai->id);

        $user = User::create([
            'role_id'    => $roles['pegawai']->id,
            'pegawai_id' => $pegawai->id,
            'name'       => $pegawai->nama,
            'email'      => "user_staf_{$nip}@test.go.id",
            'password'   => Hash::make('password'),
            'status'     => 'aktif',
        ]);

        return compact('pegawai', 'user');
    }

    // ─── Saldo Cuti ────────────────────────────────────────────────────

    /**
     * Set saldo cuti pegawai untuk tahun tertentu
     */
    protected function setSaldo(Pegawai $pegawai, int $tahun, int $hakCuti, int $carryOver = 0, int $terpakai = 0): SaldoCuti
    {
        $saldo = SaldoCuti::firstOrCreate(
            ['pegawai_id' => $pegawai->id, 'tahun' => $tahun],
            ['hak_cuti' => $hakCuti, 'carry_over' => $carryOver, 'terpakai' => $terpakai]
        );
        $saldo->update(['hak_cuti' => $hakCuti, 'carry_over' => $carryOver, 'terpakai' => $terpakai]);
        return $saldo->fresh();
    }

    /**
     * Ambil sisa saldo cuti pegawai untuk tahun ini
     */
    protected function getSaldoSisa(Pegawai $pegawai, int $tahun = null): int
    {
        $tahun ??= now()->year;
        $saldo = SaldoCuti::where('pegawai_id', $pegawai->id)->where('tahun', $tahun)->first();
        return $saldo ? $saldo->sisa : 0;
    }

    // ─── Cuti ──────────────────────────────────────────────────────────

    /**
     * Buat pengajuan cuti langsung di DB (bypass controller, untuk setup state testing)
     */
    protected function buatCuti(Pegawai $pegawai, JenisCuti $jenisCuti, array $override = []): Cuti
    {
        $tanggalMulai   = Carbon::parse($override['tanggal_mulai']   ?? now()->addDays(5)->toDateString());
        $tanggalSelesai = Carbon::parse($override['tanggal_selesai'] ?? now()->addDays(7)->toDateString());

        return Cuti::create(array_merge([
            'nomor_pengajuan'  => 'CUT/' . now()->format('Y/m') . '/' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'pegawai_id'       => $pegawai->id,
            'jenis_cuti_id'    => $jenisCuti->id,
            'tanggal_mulai'    => $tanggalMulai->toDateString(),
            'tanggal_selesai'  => $tanggalSelesai->toDateString(),
            'jumlah_hari'      => 3,
            'alasan'           => 'Keperluan keluarga untuk test',
            'alamat_cuti'      => 'Jl. Testing No. 1, Makassar',
            'no_telepon'       => '08123456789',
            'status'           => 'menunggu_verifikasi_admin',
            'tanggal_pengajuan' => now(),
        ], $override));
    }

    /**
     * Buat cuti yang sudah terverifikasi admin (menunggu_persetujuan_atasan)
     */
    protected function buatCutiTerverifikasi(Pegawai $pegawai, JenisCuti $jenisCuti, array $override = []): Cuti
    {
        $cuti = $this->buatCuti($pegawai, $jenisCuti, array_merge([
            'status'      => 'menunggu_persetujuan_atasan',
            'nomor_surat' => '100/KPA/SKET.KP4.3/IX/2026',
            'masa_kerja'  => '5 Tahun 3 Bulan',
        ], $override));

        return $cuti;
    }

    /**
     * Buat cuti yang sudah disetujui atasan (menunggu_persetujuan_ketua)
     */
    protected function buatCutiMenungguKetua(Pegawai $pegawai, JenisCuti $jenisCuti, User $userAtasan, array $override = []): Cuti
    {
        $cuti = $this->buatCutiTerverifikasi($pegawai, $jenisCuti, array_merge([
            'status' => 'menunggu_persetujuan_ketua',
        ], $override));

        PersetujuanCuti::create([
            'cuti_id'            => $cuti->id,
            'user_id'            => $userAtasan->id,
            'level'              => 'atasan',
            'status'             => 'disetujui',
            'catatan'            => null,
            'tanggal_persetujuan' => now(),
        ]);

        return $cuti;
    }

    /**
     * Pasangkan detail saldo FIFO ke cuti (untuk test kurangi/kembalikan saldo)
     */
    protected function pasangSaldoDetail(Cuti $cuti, SaldoCuti $saldo, int $jumlah): CutiSaldoDetail
    {
        return CutiSaldoDetail::create([
            'cuti_id'          => $cuti->id,
            'saldo_cuti_id'    => $saldo->id,
            'jumlah_digunakan' => $jumlah,
        ]);
    }

    // ─── Setup lengkap skenario pengajuan ──────────────────────────────

    /**
     * Setup lengkap: buat semua aktor yang dibutuhkan untuk test alur lengkap
     * Kembalikan: ['pegawai', 'userPegawai', 'panitera', 'userPanitera',
     *              'ketua', 'userKetua', 'userAdmin', 'jenisCuti']
     */
    protected function setupAktorLengkap(): array
    {
        $jenisCuti  = $this->buatJenisCuti();
        $panitera   = $this->buatPegawaiPanitera();
        $ketua      = $this->buatPegawaiKetua();
        $staf       = $this->buatPegawaiStaf($panitera['pegawai']);
        $userAdmin  = $this->buatUserAdmin();

        return [
            'pegawai'      => $staf['pegawai'],
            'userPegawai'  => $staf['user'],
            'panitera'     => $panitera['pegawai'],
            'userPanitera' => $panitera['user'],
            'ketua'        => $ketua['pegawai'],
            'userKetua'    => $ketua['user'],
            'userAdmin'    => $userAdmin,
            'jenisCuti'    => $jenisCuti,
        ];
    }
}
