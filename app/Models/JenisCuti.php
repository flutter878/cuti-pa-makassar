<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisCuti extends Model
{
    protected $table = 'jenis_cuti';

    protected $fillable = [
        'nama',
        'kode',
        'membutuhkan_lampiran',
        'mengurangi_saldo',
        'batas_hari',
        'status',
    ];

    protected $casts = [
        'membutuhkan_lampiran' => 'boolean',
        'mengurangi_saldo'     => 'boolean',
    ];

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}
