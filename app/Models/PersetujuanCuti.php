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
        'level',
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

    public function levelLabel(): string
    {
        return match($this->level) {
            'atasan' => 'Atasan Langsung',
            'ketua'  => 'Ketua',
            default  => ucfirst($this->level),
        };
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'disetujui' => 'Disetujui',
            'ditolak'   => 'Ditolak',
            default     => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'disetujui' => 'text-green-600',
            'ditolak'   => 'text-red-600',
            default     => 'text-gray-500',
        };
    }
}
