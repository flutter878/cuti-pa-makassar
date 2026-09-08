<?php

use App\Http\Controllers\AdminVerifikasiController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\CutiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HariLiburController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\JenisCutiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoutingTemplateController;
use App\Http\Controllers\SaldoCutiController;
use App\Http\Controllers\UnitKerjaController;
use Illuminate\Support\Facades\Route;

// Redirect root ke dashboard atau login
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// Dashboard — semua role yang sudah login
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// ============================================================
// ROUTE YANG MEMBUTUHKAN AUTH
// ============================================================
Route::middleware(['auth'])->group(function () {

    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --------------------------------------------------------
    // PEGAWAI — Pengajuan Cuti
    // --------------------------------------------------------
    Route::middleware(['role:pegawai'])->group(function () {
        Route::get('/cuti', [CutiController::class, 'index'])->name('cuti.index');
        Route::get('/cuti/ajukan', [CutiController::class, 'create'])->name('cuti.create');
        Route::get('/cuti/hitung-hari', [CutiController::class, 'hitungHari'])->name('cuti.hitung-hari');
        Route::post('/cuti', [CutiController::class, 'store'])->name('cuti.store');
        Route::get('/cuti/{cuti}', [CutiController::class, 'show'])->name('cuti.show');

        // Edit pengajuan yang dikembalikan
        Route::get('/cuti/{cuti}/edit', [CutiController::class, 'edit'])->name('cuti.edit');
        Route::put('/cuti/{cuti}', [CutiController::class, 'update'])->name('cuti.update');

        Route::delete('/cuti/{cuti}', [CutiController::class, 'destroy'])->name('cuti.destroy');
        Route::get('/cuti/{cuti}/lampiran/{dokumen}', [CutiController::class, 'downloadLampiran'])->name('cuti.download');
    });

    // Download formulir PDF — bisa diakses semua role
    Route::get('/cuti/{cuti}/formulir', [LaporanController::class, 'formulirCuti'])->name('cuti.formulir');

    // --------------------------------------------------------
    // APPROVAL — pejabat yang ditugaskan sebagai approver
    // Semua role yang sudah login bisa akses (akses dikontrol di controller)
    // --------------------------------------------------------
    Route::get('/approval', [ApprovalController::class, 'index'])->name('approval.index');
    Route::get('/approval/{cuti}', [ApprovalController::class, 'show'])->name('approval.show');
    Route::post('/approval/{cuti}/stage/{stage}/setujui', [ApprovalController::class, 'setujui'])
        ->name('approval.setujui');
    Route::post('/approval/{cuti}/stage/{stage}/tolak', [ApprovalController::class, 'tolak'])
        ->name('approval.tolak');
    Route::post('/approval/{cuti}/stage/{stage}/kembalikan', [ApprovalController::class, 'kembalikan'])
        ->name('approval.kembalikan');

    // --------------------------------------------------------
    // ADMIN & SUPERADMIN
    // --------------------------------------------------------
    Route::middleware(['role:admin,superadmin'])->group(function () {

        // Data Pegawai
        Route::resource('pegawai', PegawaiController::class);

        // Saldo Cuti
        Route::get('/saldo-cuti', [SaldoCutiController::class, 'index'])->name('saldo-cuti.index');
        Route::get('/saldo-cuti/{pegawai}/edit', [SaldoCutiController::class, 'edit'])->name('saldo-cuti.edit');
        Route::put('/saldo-cuti/{pegawai}', [SaldoCutiController::class, 'update'])->name('saldo-cuti.update');
        Route::get('/saldo-cuti/{pegawai}/detail', [SaldoCutiController::class, 'show'])->name('saldo-cuti.show');

        // Manajemen Pengguna
        Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');
        Route::get('/pengguna/tambah', [PenggunaController::class, 'create'])->name('pengguna.create');
        Route::post('/pengguna', [PenggunaController::class, 'store'])->name('pengguna.store');
        Route::get('/pengguna/{user}/edit', [PenggunaController::class, 'edit'])->name('pengguna.edit');
        Route::put('/pengguna/{user}', [PenggunaController::class, 'update'])->name('pengguna.update');
        Route::patch('/pengguna/{user}/toggle-status', [PenggunaController::class, 'toggleStatus'])->name('pengguna.toggle-status');

        // ──── Verifikasi & Routing Pengajuan Cuti (Admin) ────
        Route::get('/admin-verifikasi', [AdminVerifikasiController::class, 'index'])
            ->name('admin-verifikasi.index');
        Route::get('/admin-verifikasi/{cuti}', [AdminVerifikasiController::class, 'show'])
            ->name('admin-verifikasi.show');
        Route::post('/admin-verifikasi/{cuti}/verifikasi', [AdminVerifikasiController::class, 'verifikasi'])
            ->name('admin-verifikasi.verifikasi');

        // Step 2: tentukan routing
        Route::get('/admin-verifikasi/{cuti}/routing', [AdminVerifikasiController::class, 'routing'])
            ->name('admin-verifikasi.routing');
        Route::post('/admin-verifikasi/{cuti}/teruskan', [AdminVerifikasiController::class, 'teruskan'])
            ->name('admin-verifikasi.teruskan');

        Route::post('/admin-verifikasi/{cuti}/tolak', [AdminVerifikasiController::class, 'tolak'])
            ->name('admin-verifikasi.tolak');

        // AJAX: cek kuota cuti per tanggal
        Route::get('/admin-verifikasi/api/cek-kuota', [AdminVerifikasiController::class, 'cekKuota'])
            ->name('admin-verifikasi.cek-kuota');

        // ──── Template Routing ───────────────────────────────
        Route::resource('routing-template', RoutingTemplateController::class)
            ->except(['show']);
        Route::patch('/routing-template/{routingTemplate}/toggle-aktif', [RoutingTemplateController::class, 'toggleAktif'])
            ->name('routing-template.toggle-aktif');

        // ──── Hari Libur ─────────────────────────────────────
        Route::resource('hari-libur', HariLiburController::class)->except(['show']);
        Route::patch('/hari-libur/{hariLibur}/toggle-aktif', [HariLiburController::class, 'toggleAktif'])
            ->name('hari-libur.toggle');

        // ──── Laporan ────────────────────────────────────────
        Route::get('/laporan/pengajuan', [LaporanController::class, 'pengajuan'])->name('laporan.cuti');
        Route::get('/laporan/pengajuan/export', [LaporanController::class, 'exportPengajuan'])->name('laporan.export-pengajuan');
        Route::get('/laporan/saldo', [LaporanController::class, 'saldo'])->name('laporan.saldo');
        Route::get('/laporan/saldo/export', [LaporanController::class, 'exportSaldo'])->name('laporan.export-saldo');
        Route::get('/laporan/formulir/{cuti}', [LaporanController::class, 'formulirCuti'])->name('laporan.formulir');
    });

    // --------------------------------------------------------
    // SUPERADMIN SAJA
    // --------------------------------------------------------
    Route::middleware(['role:superadmin'])->group(function () {

        // Jabatan
        Route::resource('jabatan', JabatanController::class)->except(['show']);

        // Unit Kerja
        Route::resource('unit-kerja', UnitKerjaController::class)->except(['show']);

        // Jenis Cuti
        Route::resource('jenis-cuti', JenisCutiController::class)->except(['show']);
    });
});

require __DIR__.'/auth.php';
