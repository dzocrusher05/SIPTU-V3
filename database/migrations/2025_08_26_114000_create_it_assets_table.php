<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('it_assets', function (Blueprint $table) {
            $table->id();
            $table->string('kode_aset')->unique();
            $table->string('nama_perangkat');
            $table->string('merek_model')->nullable();
            $table->string('serial_number')->nullable()->index();
            $table->string('kondisi')->default('baik');
            $table->string('lokasi')->nullable();
            $table->string('penanggung_jawab')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('it_assets');
    }
};

