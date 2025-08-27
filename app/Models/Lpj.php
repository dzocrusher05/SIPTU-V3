<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lpj extends Model
{
    protected $fillable = [
        'nomor_lpj',
        'tanggal_masuk',
        'kegiatan',
        'nilai',
        'status',
        'file_path',
    ];
}
