<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bookings')) {
            return;
        }

        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'is_draft')) {
                $table->boolean('is_draft')->default(false)->after('payment_status');
            }
            if (! Schema::hasColumn('bookings', 'draft_user_id')) {
                $table->unsignedBigInteger('draft_user_id')->nullable()->index()->after('is_draft');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('bookings')) {
            return;
        }

        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'draft_user_id')) {
                $table->dropColumn('draft_user_id');
            }
            if (Schema::hasColumn('bookings', 'is_draft')) {
                $table->dropColumn('is_draft');
            }
        });
    }
};
