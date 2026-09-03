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

    public function isDiajukan(): bool        { return $this->status === 'diajukan'; }
    public function isMenungguAtasan(): bool  { return $this->status === 'menunggu_atasan'; }
    public function isMenungguKetua(): bool   { return $this->status === 'menunggu_ketua'; }
    public function isDisetujui(): bool       { return $this->status === 'disetujui'; }
    public function isDitolak(): bool         { return $this->status === 'ditolak'; }
    public function isDibatalkan(): bool      { return $this->status === 'dibatalkan'; }

    /** Apakah pengajuan masih bisa dibatalkan oleh pegawai */
    public function bisaDibatalkan(): bool
    {
        return in_array($this->status, ['diajukan', 'menunggu_atasan'], true);
    }

    /** Apakah atasan langsung bisa mengambil tindakan */
    public function bisaDiprosesAtasan(): bool
    {
        return $this->status === 'menunggu_atasan';
    }

    /** Apakah Ketua bisa mengambil tindakan */
    public function bisaDiprosesKetua(): bool
    {
        return $this->status === 'menunggu_ketua';
    }

    // ── Label & warna badge status ───────────────────────────

    public function statusLabel(): string
    {
        return match($this->status) {
            'diajukan'        => 'Diajukan',
            'menunggu_atasan' => 'Menunggu Atasan',
            'menunggu_ketua'  => 'Menunggu Ketua',
            'disetujui'       => 'Disetujui',
            'ditolak'         => 'Ditolak',
            'dibatalkan'      => 'Dibatalkan',
            default           => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'diajukan'        => 'bg-blue-100 text-blue-700',
            'menunggu_atasan' => 'bg-yellow-100 text-yellow-700',
            'menunggu_ketua'  => 'bg-orange-100 text-orange-700',
            'disetujui'       => 'bg-green-100 text-green-700',
            'ditolak'         => 'bg-red-100 text-red-700',
            'dibatalkan'      => 'bg-gray-100 text-gray-500',
            default           => 'bg-gray-100 text-gray-500',
        };
    }
}
