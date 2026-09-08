<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── 1. Tambah kolom kategori ke tabel jabatan ───────────────
        if (! Schema::hasColumn('jabatan', 'kategori')) {
            Schema::table('jabatan', function (Blueprint $table) {
                $table->enum('kategori', [
                    'staf',
                    'kasubbag',
                    'panitera_muda',
                    'pejabat_fungsional',
                    'panitera_pengganti',
                    'jurusita',
                    'jurusita_pengganti',
                    'panitera',
                    'sekretaris',
                    'hakim',
                    'wakil_ketua',
                    'ketua',
                ])->default('staf')->after('nama_jabatan');
            });
        }

        // ─── 2. Buat tabel routing_template ─────────────────────
        if (! Schema::hasTable('routing_template')) {
            Schema::create('routing_template', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 100)->comment('Contoh: Alur Staf Biasa');
                $table->enum('kategori_jabatan', [
                    'staf',
                    'kasubbag',
                    'panitera_muda',
                    'pejabat_fungsional',
                    'panitera_pengganti',
                    'jurusita',
                    'jurusita_pengganti',
                    'panitera',
                    'sekretaris',
                    'hakim',
                    'wakil_ketua',
                ])->comment('Kategori jabatan pemohon yang menggunakan template ini');
                $table->boolean('aktif')->default(true);
                $table->timestamps();
            });
        }

        // ─── 3. Buat tabel routing_template_stages ───────────────────
        if (! Schema::hasTable('routing_template_stages')) {
            Schema::create('routing_template_stages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('template_id')
                      ->constrained('routing_template')
                      ->onDelete('cascade');
                $table->tinyInteger('urutan')->unsigned();
                $table->string('label', 100)
                      ->comment('Contoh: Atasan Langsung, Sekretaris, Ketua');
                $table->enum('kategori_approver', [
                    'staf',
                    'kasubbag',
                    'panitera_muda',
                    'pejabat_fungsional',
                    'panitera_pengganti',
                    'jurusita',
                    'jurusita_pengganti',
                    'panitera',
                    'sekretaris',
                    'hakim',
                    'wakil_ketua',
                    'ketua',
                ])->comment('Kategori jabatan yang diharapkan mengisi tahap ini');
                $table->enum('jenis_tindakan', [
                    'approval',
                    'mengetahui',
                    'pertimbangan',
                    'final_approval',
                ])->default('approval');
                $table->boolean('perlu_ttd')->default(true);
                $table->unique(['template_id', 'urutan']);
                $table->timestamps();
            });
        }

        // ─── 4. Buat tabel approval_stages ──────────────────────────
        if (! Schema::hasTable('approval_stages')) {
            Schema::create('approval_stages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cuti_id')
                      ->constrained('cuti')
                      ->onDelete('cascade');
                $table->tinyInteger('urutan')->unsigned();
                $table->foreignId('user_id')
                      ->constrained('users')
                      ->comment('User yang ditunjuk sebagai approver');
                $table->foreignId('pegawai_id')
                      ->nullable()
                      ->constrained('pegawai')
                      ->nullOnDelete()
                      ->comment('Data pegawai approver (snapshot relasi)');
                $table->string('nama_approver', 150)
                      ->comment('Snapshot nama saat routing dibuat');
                $table->string('jabatan_approver', 100)
                      ->comment('Snapshot jabatan saat routing dibuat');
                $table->string('label_tahap', 100)
                      ->comment('Label tampilan, contoh: Atasan Langsung');
                $table->enum('jenis_tindakan', [
                    'approval',
                    'mengetahui',
                    'pertimbangan',
                    'final_approval',
                ])->default('approval');
                $table->boolean('perlu_ttd')->default(true);
                $table->enum('status', [
                    'pending',
                    'approved',
                    'rejected',
                    'returned',
                    'skipped',
                ])->default('pending');
                $table->text('catatan')->nullable();
                $table->timestamp('tanggal_tindakan')->nullable();
                $table->timestamps();

                $table->unique(['cuti_id', 'urutan']);
            });
        }

        // ─── 5. Buat tabel cuti_audit_trail ─────────────────────────
        if (! Schema::hasTable('cuti_audit_trail')) {
            Schema::create('cuti_audit_trail', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cuti_id')
                      ->constrained('cuti')
                      ->onDelete('cascade');
                $table->foreignId('user_id')
                      ->nullable()
                      ->constrained('users')
                      ->nullOnDelete()
                      ->comment('NULL = tindakan sistem');
                $table->string('aktor', 150)
                      ->comment('Nama/label aktor yang melakukan tindakan');
                $table->string('aksi', 100)
                      ->comment('MENGAJUKAN, MEMVERIFIKASI, MENENTUKAN_ROUTING, MENYETUJUI, dll');
                $table->text('keterangan')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // ─── 6. Update enum status di tabel cuti ────────────────────
        // Langkah 1: Perluas ENUM dulu agar mencakup semua value lama + baru
        // sehingga UPDATE tidak error "Data truncated"
        DB::statement("
            ALTER TABLE cuti MODIFY COLUMN status ENUM(
                'diajukan',
                'diproses',
                'menunggu_persetujuan',
                'menunggu_verifikasi_admin',
                'menunggu_persetujuan_atasan',
                'menunggu_persetujuan_ketua',
                'menunggu_routing',
                'menunggu_approval',
                'dikembalikan',
                'disetujui',
                'ditolak',
                'dibatalkan',
                'selesai'
            ) NOT NULL DEFAULT 'menunggu_verifikasi_admin'
        ");

        // Langkah 2: Migrate semua status lama ke nilai baru
        DB::statement("
            UPDATE cuti SET status = 'menunggu_verifikasi_admin'
            WHERE status IN ('diajukan', 'diproses')
        ");
        DB::statement("
            UPDATE cuti SET status = 'menunggu_approval'
            WHERE status IN (
                'menunggu_persetujuan',
                'menunggu_persetujuan_atasan',
                'menunggu_persetujuan_ketua'
            )
        ");

        // Langkah 3: Sekarang baru MODIFY ke enum final (bersih)
        DB::statement("
            ALTER TABLE cuti MODIFY COLUMN status ENUM(
                'menunggu_verifikasi_admin',
                'menunggu_routing',
                'menunggu_approval',
                'dikembalikan',
                'disetujui',
                'ditolak',
                'dibatalkan',
                'selesai'
            ) NOT NULL DEFAULT 'menunggu_verifikasi_admin'
        ");
    }

    public function down(): void
    {
        // Kembalikan enum status cuti
        DB::statement("
            UPDATE cuti SET status = 'menunggu_verifikasi_admin'
            WHERE status IN ('menunggu_routing', 'dikembalikan', 'selesai')
        ");
        DB::statement("
            UPDATE cuti SET status = 'menunggu_persetujuan_atasan'
            WHERE status = 'menunggu_approval'
        ");

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

        // Hapus tabel baru
        Schema::dropIfExists('cuti_audit_trail');
        Schema::dropIfExists('approval_stages');
        Schema::dropIfExists('routing_template_stages');
        Schema::dropIfExists('routing_template');

        // Hapus kolom kategori dari jabatan
        Schema::table('jabatan', function (Blueprint $table) {
            $table->dropColumn('kategori');
        });
    }
};
