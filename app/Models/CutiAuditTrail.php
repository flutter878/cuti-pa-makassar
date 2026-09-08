<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CutiAuditTrail extends Model
{
    protected $table = 'cuti_audit_trail';

    // Tabel ini tidak pakai updated_at
    public $timestamps = false;

    protected $fillable = [
        'cuti_id',
        'user_id',
        'aktor',
        'aksi',
        'keterangan',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // ── Relasi ──────────────────────────────────────────────

    public function cuti(): BelongsTo
    {
        return $this->belongsTo(Cuti::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Static helper: catat tindakan ───────────────────────

    /**
     * Catat satu baris audit trail.
     *
     * @param  int         $cutiId
     * @param  string      $aktor   Nama/label aktor
     * @param  string      $aksi    MENGAJUKAN|MEMVERIFIKASI|MENENTUKAN_ROUTING|...
     * @param  string|null $keterangan
     * @param  int|null    $userId
     * @param  string|null $ip
     */
    public static function catat(
        int     $cutiId,
        string  $aktor,
        string  $aksi,
        ?string $keterangan = null,
        ?int    $userId = null,
        ?string $ip = null
    ): self {
        return self::create([
            'cuti_id'    => $cutiId,
            'user_id'    => $userId,
            'aktor'      => $aktor,
            'aksi'       => $aksi,
            'keterangan' => $keterangan,
            'ip_address' => $ip,
            'created_at' => now(),
        ]);
    }

    // ── Label aksi ───────────────────────────────────────────

    public function aksiLabel(): string
    {
        return match ($this->aksi) {
            'MENGAJUKAN'          => 'Mengajukan cuti',
            'MEMVERIFIKASI'       => 'Memverifikasi pengajuan',
            'MENENTUKAN_ROUTING'  => 'Menentukan routing approval',
            'MENERUSKAN'          => 'Meneruskan pengajuan',
            'MENYETUJUI'          => 'Menyetujui',
            'MENGETAHUI'          => 'Mengetahui',
            'MEMBERIKAN_PERTIMBANGAN' => 'Memberikan pertimbangan',
            'MENYETUJUI_FINAL'    => 'Menyetujui (Final)',
            'MENOLAK'             => 'Menolak',
            'MENGEMBALIKAN'       => 'Mengembalikan ke pemohon',
            'SUBMIT_ULANG'        => 'Mengajukan ulang setelah perbaikan',
            'MEMBATALKAN'         => 'Membatalkan pengajuan',
            'MEMBUAT_DOKUMEN'     => 'Membuat dokumen final',
            'TOLAK_ADMIN'         => 'Ditolak oleh Admin (verifikasi)',
            default               => $this->aksi,
        };
    }
}
