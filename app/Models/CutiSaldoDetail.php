<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CutiSaldoDetail extends Model
{
    protected $table = 'cuti_saldo_detail';

    protected $fillable = [
        'cuti_id',
        'saldo_cuti_id',
        'jumlah_digunakan',
    ];

    public function cuti(): BelongsTo
    {
        return $this->belongsTo(Cuti::class);
    }

    public function saldoCuti(): BelongsTo
    {
        return $this->belongsTo(SaldoCuti::class);
    }
}
