<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Employees migration filename was changed so it runs before `reviews` (foreign key order).
     * If the database still records the old migration name, rename it so the new file is not run twice.
     */
    public function up(): void
    {
        if (! Schema::hasTable('migrations')) {
            return;
        }

        DB::table('migrations')
            ->where('migration', '2025_03_07_052735_create_employees_table')
            ->update(['migration' => '2025_03_06_203610_create_employees_table']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('migrations')) {
            return;
        }

        DB::table('migrations')
            ->where('migration', '2025_03_06_203610_create_employees_table')
            ->update(['migration' => '2025_03_07_052735_create_employees_table']);
    }
};
