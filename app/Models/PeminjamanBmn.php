<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeminjamanBmn extends Model
{
    protected $fillable = [
        'bmn_id',
        'pegawai_id',
        'tanggal_pinjam',
        'tanggal_kembali',
        'status',
        'keperluan',
        'lokasi_tujuan',
        'keterangan',
    ];

    public function bmn(): BelongsTo
    {
        return $this->belongsTo(Bmn::class);
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }
}
