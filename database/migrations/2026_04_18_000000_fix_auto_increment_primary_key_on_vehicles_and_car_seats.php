<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables were created without AUTO_INCREMENT on `id`, which causes
     * SQLSTATE[HY000]: 1364 Field 'id' doesn't have a default value on insert.
     */
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $this->fixTable('vehicles');
        $this->fixTable('car_seats');
    }

    private function fixTable(string $table): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $idExtra = DB::selectOne(
            'SELECT EXTRA FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?',
            [$table, 'id']
        );

        if ($idExtra && stripos((string) $idExtra->EXTRA, 'auto_increment') !== false) {
            return;
        }

        $hasPk = (int) (DB::selectOne(
            'SELECT COUNT(*) AS c FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND CONSTRAINT_TYPE = ?',
            [$table, 'PRIMARY KEY']
        )->c ?? 0) > 0;

        $maxId = (int) (DB::table($table)->max('id') ?? 0);

        $sql = 'ALTER TABLE `' . str_replace('`', '``', $table) . '` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT';
        if (! $hasPk) {
            $sql .= ', ADD PRIMARY KEY (`id`)';
        }
        DB::statement($sql);

        if ($maxId > 0) {
            DB::statement('ALTER TABLE `' . str_replace('`', '``', $table) . '` AUTO_INCREMENT = ' . ($maxId + 1));
        }
    }

    public function down(): void
    {
        //
    }
};
