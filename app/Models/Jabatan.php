<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jabatan extends Model
{
    protected $table = 'jabatan';

    protected $fillable = [
        'nama_jabatan',
        'kategori',
    ];

    // ── Relasi ──────────────────────────────────────────────

    public function pegawai(): HasMany
    {
        return $this->hasMany(Pegawai::class);
    }

    // ── Daftar kategori yang tersedia ────────────────────────

    public static function daftarKategori(): array
    {
        return [
            'staf'               => 'Staf',
            'kasubbag'           => 'Kepala Sub Bagian (Kasubbag)',
            'panitera_muda'      => 'Panitera Muda',
            'pejabat_fungsional' => 'Pejabat Fungsional',
            'panitera_pengganti' => 'Panitera Pengganti',
            'jurusita'           => 'Jurusita',
            'jurusita_pengganti' => 'Jurusita Pengganti',
            'panitera'           => 'Panitera',
            'sekretaris'         => 'Sekretaris',
            'hakim'              => 'Hakim',
            'wakil_ketua'        => 'Wakil Ketua',
            'ketua'              => 'Ketua',
        ];
    }

    // ── Label kategori ───────────────────────────────────────

    public function kategoriLabel(): string
    {
        return self::daftarKategori()[$this->kategori] ?? ucfirst(str_replace('_', ' ', $this->kategori ?? 'staf'));
    }

    // ── Helpers pemeriksaan kategori ─────────────────────────

    public function isKetua(): bool
    {
        return $this->kategori === 'ketua';
    }

    public function isWakilKetua(): bool
    {
        return $this->kategori === 'wakil_ketua';
    }

    public function isHakim(): bool
    {
        return $this->kategori === 'hakim';
    }

    public function isPanitera(): bool
    {
        return $this->kategori === 'panitera';
    }

    public function isSekretaris(): bool
    {
        return $this->kategori === 'sekretaris';
    }

    public function isStaf(): bool
    {
        return $this->kategori === 'staf';
    }

    /**
     * Apakah jabatan ini termasuk level yang perlu melewati lebih banyak tahap
     * (staf, panitera_pengganti, jurusita, jurusita_pengganti)
     */
    public function isLevelStaf(): bool
    {
        return in_array($this->kategori, [
            'staf',
            'panitera_pengganti',
            'jurusita',
            'jurusita_pengganti',
        ], true);
    }

    /**
     * Apakah jabatan ini termasuk level menengah
     * (kasubbag, panitera_muda, pejabat_fungsional)
     */
    public function isLevelMenengah(): bool
    {
        return in_array($this->kategori, [
            'kasubbag',
            'panitera_muda',
            'pejabat_fungsional',
        ], true);
    }

    /**
     * Apakah jabatan ini termasuk level pimpinan
     * (panitera, sekretaris, hakim, wakil_ketua, ketua)
     */
    public function isLevelPimpinan(): bool
    {
        return in_array($this->kategori, [
            'panitera',
            'sekretaris',
            'hakim',
            'wakil_ketua',
            'ketua',
        ], true);
    }
}
