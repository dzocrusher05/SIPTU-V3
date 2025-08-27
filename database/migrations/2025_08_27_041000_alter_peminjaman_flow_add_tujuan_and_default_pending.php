<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add lokasi_tujuan and change default status to 'pending'
        DB::statement("ALTER TABLE `peminjaman_bmns` ADD `lokasi_tujuan` VARCHAR(100) NULL AFTER `keperluan`");
        DB::statement("ALTER TABLE `peminjaman_bmns` MODIFY `status` VARCHAR(50) NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `peminjaman_bmns` DROP COLUMN `lokasi_tujuan`");
        DB::statement("ALTER TABLE `peminjaman_bmns` MODIFY `status` VARCHAR(50) NOT NULL DEFAULT 'dipinjam'");
    }
};

