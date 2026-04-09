<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Child seat add-on for admin reservation (type, quantity, computed fee).
     */
    public function up(): void
    {
        if (! Schema::hasTable('bookings')) {
            return;
        }

        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'child_seat_type')) {
                $table->string('child_seat_type', 32)->nullable();
            }
            if (! Schema::hasColumn('bookings', 'child_seat_quantity')) {
                $table->unsignedSmallInteger('child_seat_quantity')->nullable();
            }
            if (! Schema::hasColumn('bookings', 'child_seat_fee')) {
                $table->decimal('child_seat_fee', 10, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('bookings')) {
            return;
        }

        Schema::table('bookings', function (Blueprint $table) {
            foreach (['child_seat_fee', 'child_seat_quantity', 'child_seat_type'] as $col) {
                if (Schema::hasColumn('bookings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
