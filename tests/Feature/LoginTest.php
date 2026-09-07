<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CutiTestHelper;

/**
 * Test 6.1 — Login semua role
 *
 * Memverifikasi:
 * - Login berhasil dengan kredensial valid untuk semua role
 * - Redirect ke dashboard setelah login
 * - Login gagal dengan password salah
 * - Login gagal untuk akun nonaktif
 * - Logout berfungsi
 */
class LoginTest extends TestCase
{
    use RefreshDatabase, CutiTestHelper;

    // ─── Setup data dasar ──────────────────────────────────────────────

    protected function setUp(): void
    {
        parent::setUp();
        $this->buatRole();
        $this->buatJabatan();
        $this->buatUnitKerja();
    }

    // ─── Test login role Superadmin ────────────────────────────────────

    public function test_superadmin_dapat_login(): void
    {
        $user = $this->buatUserSuperadmin();

        $response = $this->post('/login', [
            'email'    => 'superadmin@test.go.id',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    // ─── Test login role Admin ─────────────────────────────────────────

    public function test_admin_dapat_login(): void
    {
        $user = $this->buatUserAdmin();

        $response = $this->post('/login', [
            'email'    => 'admin@test.go.id',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    // ─── Test login role Pegawai (Ketua) ───────────────────────────────

    public function test_pegawai_ketua_dapat_login(): void
    {
        $data = $this->buatPegawaiKetua();

        $response = $this->post('/login', [
            'email'    => 'ketua@test.go.id',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($data['user']);
    }

    // ─── Test login role Pegawai (Panitera / Atasan Langsung) ──────────

    public function test_pegawai_panitera_dapat_login(): void
    {
        $data = $this->buatPegawaiPanitera();

        $response = $this->post('/login', [
            'email'    => 'panitera@test.go.id',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($data['user']);
    }

    // ─── Test login role Pegawai Biasa (Staf) ──────────────────────────

    public function test_pegawai_staf_dapat_login(): void
    {
        $panitera = $this->buatPegawaiPanitera();
        $data     = $this->buatPegawaiStaf($panitera['pegawai']);

        $response = $this->post('/login', [
            'email'    => $data['user']->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($data['user']);
    }

    // ─── Test login gagal: password salah ─────────────────────────────

    public function test_login_gagal_dengan_password_salah(): void
    {
        $this->buatUserAdmin();

        $response = $this->post('/login', [
            'email'    => 'admin@test.go.id',
            'password' => 'password_salah',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // ─── Test login gagal: email tidak terdaftar ───────────────────────

    public function test_login_gagal_email_tidak_ada(): void
    {
        $response = $this->post('/login', [
            'email'    => 'tidakada@test.go.id',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // ─── Test login gagal: akun nonaktif ───────────────────────────────

    public function test_login_gagal_akun_nonaktif(): void
    {
        $roles = $this->buatRole();

        // Buat akun dengan status nonaktif
        \App\Models\User::create([
            'role_id'  => $roles['admin']->id,
            'name'     => 'Admin Nonaktif',
            'email'    => 'nonaktif@test.go.id',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'status'   => 'nonaktif',
        ]);

        $response = $this->post('/login', [
            'email'    => 'nonaktif@test.go.id',
            'password' => 'password',
        ]);

        // Harus gagal — akun nonaktif tidak boleh masuk
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // ─── Test halaman dashboard dapat diakses setelah login ────────────

    public function test_dashboard_dapat_diakses_setelah_login(): void
    {
        $user = $this->buatUserAdmin();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    // ─── Test halaman dashboard redirect ke login jika belum auth ──────

    public function test_dashboard_redirect_ke_login_jika_belum_auth(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    // ─── Test logout berfungsi ─────────────────────────────────────────

    public function test_logout_berhasil(): void
    {
        $user = $this->buatUserAdmin();

        $this->actingAs($user)->post('/logout');
        $this->assertGuest();
    }

    // ─── Test root redirect ke login jika belum auth ──────────────────

    public function test_root_redirect_ke_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    // ─── Test root redirect ke dashboard jika sudah login ─────────────

    public function test_root_redirect_ke_dashboard_jika_sudah_login(): void
    {
        $user = $this->buatUserAdmin();

        $response = $this->actingAs($user)->get('/');
        $response->assertRedirect('/dashboard');
    }
}
