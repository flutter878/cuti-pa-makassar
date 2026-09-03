<?php

use App\Http\Controllers\CutiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\JenisCutiController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PersetujuanCutiController;
use App\Http\Controllers\ProfileController;
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
        Route::post('/cuti', [CutiController::class, 'store'])->name('cuti.store');
        Route::get('/cuti/{cuti}', [CutiController::class, 'show'])->name('cuti.show');
        Route::delete('/cuti/{cuti}', [CutiController::class, 'destroy'])->name('cuti.destroy');
        Route::get('/cuti/{cuti}/lampiran/{dokumen}', [CutiController::class, 'downloadLampiran'])->name('cuti.download');
        Route::get('/cuti/hitung-hari', [CutiController::class, 'hitungHari'])->name('cuti.hitung-hari');

        // Persetujuan untuk Panitera, Sekretaris, dan Ketua (role=pegawai tapi jabatan khusus)
        Route::get('/persetujuan', [PersetujuanCutiController::class, 'index'])->name('persetujuan.index');
        Route::get('/persetujuan/{cuti}', [PersetujuanCutiController::class, 'show'])->name('persetujuan.show');
        Route::post('/persetujuan/{cuti}/atasan/setujui', [PersetujuanCutiController::class, 'approveAtasan'])->name('persetujuan.atasan.setujui');
        Route::post('/persetujuan/{cuti}/atasan/tolak',   [PersetujuanCutiController::class, 'rejectAtasan'])->name('persetujuan.atasan.tolak');
        Route::post('/persetujuan/{cuti}/ketua/setujui',  [PersetujuanCutiController::class, 'approveKetua'])->name('persetujuan.ketua.setujui');
        Route::post('/persetujuan/{cuti}/ketua/tolak',    [PersetujuanCutiController::class, 'rejectKetua'])->name('persetujuan.ketua.tolak');
    });

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
