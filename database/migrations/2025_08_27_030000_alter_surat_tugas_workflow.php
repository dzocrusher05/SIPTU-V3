<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // nomor_st and tanggal_st become nullable to support public requests without these fields
        DB::statement('ALTER TABLE `surat_tugas` MODIFY `nomor_st` VARCHAR(150) NULL');
        DB::statement('ALTER TABLE `surat_tugas` MODIFY `tanggal_st` DATE NULL');
        // add simple status column for workflow
        if (!Schema::hasColumn('surat_tugas', 'status')) {
            DB::statement("ALTER TABLE `surat_tugas` ADD `status` VARCHAR(30) NOT NULL DEFAULT 'requested' AFTER `deskripsi_tugas`");
        }
    }

    public function down(): void
    {
        // Revert to NOT NULL with defaults; set empty nomor to temporary value if needed
        DB::statement("UPDATE `surat_tugas` SET `nomor_st` = CONCAT('TEMP-', id) WHERE `nomor_st` IS NULL");
        DB::statement('ALTER TABLE `surat_tugas` MODIFY `nomor_st` VARCHAR(150) NOT NULL');
        DB::statement("UPDATE `surat_tugas` SET `tanggal_st` = COALESCE(`tanggal_mulai`, CURRENT_DATE) WHERE `tanggal_st` IS NULL");
        DB::statement('ALTER TABLE `surat_tugas` MODIFY `tanggal_st` DATE NOT NULL');
        DB::statement('ALTER TABLE `surat_tugas` DROP COLUMN `status`');
    }
};
