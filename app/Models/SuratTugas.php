<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SuratTugas extends Model
{
    protected $fillable = [
        'nomor_st',
        'tanggal_st',
        'tanggal_mulai',
        'tanggal_selesai',
        'lokasi_tugas',
        'deskripsi_tugas',
    ];

    public function pegawai(): BelongsToMany
    {
        return $this->belongsToMany(Pegawai::class, 'surat_tugas_pegawai');
    }

    public function maks(): BelongsToMany
    {
        return $this->belongsToMany(Mak::class, 'surat_tugas_mak');
    }
}
