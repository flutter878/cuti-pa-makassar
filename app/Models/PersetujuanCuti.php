<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersetujuanCuti extends Model
{
    protected $table = 'persetujuan_cuti';

    protected $fillable = [
        'cuti_id',
        'user_id',
        'status',
        'catatan',
        'tanggal_persetujuan',
    ];

    protected $casts = [
        'tanggal_persetujuan' => 'datetime',
    ];

    public function cuti(): BelongsTo
    {
        return $this->belongsTo(Cuti::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
