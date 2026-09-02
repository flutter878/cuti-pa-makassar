<?php

use App\Http\Controllers\CutiController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\PegawaiController;
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
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
    });

    // --------------------------------------------------------
    // SUPERADMIN SAJA
    // --------------------------------------------------------
    Route::middleware(['role:superadmin'])->group(function () {

        // Jabatan
        Route::resource('jabatan', JabatanController::class)->except(['show']);

        // Unit Kerja
        Route::resource('unit-kerja', UnitKerjaController::class)->except(['show']);
    });
});

require __DIR__.'/auth.php';
