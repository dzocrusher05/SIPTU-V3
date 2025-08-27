<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bmns', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang')->index();
            $table->string('nup')->index();
            $table->string('nama_barang');
            $table->string('merek_barang')->nullable();
            $table->timestamps();
            $table->unique(['kode_barang', 'nup']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bmns');
    }
};
