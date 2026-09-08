<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalStage extends Model
{
    protected $table = 'approval_stages';

    protected $fillable = [
        'cuti_id',
        'urutan',
        'user_id',
        'pegawai_id',
        'nama_approver',
        'jabatan_approver',
        'label_tahap',
        'jenis_tindakan',
        'perlu_ttd',
        'status',
        'catatan',
        'tanggal_tindakan',
    ];

    protected $casts = [
        'perlu_ttd'        => 'boolean',
        'tanggal_tindakan' => 'datetime',
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

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    // ── Scope ────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeUrutkan($query)
    {
        return $query->orderBy('urutan');
    }

    // ── Helpers ──────────────────────────────────────────────

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isApproved(): bool  { return $this->status === 'approved'; }
    public function isRejected(): bool  { return $this->status === 'rejected'; }
    public function isReturned(): bool  { return $this->status === 'returned'; }
    public function isSkipped(): bool   { return $this->status === 'skipped'; }

    public function isFinalApproval(): bool
    {
        return $this->jenis_tindakan === 'final_approval';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending'  => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'returned' => 'Dikembalikan',
            'skipped'  => 'Dilewati',
            default    => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'pending'  => 'bg-yellow-100 text-yellow-700',
            'approved' => 'bg-green-100 text-green-700',
            'rejected' => 'bg-red-100 text-red-700',
            'returned' => 'bg-orange-100 text-orange-700',
            'skipped'  => 'bg-gray-100 text-gray-500',
            default    => 'bg-gray-100 text-gray-500',
        };
    }

    public function jenisTindakanLabel(): string
    {
        return match ($this->jenis_tindakan) {
            'approval'       => 'Menyetujui',
            'mengetahui'     => 'Mengetahui',
            'pertimbangan'   => 'Memberikan Pertimbangan',
            'final_approval' => 'Menyetujui (Final)',
            default          => ucfirst(str_replace('_', ' ', $this->jenis_tindakan)),
        };
    }
}
