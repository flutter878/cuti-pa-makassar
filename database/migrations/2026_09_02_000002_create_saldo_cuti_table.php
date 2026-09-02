<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saldo_cuti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawai')->onDelete('cascade');
            $table->year('tahun');
            $table->integer('hak_cuti')->default(12);
            $table->integer('carry_over')->default(0);
            $table->integer('terpakai')->default(0);
            $table->integer('sisa')->virtualAs('hak_cuti + carry_over - terpakai');
            $table->timestamps();

            $table->unique(['pegawai_id', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saldo_cuti');
    }
};
