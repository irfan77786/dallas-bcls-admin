<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Shared DB `return_service` may have been created without a PRIMARY KEY on `id`,
     * so AUTO_INCREMENT cannot be applied (MySQL 1075). Inserts then fail with 1364.
     */
    public function up(): void
    {
        if (! Schema::hasTable('return_service')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $idExtra = DB::selectOne(
            'SELECT EXTRA FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            ['return_service', 'id']
        );
        if ($idExtra && stripos((string) $idExtra->EXTRA, 'auto_increment') !== false) {
            return;
        }

        // Legacy table may have invalid timestamp defaults (0000-00-00), blocking ALTER in strict mode.
        try {
            DB::statement('ALTER TABLE `return_service` MODIFY `created_at` TIMESTAMP NULL DEFAULT NULL');
        } catch (\Throwable $e) {
            // ignore if column already compatible
        }
        try {
            DB::statement('ALTER TABLE `return_service` MODIFY `updated_at` TIMESTAMP NULL DEFAULT NULL');
        } catch (\Throwable $e) {
        }

        $pk = DB::select('SHOW KEYS FROM `return_service` WHERE Key_name = ?', ['PRIMARY']);
        if (empty($pk)) {
            DB::statement('ALTER TABLE `return_service` ADD PRIMARY KEY (`id`)');
        }

        DB::statement('ALTER TABLE `return_service` MODIFY COLUMN `id` BIGINT NOT NULL AUTO_INCREMENT');
    }

    public function down(): void
    {
        // Intentionally empty: unsafe to strip PK/AI without the original DDL.
    }
};
