<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Mak extends Model
{
    protected $fillable = [
        'kode',
        'uraian',
    ];

    public function suratTugas(): BelongsToMany
    {
        return $this->belongsToMany(SuratTugas::class, 'surat_tugas_mak');
    }
}
