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
            if (! Schema::hasColumn('bookings', 'account_id')) {
                $table->unsignedBigInteger('account_id')->nullable()->after('from_admin_reservation');
                $table->index('account_id');
            }
            if (! Schema::hasColumn('bookings', 'account_company_number')) {
                $table->string('account_company_number', 20)->nullable()->after('account_id');
            }
            if (! Schema::hasColumn('bookings', 'account_company_name')) {
                $table->string('account_company_name')->nullable()->after('account_company_number');
            }
            if (! Schema::hasColumn('bookings', 'account_company_email')) {
                $table->string('account_company_email')->nullable()->after('account_company_name');
            }
            if (! Schema::hasColumn('bookings', 'account_company_phone')) {
                $table->string('account_company_phone', 30)->nullable()->after('account_company_email');
            }
            if (! Schema::hasColumn('bookings', 'account_company_address')) {
                $table->text('account_company_address')->nullable()->after('account_company_phone');
            }
            if (! Schema::hasColumn('bookings', 'account_billing_name')) {
                $table->string('account_billing_name')->nullable()->after('account_company_address');
            }
            if (! Schema::hasColumn('bookings', 'account_billing_email')) {
                $table->string('account_billing_email')->nullable()->after('account_billing_name');
            }
            if (! Schema::hasColumn('bookings', 'account_billing_phone')) {
                $table->string('account_billing_phone', 30)->nullable()->after('account_billing_email');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('bookings')) {
            return;
        }

        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'account_billing_phone')) {
                $table->dropColumn('account_billing_phone');
            }
            if (Schema::hasColumn('bookings', 'account_billing_email')) {
                $table->dropColumn('account_billing_email');
            }
            if (Schema::hasColumn('bookings', 'account_billing_name')) {
                $table->dropColumn('account_billing_name');
            }
            if (Schema::hasColumn('bookings', 'account_company_address')) {
                $table->dropColumn('account_company_address');
            }
            if (Schema::hasColumn('bookings', 'account_company_phone')) {
                $table->dropColumn('account_company_phone');
            }
            if (Schema::hasColumn('bookings', 'account_company_email')) {
                $table->dropColumn('account_company_email');
            }
            if (Schema::hasColumn('bookings', 'account_company_name')) {
                $table->dropColumn('account_company_name');
            }
            if (Schema::hasColumn('bookings', 'account_company_number')) {
                $table->dropColumn('account_company_number');
            }
            if (Schema::hasColumn('bookings', 'account_id')) {
                $table->dropIndex(['account_id']);
                $table->dropColumn('account_id');
            }
        });
    }
};
