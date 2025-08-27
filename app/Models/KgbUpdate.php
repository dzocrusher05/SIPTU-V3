<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KgbUpdate extends Model
{
    protected $fillable = [
        'pegawai_id',
        'tanggal_kgb',
        'jumlah_tahun',
        'tanggal_kgb_berikutnya',
        'catatan',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }
}
