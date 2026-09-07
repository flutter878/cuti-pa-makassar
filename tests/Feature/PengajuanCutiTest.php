<?php

namespace Tests\Feature;

use App\Models\Cuti;
use App\Models\PersetujuanCuti;
use App\Models\SaldoCuti;
use App\Services\CutiService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CutiTestHelper;

/**
 * Test 6.2, 6.7, 6.8 — Pengajuan Cuti & Alur 4 Tahap Lengkap
 *
 * Memverifikasi:
 * 6.2  - Pegawai dapat mengajukan cuti tahunan normal
 * 6.7  - Alur: verifikasi admin → persetujuan atasan → ke ketua
 * 6.8  - Alur: persetujuan ketua → status disetujui + saldo berkurang
 *
 * Alur lengkap:
 * Pegawai ajukan → menunggu_verifikasi_admin
 *      ↓ Admin verifikasi
 * menunggu_persetujuan_atasan
 *      ↓ Atasan setujui
 * menunggu_persetujuan_ketua
 *      ↓ Ketua setujui
 * disetujui (saldo berkurang)
 */
class PengajuanCutiTest extends TestCase
{
    use RefreshDatabase, CutiTestHelper;

    private array $aktor;
    private $jenisTahunan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buatRole();
        $this->buatJabatan();
        $this->buatUnitKerja();
        $this->aktor = $this->setupAktorLengkap();
        $this->jenisTahunan = $this->aktor['jenisCuti']['tahunan'];
    }

    // ─── 6.2: Pengajuan cuti tahunan normal ───────────────────────────

    public function test_pegawai_dapat_mengajukan_cuti_tahunan(): void
    {
        $pegawai = $this->aktor['pegawai'];
        $user    = $this->aktor['userPegawai'];

        // Set saldo cukup
        $this->setSaldo($pegawai, now()->year, 12);

        // Tanggal minimal H+5 hari (lolos validasi H-3)
        $mulai   = now()->addDays(5)->toDateString();
        $selesai = now()->addDays(7)->toDateString();

        $response = $this->actingAs($user)->post('/cuti', [
            'jenis_cuti_id'   => $this->jenisTahunan->id,
            'tanggal_mulai'   => $mulai,
            'tanggal_selesai' => $selesai,
            'alasan'          => 'Keperluan keluarga yang mendesak',
            'alamat_cuti'     => 'Jl. Pengujian No. 1, Makassar',
            'no_telepon'      => '08123456789',
        ]);

        // Harus redirect ke halaman detail cuti
        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // Cuti tersimpan di database
        $this->assertDatabaseHas('cuti', [
            'pegawai_id'    => $pegawai->id,
            'jenis_cuti_id' => $this->jenisTahunan->id,
            'tanggal_mulai' => $mulai,
            'status'        => 'menunggu_verifikasi_admin',
        ]);
    }

    public function test_nomor_pengajuan_digenerate_otomatis(): void
    {
        $pegawai = $this->aktor['pegawai'];
        $user    = $this->aktor['userPegawai'];

        $this->setSaldo($pegawai, now()->year, 12);

        $mulai   = now()->addDays(5)->toDateString();
        $selesai = now()->addDays(7)->toDateString();

        $this->actingAs($user)->post('/cuti', [
            'jenis_cuti_id'   => $this->jenisTahunan->id,
            'tanggal_mulai'   => $mulai,
            'tanggal_selesai' => $selesai,
            'alasan'          => 'Test nomor pengajuan otomatis',
            'alamat_cuti'     => 'Jl. Testing, Makassar',
        ]);

        $cuti = Cuti::where('pegawai_id', $pegawai->id)->latest()->first();
        $this->assertNotNull($cuti);
        $this->assertStringStartsWith('CUT/', $cuti->nomor_pengajuan);
    }

    public function test_status_awal_adalah_menunggu_verifikasi_admin(): void
    {
        $pegawai = $this->aktor['pegawai'];
        $user    = $this->aktor['userPegawai'];

        $this->setSaldo($pegawai, now()->year, 12);

        $this->actingAs($user)->post('/cuti', [
            'jenis_cuti_id'   => $this->jenisTahunan->id,
            'tanggal_mulai'   => now()->addDays(5)->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'alasan'          => 'Test status awal pengajuan',
            'alamat_cuti'     => 'Jl. Testing, Makassar',
        ]);

        $cuti = Cuti::where('pegawai_id', $pegawai->id)->latest()->first();
        $this->assertNotNull($cuti);
        $this->assertEquals('menunggu_verifikasi_admin', $cuti->status);
    }

    public function test_saldo_belum_berkurang_saat_diajukan(): void
    {
        $pegawai = $this->aktor['pegawai'];
        $user    = $this->aktor['userPegawai'];

        $this->setSaldo($pegawai, now()->year, 12);
        $saldoSebelum = $this->getSaldoSisa($pegawai);

        $this->actingAs($user)->post('/cuti', [
            'jenis_cuti_id'   => $this->jenisTahunan->id,
            'tanggal_mulai'   => now()->addDays(5)->toDateString(),
            'tanggal_selesai' => now()->addDays(7)->toDateString(),
            'alasan'          => 'Test saldo belum berkurang saat pengajuan',
            'alamat_cuti'     => 'Jl. Testing, Makassar',
        ]);

        $saldoSesudah = $this->getSaldoSisa($pegawai);
        // Saldo tidak boleh berkurang saat baru diajukan
        $this->assertEquals($saldoSebelum, $saldoSesudah);
    }

    // ─── 6.7: Admin verifikasi → Atasan setujui → ke Ketua ───────────

    public function test_admin_dapat_verifikasi_pengajuan(): void
    {
        $pegawai   = $this->aktor['pegawai'];
        $userAdmin = $this->aktor['userAdmin'];

        $cuti = $this->buatCuti($pegawai, $this->jenisTahunan);

        $response = $this->actingAs($userAdmin)->post("/admin-verifikasi/{$cuti->id}/verifikasi", [
            'nomor_awal' => '444',
            'masa_kerja' => '5 Tahun 3 Bulan',
        ]);

        $response->assertRedirect('/admin-verifikasi');
        $response->assertSessionHasNoErrors();

        // Status harus berubah ke menunggu_persetujuan_atasan (karena pegawai punya atasan)
        $this->assertDatabaseHas('cuti', [
            'id'          => $cuti->id,
            'status'      => 'menunggu_persetujuan_atasan',
            'masa_kerja'  => '5 Tahun 3 Bulan',
        ]);
    }

    public function test_verifikasi_admin_mengisi_nomor_surat(): void
    {
        $pegawai   = $this->aktor['pegawai'];
        $userAdmin = $this->aktor['userAdmin'];

        $cuti = $this->buatCuti($pegawai, $this->jenisTahunan);

        $this->actingAs($userAdmin)->post("/admin-verifikasi/{$cuti->id}/verifikasi", [
            'nomor_awal' => '444',
            'masa_kerja' => '5 Tahun 3 Bulan',
        ]);

        $cuti->refresh();
        $this->assertNotNull($cuti->nomor_surat);
        $this->assertStringContainsString('444', $cuti->nomor_surat);
        $this->assertStringContainsString('KPA/SKET.KP4.3', $cuti->nomor_surat);
    }

    public function test_atasan_dapat_menyetujui_pengajuan(): void
    {
        $pegawai      = $this->aktor['pegawai'];
        $userPanitera = $this->aktor['userPanitera'];

        $cuti = $this->buatCutiTerverifikasi($pegawai, $this->jenisTahunan);

        $response = $this->actingAs($userPanitera)->post("/persetujuan/{$cuti->id}/atasan/setujui", [
            'catatan' => 'Disetujui untuk keperluan keluarga',
        ]);

        $response->assertRedirect("/persetujuan/{$cuti->id}");
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('cuti', [
            'id'     => $cuti->id,
            'status' => 'menunggu_persetujuan_ketua',
        ]);
    }

    public function test_persetujuan_atasan_tercatat_di_riwayat(): void
    {
        $pegawai      = $this->aktor['pegawai'];
        $userPanitera = $this->aktor['userPanitera'];

        $cuti = $this->buatCutiTerverifikasi($pegawai, $this->jenisTahunan);

        $this->actingAs($userPanitera)->post("/persetujuan/{$cuti->id}/atasan/setujui");

        $this->assertDatabaseHas('persetujuan_cuti', [
            'cuti_id' => $cuti->id,
            'user_id' => $userPanitera->id,
            'level'   => 'atasan',
            'status'  => 'disetujui',
        ]);
    }

    // ─── 6.8: Ketua setujui → disetujui + saldo berkurang ────────────

    public function test_ketua_dapat_menyetujui_pengajuan(): void
    {
        $pegawai   = $this->aktor['pegawai'];
        $userKetua = $this->aktor['userKetua'];
        $panitera  = $this->aktor['panitera'];
        $userPanitera = $this->aktor['userPanitera'];

        $cuti = $this->buatCutiMenungguKetua($pegawai, $this->jenisTahunan, $userPanitera);

        $response = $this->actingAs($userKetua)->post("/persetujuan/{$cuti->id}/ketua/setujui", [
            'catatan' => 'Disetujui',
        ]);

        $response->assertRedirect("/persetujuan/{$cuti->id}");
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('cuti', [
            'id'     => $cuti->id,
            'status' => 'disetujui',
        ]);
    }

    public function test_saldo_berkurang_setelah_ketua_menyetujui(): void
    {
        $pegawai      = $this->aktor['pegawai'];
        $userKetua    = $this->aktor['userKetua'];
        $userPanitera = $this->aktor['userPanitera'];

        // Set saldo cukup
        $saldo = $this->setSaldo($pegawai, now()->year, 12);
        $saldoAwal = $saldo->sisa;

        $cuti = $this->buatCutiMenungguKetua($pegawai, $this->jenisTahunan, $userPanitera, [
            'jumlah_hari' => 3,
        ]);

        // Pasang detail saldo FIFO
        $this->pasangSaldoDetail($cuti, $saldo->fresh(), 3);

        $this->actingAs($userKetua)->post("/persetujuan/{$cuti->id}/ketua/setujui");

        $saldoSetelah = $this->getSaldoSisa($pegawai);
        $this->assertEquals($saldoAwal - 3, $saldoSetelah);
    }

    public function test_persetujuan_ketua_tercatat_di_riwayat(): void
    {
        $pegawai      = $this->aktor['pegawai'];
        $userKetua    = $this->aktor['userKetua'];
        $userPanitera = $this->aktor['userPanitera'];

        $cuti = $this->buatCutiMenungguKetua($pegawai, $this->jenisTahunan, $userPanitera);

        $this->actingAs($userKetua)->post("/persetujuan/{$cuti->id}/ketua/setujui");

        $this->assertDatabaseHas('persetujuan_cuti', [
            'cuti_id' => $cuti->id,
            'user_id' => $userKetua->id,
            'level'   => 'ketua',
            'status'  => 'disetujui',
        ]);
    }

    // ─── Test alur 4 tahap end-to-end ─────────────────────────────────

    public function test_alur_4_tahap_lengkap_dari_pengajuan_sampai_disetujui(): void
    {
        $pegawai      = $this->aktor['pegawai'];
        $userPegawai  = $this->aktor['userPegawai'];
        $userAdmin    = $this->aktor['userAdmin'];
        $userPanitera = $this->aktor['userPanitera'];
        $userKetua    = $this->aktor['userKetua'];

        $saldo = $this->setSaldo($pegawai, now()->year, 12);

        // TAHAP 1 — Pegawai ajukan cuti
        $mulai   = now()->addDays(5)->toDateString();
        $selesai = now()->addDays(7)->toDateString();

        $this->actingAs($userPegawai)->post('/cuti', [
            'jenis_cuti_id'   => $this->jenisTahunan->id,
            'tanggal_mulai'   => $mulai,
            'tanggal_selesai' => $selesai,
            'alasan'          => 'Keperluan mendesak untuk test alur 4 tahap',
            'alamat_cuti'     => 'Jl. Test End-to-End, Makassar',
        ]);

        $cuti = Cuti::where('pegawai_id', $pegawai->id)->latest()->first();
        $this->assertEquals('menunggu_verifikasi_admin', $cuti->status);

        // TAHAP 2 — Admin verifikasi
        $this->actingAs($userAdmin)->post("/admin-verifikasi/{$cuti->id}/verifikasi", [
            'nomor_awal' => '500',
            'masa_kerja' => '6 Tahun',
        ]);

        $cuti->refresh();
        $this->assertEquals('menunggu_persetujuan_atasan', $cuti->status);

        // TAHAP 3 — Atasan setujui
        $this->actingAs($userPanitera)->post("/persetujuan/{$cuti->id}/atasan/setujui");

        $cuti->refresh();
        $this->assertEquals('menunggu_persetujuan_ketua', $cuti->status);

        // TAHAP 4 — Ketua setujui
        $this->actingAs($userKetua)->post("/persetujuan/{$cuti->id}/ketua/setujui");

        $cuti->refresh();
        $this->assertEquals('disetujui', $cuti->status);

        // Verifikasi ada 2 record persetujuan
        $this->assertEquals(2, PersetujuanCuti::where('cuti_id', $cuti->id)->count());
    }

    // ─── Test tidak bisa skip tahap ───────────────────────────────────

    public function test_atasan_tidak_bisa_proses_sebelum_verifikasi_admin(): void
    {
        $pegawai      = $this->aktor['pegawai'];
        $userPanitera = $this->aktor['userPanitera'];

        // Cuti baru diajukan, belum diverifikasi admin
        $cuti = $this->buatCuti($pegawai, $this->jenisTahunan, [
            'status' => 'menunggu_verifikasi_admin',
        ]);

        $response = $this->actingAs($userPanitera)->post("/persetujuan/{$cuti->id}/atasan/setujui");

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Status tidak berubah
        $this->assertDatabaseHas('cuti', [
            'id'     => $cuti->id,
            'status' => 'menunggu_verifikasi_admin',
        ]);
    }

    public function test_ketua_tidak_bisa_proses_sebelum_disetujui_atasan(): void
    {
        $pegawai   = $this->aktor['pegawai'];
        $userKetua = $this->aktor['userKetua'];

        // Cuti sudah terverifikasi admin tapi belum disetujui atasan
        $cuti = $this->buatCutiTerverifikasi($pegawai, $this->jenisTahunan);

        $response = $this->actingAs($userKetua)->post("/persetujuan/{$cuti->id}/ketua/setujui");

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('cuti', [
            'id'     => $cuti->id,
            'status' => 'menunggu_persetujuan_atasan',
        ]);
    }
}
