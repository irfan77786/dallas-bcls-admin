<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'trip_status')) {
                $table->string('trip_status', 40)->nullable()->after('payment_status');
            }
            if (! Schema::hasColumn('bookings', 'dropoff_time')) {
                $table->string('dropoff_time', 20)->nullable()->after('pickup_time');
            }
            if (! Schema::hasColumn('bookings', 'spot_time')) {
                $table->string('spot_time', 20)->nullable()->after('dropoff_time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            foreach (['trip_status', 'dropoff_time', 'spot_time'] as $col) {
                if (Schema::hasColumn('bookings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
