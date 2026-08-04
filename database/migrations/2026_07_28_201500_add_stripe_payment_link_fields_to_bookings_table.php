<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'stripe_checkout_session_id')) {
                $table->string('stripe_checkout_session_id')->nullable()->after('stripe_payment_method_id');
            }
            if (! Schema::hasColumn('bookings', 'stripe_payment_link_url')) {
                $table->string('stripe_payment_link_url', 500)->nullable()->after('stripe_checkout_session_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'stripe_payment_link_url')) {
                $table->dropColumn('stripe_payment_link_url');
            }
            if (Schema::hasColumn('bookings', 'stripe_checkout_session_id')) {
                $table->dropColumn('stripe_checkout_session_id');
            }
        });
    }
};
