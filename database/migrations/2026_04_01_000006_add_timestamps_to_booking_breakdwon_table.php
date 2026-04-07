<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Shared DB table `booking_breakdwon` (legacy spelling) may omit Laravel timestamps.
     */
    public function up(): void
    {
        if (! Schema::hasTable('booking_breakdwon')) {
            return;
        }

        if (! Schema::hasColumn('booking_breakdwon', 'created_at')) {
            Schema::table('booking_breakdwon', function (Blueprint $table) {
                $table->timestamp('created_at')->nullable();
            });
        }
        if (! Schema::hasColumn('booking_breakdwon', 'updated_at')) {
            Schema::table('booking_breakdwon', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('booking_breakdwon')) {
            return;
        }

        Schema::table('booking_breakdwon', function (Blueprint $table) {
            $drop = [];
            foreach (['updated_at', 'created_at'] as $c) {
                if (Schema::hasColumn('booking_breakdwon', $c)) {
                    $drop[] = $c;
                }
            }
            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
