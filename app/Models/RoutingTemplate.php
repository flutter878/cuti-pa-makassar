<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoutingTemplate extends Model
{
    protected $table = 'routing_template';

    protected $fillable = [
        'nama',
        'kategori_jabatan',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    // ── Relasi ──────────────────────────────────────────────

    public function stages(): HasMany
    {
        return $this->hasMany(RoutingTemplateStage::class, 'template_id')
                    ->orderBy('urutan');
    }

    // ── Scope ────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    // ── Label kategori jabatan ───────────────────────────────

    public static function kategoriLabel(string $kategori): string
    {
        return match ($kategori) {
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
            default              => ucfirst(str_replace('_', ' ', $kategori)),
        };
    }

    public function kategoriLabelAttr(): string
    {
        return self::kategoriLabel($this->kategori_jabatan);
    }
}
