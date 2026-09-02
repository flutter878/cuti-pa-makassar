<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuti_saldo_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuti_id')->constrained('cuti')->onDelete('cascade');
            $table->foreignId('saldo_cuti_id')->constrained('saldo_cuti')->onDelete('restrict');
            $table->integer('jumlah_digunakan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuti_saldo_detail');
    }
};
