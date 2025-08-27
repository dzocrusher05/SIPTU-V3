<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Make jumlah_tahun_kgb nullable to allow empty value on import
        DB::statement('ALTER TABLE `pegawais` MODIFY `jumlah_tahun_kgb` TINYINT UNSIGNED NULL DEFAULT NULL');
    }

    public function down(): void
    {
        // Revert to NOT NULL with default 2 (previous state)
        DB::statement('ALTER TABLE `pegawais` MODIFY `jumlah_tahun_kgb` TINYINT UNSIGNED NOT NULL DEFAULT 2');
    }
};

