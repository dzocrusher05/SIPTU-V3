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
        Schema::create('lpjs', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_lpj');
            $table->date('tanggal_masuk');
            $table->string('kegiatan')->nullable();
            $table->decimal('nilai', 15, 2)->nullable();
            $table->string('status')->default('baru');
            $table->string('file_path')->nullable();
            $table->timestamps();
            $table->index('tanggal_masuk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lpjs');
    }
};
