<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pax count, luggage count, and detailed service option for admin reservation v2.
     */
    public function up(): void
    {
        if (! Schema::hasTable('bookings')) {
            return;
        }

        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'pax_count')) {
                $table->unsignedSmallInteger('pax_count')->nullable();
            }
            if (! Schema::hasColumn('bookings', 'luggage_count')) {
                $table->unsignedSmallInteger('luggage_count')->nullable();
            }
            if (! Schema::hasColumn('bookings', 'service_option')) {
                $table->string('service_option', 32)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('bookings')) {
            return;
        }

        Schema::table('bookings', function (Blueprint $table) {
            foreach (['service_option', 'luggage_count', 'pax_count'] as $col) {
                if (Schema::hasColumn('bookings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
