<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cuti', function (Blueprint $table) {
            // Nomor surat resmi — diisi Admin saat verifikasi
            $table->string('nomor_surat', 100)->nullable()->after('nomor_pengajuan');

            // Masa kerja — diisi Admin saat verifikasi (contoh: "4 Tahun 6 Bulan")
            $table->string('masa_kerja', 50)->nullable()->after('nomor_surat');
        });

        // Update nilai status yang lama ke nama baru agar data tidak rusak
        // menunggu_atasan → menunggu_persetujuan_atasan
        // menunggu_ketua  → menunggu_persetujuan_ketua
        DB::statement("
            UPDATE cuti SET status = 'menunggu_persetujuan_atasan'
            WHERE status = 'menunggu_atasan'
        ");
        DB::statement("
            UPDATE cuti SET status = 'menunggu_persetujuan_ketua'
            WHERE status = 'menunggu_ketua'
        ");

        // Ubah kolom status ke enum lengkap dengan status baru
        DB::statement("
            ALTER TABLE cuti MODIFY COLUMN status ENUM(
                'menunggu_verifikasi_admin',
                'menunggu_persetujuan_atasan',
                'menunggu_persetujuan_ketua',
                'disetujui',
                'ditolak',
                'dibatalkan'
            ) NOT NULL DEFAULT 'menunggu_verifikasi_admin'
        ");
    }

    public function down(): void
    {
        Schema::table('cuti', function (Blueprint $table) {
            $table->dropColumn(['nomor_surat', 'masa_kerja']);
        });

        // Kembalikan status ke enum lama
        DB::statement("
            UPDATE cuti SET status = 'menunggu_atasan'
            WHERE status = 'menunggu_persetujuan_atasan'
        ");
        DB::statement("
            UPDATE cuti SET status = 'menunggu_ketua'
            WHERE status = 'menunggu_persetujuan_ketua'
        ");
        DB::statement("
            UPDATE cuti SET status = 'menunggu_atasan'
            WHERE status = 'menunggu_verifikasi_admin'
        ");

        DB::statement("
            ALTER TABLE cuti MODIFY COLUMN status ENUM(
                'diajukan',
                'menunggu_atasan',
                'menunggu_ketua',
                'disetujui',
                'ditolak',
                'dibatalkan'
            ) NOT NULL DEFAULT 'menunggu_atasan'
        ");
    }
};
