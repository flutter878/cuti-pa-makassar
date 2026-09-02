<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_cuti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuti_id')->constrained('cuti')->onDelete('cascade');
            $table->string('nama_file');
            $table->string('file_path');
            $table->string('tipe_file', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_cuti');
    }
};
