<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bmn extends Model
{
    protected $fillable = [
        'kode_barang',
        'nup',
        'nama_barang',
        'merek_barang',
    ];

    public function peminjaman(): HasMany
    {
        return $this->hasMany(PeminjamanBmn::class);
    }
}
