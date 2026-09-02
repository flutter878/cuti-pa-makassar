<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaldoCuti extends Model
{
    protected $table = 'saldo_cuti';

    protected $fillable = [
        'pegawai_id',
        'tahun',
        'hak_cuti',
        'carry_over',
        'terpakai',
    ];

    // 'sisa' adalah virtual column, tidak perlu di fillable

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    /**
     * Inisialisasi saldo tahun berjalan untuk pegawai baru.
     */
    public static function inisialisasi(int $pegawaiId, int $tahun = null): self
    {
        $tahun ??= now()->year;

        return self::firstOrCreate(
            ['pegawai_id' => $pegawaiId, 'tahun' => $tahun],
            ['hak_cuti' => 12, 'carry_over' => 0, 'terpakai' => 0]
        );
    }
}
