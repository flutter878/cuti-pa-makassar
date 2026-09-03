<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pegawai extends Model
{
    protected $table = 'pegawai';

    protected $fillable = [
        'nip',
        'nama',
        'email',
        'no_telepon',
        'jabatan_id',
        'unit_kerja_id',
        'atasan_langsung_id',
        'status',
    ];

    // ── Relasi ──────────────────────────────────────────────

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class);
    }

    public function atasanLangsung(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'atasan_langsung_id');
    }

    public function bawahan(): HasMany
    {
        return $this->hasMany(Pegawai::class, 'atasan_langsung_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function saldoCuti(): HasMany
    {
        return $this->hasMany(SaldoCuti::class);
    }

    public function cuti(): HasMany
    {
        return $this->hasMany(Cuti::class);
    }

    // ── Helpers jabatan approver ─────────────────────────────

    public function isAtasanLangsung(): bool
    {
        $jabatanAtasan = config('approver.jabatan_atasan', []);
        return in_array($this->jabatan?->nama_jabatan, $jabatanAtasan, true);
    }

    public function isKetua(): bool
    {
        $jabatanKetua = config('approver.jabatan_ketua', []);
        return in_array($this->jabatan?->nama_jabatan, $jabatanKetua, true);
    }
}
