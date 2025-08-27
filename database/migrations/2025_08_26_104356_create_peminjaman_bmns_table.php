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
        Schema::create('peminjaman_bmns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bmn_id')->constrained('bmns')->cascadeOnDelete();
            $table->foreignId('pegawai_id')->nullable()->constrained('pegawais')->nullOnDelete();
            $table->date('tanggal_pinjam');
            $table->date('tanggal_kembali')->nullable();
            $table->string('status')->default('dipinjam');
            $table->text('keperluan')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->index(['bmn_id', 'pegawai_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman_bmns');
    }
};
