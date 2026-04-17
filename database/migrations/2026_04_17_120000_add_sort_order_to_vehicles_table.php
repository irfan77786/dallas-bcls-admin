<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vehicles')) {
            return;
        }

        if (! Schema::hasColumn('vehicles', 'sort_order')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->default(0)->after('id');
            });
        }

        $ids = DB::table('vehicles')->orderBy('id')->pluck('id');
        foreach ($ids as $index => $vehicleId) {
            DB::table('vehicles')->where('id', $vehicleId)->update(['sort_order' => $index + 1]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('vehicles')) {
            return;
        }

        if (Schema::hasColumn('vehicles', 'sort_order')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};
