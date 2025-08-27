<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pegawai extends Model
{
    protected $fillable = [
        'nip',
        'nama',
        'pangkat_gol',
        'jabatan',
        'tanggal_kgb_terakhir',
        'jumlah_tahun_kgb',
    ];

    public function kgbUpdates(): HasMany
    {
        return $this->hasMany(KgbUpdate::class);
    }

    public function peminjamanBmn(): HasMany
    {
        return $this->hasMany(PeminjamanBmn::class);
    }

    public function suratTugas(): BelongsToMany
    {
        return $this->belongsToMany(SuratTugas::class, 'surat_tugas_pegawai');
    }
}
