<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom atasan_langsung_id di tabel pegawai
        Schema::table('pegawai', function (Blueprint $table) {
            $table->foreignId('atasan_langsung_id')
                  ->nullable()
                  ->after('unit_kerja_id')
                  ->constrained('pegawai')
                  ->nullOnDelete();
        });

        // 2. Tambah kolom level di persetujuan_cuti
        Schema::table('persetujuan_cuti', function (Blueprint $table) {
            $table->enum('level', ['atasan', 'ketua'])
                  ->after('user_id')
                  ->default('atasan');
        });

        // 3. Update enum status di tabel cuti
        //    diajukan → menunggu_atasan → menunggu_ketua → disetujui / ditolak / dibatalkan
        DB::statement("ALTER TABLE cuti MODIFY COLUMN status ENUM(
            'diajukan',
            'menunggu_atasan',
            'menunggu_ketua',
            'disetujui',
            'ditolak',
            'dibatalkan'
        ) NOT NULL DEFAULT 'diajukan'");
    }

    public function down(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->dropForeign(['atasan_langsung_id']);
            $table->dropColumn('atasan_langsung_id');
        });

        Schema::table('persetujuan_cuti', function (Blueprint $table) {
            $table->dropColumn('level');
        });

        DB::statement("ALTER TABLE cuti MODIFY COLUMN status ENUM(
            'diajukan',
            'diproses',
            'menunggu_persetujuan',
            'disetujui',
            'ditolak',
            'dibatalkan'
        ) NOT NULL DEFAULT 'diajukan'");
    }
};
