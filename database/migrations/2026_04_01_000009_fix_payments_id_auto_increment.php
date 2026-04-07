<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Shared DB `payments` may lack AUTO_INCREMENT on `id`.
     * Inserts then fail with: SQLSTATE[HY000]: 1364 Field 'id' doesn't have a default value.
     */
    public function up(): void
    {
        if (! Schema::hasTable('payments')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $idExtra = DB::selectOne(
            'SELECT EXTRA FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            ['payments', 'id']
        );
        if ($idExtra && stripos((string) $idExtra->EXTRA, 'auto_increment') !== false) {
            return;
        }

        foreach (['created_at', 'updated_at'] as $col) {
            try {
                DB::statement("ALTER TABLE `payments` MODIFY `{$col}` TIMESTAMP NULL DEFAULT NULL");
            } catch (\Throwable $e) {
                // ignore if column missing or already compatible
            }
        }

        $pk = DB::select('SHOW KEYS FROM `payments` WHERE Key_name = ?', ['PRIMARY']);
        if (empty($pk)) {
            DB::statement('ALTER TABLE `payments` ADD PRIMARY KEY (`id`)');
        }

        $typeRow = DB::selectOne(
            'SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            ['payments', 'id']
        );
        $colType = $typeRow && $typeRow->COLUMN_TYPE ? (string) $typeRow->COLUMN_TYPE : 'bigint(20) unsigned';

        DB::statement("ALTER TABLE `payments` MODIFY COLUMN `id` {$colType} NOT NULL AUTO_INCREMENT");
    }

    public function down(): void
    {
        // Intentionally empty: unsafe to strip PK/AI without the original DDL.
    }
};
