<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class HariLibur extends Model
{
    protected $table = 'hari_libur';

    protected $fillable = [
        'tanggal',
        'nama',
        'keterangan',
        'aktif',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'aktif'   => 'boolean',
    ];

    // ── Scope aktif ─────────────────────────────────────────

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }

    // ── Static helper: ambil semua tanggal libur aktif ─────

    /**
     * Kembalikan array tanggal libur aktif dalam format 'Y-m-d'
     * pada rentang tahun tertentu.
     */
    public static function tanggalLiburAktif(int $tahunMulai, int $tahunSelesai): array
    {
        return self::aktif()
            ->whereYear('tanggal', '>=', $tahunMulai)
            ->whereYear('tanggal', '<=', $tahunSelesai)
            ->pluck('tanggal')
            ->map(fn($d) => $d->format('Y-m-d'))
            ->toArray();
    }
}
