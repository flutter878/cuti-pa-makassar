<?php

use App\Http\Controllers\AdminVerifikasiController;
use App\Http\Controllers\CutiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HariLiburController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\JenisCutiController;
use App\Http\Controllers\LaporanController;
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
    });

    // --------------------------------------------------------
    // PERSETUJUAN — semua role (pegawai approver, admin, superadmin)
    // Akses dikendalikan di dalam controller berdasarkan jabatan/role
    // --------------------------------------------------------
    Route::get('/persetujuan', [PersetujuanCutiController::class, 'index'])->name('persetujuan.index');
    Route::get('/persetujuan/{cuti}', [PersetujuanCutiController::class, 'show'])->name('persetujuan.show');
    Route::middleware(['role:pegawai'])->group(function () {
        // Aksi approve/reject hanya untuk pegawai dengan jabatan approver
        Route::post('/persetujuan/{cuti}/atasan/setujui', [PersetujuanCutiController::class, 'approveAtasan'])->name('persetujuan.atasan.setujui');
        Route::post('/persetujuan/{cuti}/atasan/tolak',   [PersetujuanCutiController::class, 'rejectAtasan'])->name('persetujuan.atasan.tolak');
        Route::post('/persetujuan/{cuti}/ketua/setujui',  [PersetujuanCutiController::class, 'approveKetua'])->name('persetujuan.ketua.setujui');
        Route::post('/persetujuan/{cuti}/ketua/tolak',    [PersetujuanCutiController::class, 'rejectKetua'])->name('persetujuan.ketua.tolak');
    });

    // Download formulir PDF — bisa diakses semua role
    Route::get('/cuti/{cuti}/formulir', [LaporanController::class, 'formulirCuti'])->name('cuti.formulir');

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

        // Verifikasi Pengajuan Cuti (Admin)
        Route::get('/admin-verifikasi', [AdminVerifikasiController::class, 'index'])->name('admin-verifikasi.index');
        Route::get('/admin-verifikasi/{cuti}', [AdminVerifikasiController::class, 'show'])->name('admin-verifikasi.show');
        Route::post('/admin-verifikasi/{cuti}/verifikasi', [AdminVerifikasiController::class, 'verifikasi'])->name('admin-verifikasi.verifikasi');
        Route::post('/admin-verifikasi/{cuti}/tolak', [AdminVerifikasiController::class, 'tolak'])->name('admin-verifikasi.tolak');

        // Hari Libur
        Route::resource('hari-libur', HariLiburController::class)->except(['show']);
        Route::patch('/hari-libur/{hariLibur}/toggle-aktif', [HariLiburController::class, 'toggleAktif'])->name('hari-libur.toggle');

        // Laporan
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
