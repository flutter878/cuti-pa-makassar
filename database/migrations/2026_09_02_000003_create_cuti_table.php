<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuti', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengajuan', 30)->unique();
            $table->foreignId('pegawai_id')->constrained('pegawai')->onDelete('restrict');
            $table->foreignId('jenis_cuti_id')->constrained('jenis_cuti')->onDelete('restrict');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->integer('jumlah_hari');
            $table->text('alasan');
            $table->string('alamat_cuti');
            $table->string('no_telepon', 20)->nullable();
            $table->enum('status', [
                'diajukan',
                'diproses',
                'menunggu_persetujuan',
                'disetujui',
                'ditolak',
                'dibatalkan',
            ])->default('diajukan');
            $table->text('catatan')->nullable();
            $table->timestamp('tanggal_pengajuan')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuti');
    }
};
