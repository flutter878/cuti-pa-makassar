<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('persetujuan_cuti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuti_id')->constrained('cuti')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->enum('status', ['diproses', 'disetujui', 'ditolak']);
            $table->text('catatan')->nullable();
            $table->timestamp('tanggal_persetujuan')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persetujuan_cuti');
    }
};
