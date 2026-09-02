<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_cuti', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kode', 10)->unique();
            $table->boolean('membutuhkan_lampiran')->default(false);
            $table->boolean('mengurangi_saldo')->default(true);
            $table->integer('batas_hari')->nullable()->comment('null = tidak ada batas');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_cuti');
    }
};
