<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutingTemplateStage extends Model
{
    protected $table = 'routing_template_stages';

    protected $fillable = [
        'template_id',
        'urutan',
        'label',
        'kategori_approver',
        'jenis_tindakan',
        'perlu_ttd',
    ];

    protected $casts = [
        'perlu_ttd' => 'boolean',
    ];

    // ── Relasi ──────────────────────────────────────────────

    public function template(): BelongsTo
    {
        return $this->belongsTo(RoutingTemplate::class, 'template_id');
    }

    // ── Label helpers ────────────────────────────────────────

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

    public function kategoriApproverLabel(): string
    {
        return RoutingTemplate::kategoriLabel($this->kategori_approver);
    }
}
