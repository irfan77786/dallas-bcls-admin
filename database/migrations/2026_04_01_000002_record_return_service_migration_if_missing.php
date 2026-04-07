<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * If 2026_04_01_000001 ran before `migrations.id` was fixed, the row may be missing.
     */
    public function up(): void
    {
        $name = '2026_04_01_000001_fix_return_service_id_auto_increment';
        if (DB::table('migrations')->where('migration', $name)->exists()) {
            return;
        }
        $batch = (int) DB::table('migrations')->max('batch') + 1;
        DB::table('migrations')->insert(['migration' => $name, 'batch' => $batch]);
    }

    public function down(): void
    {
        DB::table('migrations')->where('migration', '2026_04_01_000001_fix_return_service_id_auto_increment')->delete();
    }
};
