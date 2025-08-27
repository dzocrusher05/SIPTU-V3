<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItAsset extends Model
{
    protected $fillable = [
        'kode_aset','nama_perangkat','merek_model','serial_number','kondisi','lokasi','penanggung_jawab','keterangan'
    ];
}

