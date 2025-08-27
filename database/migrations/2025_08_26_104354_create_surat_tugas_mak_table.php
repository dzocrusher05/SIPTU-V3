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
        Schema::create('surat_tugas_mak', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_tugas_id')->constrained('surat_tugas')->cascadeOnDelete();
            $table->foreignId('mak_id')->constrained('maks')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['surat_tugas_id','mak_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_tugas_mak');
    }
};
