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

    public function persetujuan(): HasMany
    {
        return $this->hasMany(PersetujuanCuti::class);
    }

    // ── Status helpers ──────────────────────────────────────

    public function isMenungguVerifikasiAdmin(): bool { return $this->status === 'menunggu_verifikasi_admin'; }
    public function isMenungguAtasan(): bool          { return $this->status === 'menunggu_persetujuan_atasan'; }
    public function isMenungguKetua(): bool           { return $this->status === 'menunggu_persetujuan_ketua'; }
    public function isDisetujui(): bool               { return $this->status === 'disetujui'; }
    public function isDitolak(): bool                 { return $this->status === 'ditolak'; }
    public function isDibatalkan(): bool              { return $this->status === 'dibatalkan'; }

    /** Pegawai bisa batalkan selama belum diproses atasan */
    public function bisaDibatalkan(): bool
    {
        return in_array($this->status, ['menunggu_verifikasi_admin', 'menunggu_persetujuan_atasan'], true);
    }

    /** Admin bisa proses verifikasi */
    public function bisaDiprosesAdmin(): bool
    {
        return $this->status === 'menunggu_verifikasi_admin';
    }

    /** Atasan langsung bisa proses */
    public function bisaDiprosesAtasan(): bool
    {
        return $this->status === 'menunggu_persetujuan_atasan';
    }

    /** Ketua bisa proses */
    public function bisaDiprosesKetua(): bool
    {
        return $this->status === 'menunggu_persetujuan_ketua';
    }

    // ── Label & warna badge status ───────────────────────────

    public function statusLabel(): string
    {
        return match($this->status) {
            'menunggu_verifikasi_admin'  => 'Menunggu Verifikasi Admin',
            'menunggu_persetujuan_atasan' => 'Menunggu Persetujuan Atasan',
            'menunggu_persetujuan_ketua'  => 'Menunggu Persetujuan Ketua',
            'disetujui'                  => 'Disetujui',
            'ditolak'                    => 'Ditolak',
            'dibatalkan'                 => 'Dibatalkan',
            default                      => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'menunggu_verifikasi_admin'   => 'bg-purple-100 text-purple-700',
            'menunggu_persetujuan_atasan' => 'bg-yellow-100 text-yellow-700',
            'menunggu_persetujuan_ketua'  => 'bg-orange-100 text-orange-700',
            'disetujui'                   => 'bg-green-100 text-green-700',
            'ditolak'                     => 'bg-red-100 text-red-700',
            'dibatalkan'                  => 'bg-gray-100 text-gray-500',
            default                       => 'bg-gray-100 text-gray-500',
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
