<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cuti extends Model
{
    protected $table = 'cuti';

    protected $fillable = [
        'nomor_pengajuan',
        'nomor_surat',
        'masa_kerja',
        'pegawai_id',
        'jenis_cuti_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_hari',
        'alasan',
        'alamat_cuti',
        'no_telepon',
        'status',
        'catatan',
        'tanggal_pengajuan',
    ];

    protected $casts = [
        'tanggal_mulai'     => 'date',
        'tanggal_selesai'   => 'date',
        'tanggal_pengajuan' => 'datetime',
    ];

    // ── Relasi ──────────────────────────────────────────────

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function jenisCuti(): BelongsTo
    {
        return $this->belongsTo(JenisCuti::class);
    }

    public function saldoDetail(): HasMany
    {
        return $this->hasMany(CutiSaldoDetail::class);
    }

    public function dokumen(): HasMany
    {
        return $this->hasMany(DokumenCuti::class);
    }

    /**
     * Tahapan approval dinamis (sistem baru)
     */
    public function approvalStages(): HasMany
    {
        return $this->hasMany(ApprovalStage::class)->orderBy('urutan');
    }

    /**
     * Audit trail seluruh tindakan pada pengajuan ini
     */
    public function auditTrail(): HasMany
    {
        return $this->hasMany(CutiAuditTrail::class)->orderBy('created_at');
    }

    /**
     * Persetujuan lama (backward compat — data sebelum sistem baru)
     */
    public function persetujuan(): HasMany
    {
        return $this->hasMany(PersetujuanCuti::class);
    }

    // ── Status helpers ──────────────────────────────────────

    public function isMenungguVerifikasiAdmin(): bool { return $this->status === 'menunggu_verifikasi_admin'; }
    public function isMenungguRouting(): bool          { return $this->status === 'menunggu_routing'; }
    public function isMenungguApproval(): bool         { return $this->status === 'menunggu_approval'; }
    public function isDikembalikan(): bool             { return $this->status === 'dikembalikan'; }
    public function isDisetujui(): bool                { return $this->status === 'disetujui'; }
    public function isDitolak(): bool                  { return $this->status === 'ditolak'; }
    public function isDibatalkan(): bool               { return $this->status === 'dibatalkan'; }
    public function isSelesai(): bool                  { return $this->status === 'selesai'; }

    /** Pegawai bisa batalkan selama belum diproses */
    public function bisaDibatalkan(): bool
    {
        return in_array($this->status, [
            'menunggu_verifikasi_admin',
            'dikembalikan',
        ], true);
    }

    /** Admin bisa verifikasi */
    public function bisaDiprosesAdmin(): bool
    {
        return $this->status === 'menunggu_verifikasi_admin';
    }

    /** Admin bisa tentukan routing */
    public function bisaDiRouting(): bool
    {
        return $this->status === 'menunggu_routing';
    }

    /** Dalam antrian approval pejabat */
    public function sedangDiApproval(): bool
    {
        return $this->status === 'menunggu_approval';
    }

    /** Pegawai bisa edit (dikembalikan) */
    public function bisaDiedit(): bool
    {
        return $this->status === 'dikembalikan';
    }

    /** Ambil tahap approval yang sedang aktif (pending) */
    public function tahapAktif(): ?ApprovalStage
    {
        return $this->approvalStages()
                    ->where('status', 'pending')
                    ->orderBy('urutan')
                    ->first();
    }

    // ── Label & warna badge status ───────────────────────────

    public function statusLabel(): string
    {
        return match ($this->status) {
            'menunggu_verifikasi_admin' => 'Menunggu Verifikasi Admin',
            'menunggu_routing'          => 'Menunggu Penentuan Routing',
            'menunggu_approval'         => 'Dalam Proses Approval',
            'dikembalikan'              => 'Dikembalikan ke Pemohon',
            'disetujui'                 => 'Disetujui',
            'ditolak'                   => 'Ditolak',
            'dibatalkan'                => 'Dibatalkan',
            'selesai'                   => 'Selesai',
            default                     => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'menunggu_verifikasi_admin' => 'bg-purple-100 text-purple-700',
            'menunggu_routing'          => 'bg-indigo-100 text-indigo-700',
            'menunggu_approval'         => 'bg-yellow-100 text-yellow-700',
            'dikembalikan'              => 'bg-orange-100 text-orange-700',
            'disetujui'                 => 'bg-green-100 text-green-700',
            'ditolak'                   => 'bg-red-100 text-red-700',
            'dibatalkan'                => 'bg-gray-100 text-gray-500',
            'selesai'                   => 'bg-teal-100 text-teal-700',
            default                     => 'bg-gray-100 text-gray-500',
        };
    }

    // ── Format nomor surat resmi ─────────────────────────────

    /**
     * Generate nomor surat resmi dari nomor awal yang diinput admin.
     * Format: {nomor}/KPA/SKET.KP4.3/{bulan-romawi}/{tahun}
     */
    public static function formatNomorSurat(string $nomorAwal): string
    {
        $bulanRomawi = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        $bulan = now()->month;
        $tahun = now()->year;

        return "{$nomorAwal}/KPA/SKET.KP4.3/{$bulanRomawi[$bulan]}/{$tahun}";
    }
}
