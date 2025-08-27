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
        Schema::create('kgb_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawais')->cascadeOnDelete();
            $table->date('tanggal_kgb');
            $table->unsignedTinyInteger('jumlah_tahun');
            $table->date('tanggal_kgb_berikutnya')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->index(['pegawai_id', 'tanggal_kgb']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kgb_updates');
    }
};
