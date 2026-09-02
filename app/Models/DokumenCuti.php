<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenCuti extends Model
{
    protected $table = 'dokumen_cuti';

    protected $fillable = [
        'cuti_id',
        'nama_file',
        'file_path',
        'tipe_file',
    ];

    public function cuti(): BelongsTo
    {
        return $this->belongsTo(Cuti::class);
    }
}
