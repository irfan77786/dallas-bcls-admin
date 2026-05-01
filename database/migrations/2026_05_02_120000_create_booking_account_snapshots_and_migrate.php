<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Store B2B account snapshot per booking in a dedicated table (normalized off `bookings`).
     */
    public function up(): void
    {
        Schema::create('booking_account_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->unsignedBigInteger('account_id')->nullable()->index();
            $table->string('account_company_number', 20)->nullable();
            $table->string('account_company_name')->nullable();
            $table->string('account_company_email')->nullable();
            $table->string('account_company_phone', 30)->nullable();
            $table->text('account_company_address')->nullable();
            $table->string('account_billing_name')->nullable();
            $table->string('account_billing_email')->nullable();
            $table->string('account_billing_phone', 30)->nullable();
            $table->timestamps();

            $table->unique('booking_id');
        });

        if (! Schema::hasTable('bookings') || ! Schema::hasColumn('bookings', 'account_id')) {
            return;
        }

        $now = now();

        DB::table('bookings')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($now) {
                foreach ($rows as $row) {
                    $r = (array) $row;

                    if (
                        empty($r['account_id'])
                        && empty($r['account_company_number'])
                        && empty($r['account_company_name'])
                        && empty($r['account_company_email'])
                        && empty($r['account_company_phone'])
                        && empty($r['account_company_address'])
                        && empty($r['account_billing_name'])
                        && empty($r['account_billing_email'])
                        && empty($r['account_billing_phone'])
                    ) {
                        continue;
                    }

                    DB::table('booking_account_snapshots')->insert([
                        'booking_id' => $r['id'],
                        'account_id' => $r['account_id'] ?? null,
                        'account_company_number' => $r['account_company_number'] ?? null,
                        'account_company_name' => $r['account_company_name'] ?? null,
                        'account_company_email' => $r['account_company_email'] ?? null,
                        'account_company_phone' => $r['account_company_phone'] ?? null,
                        'account_company_address' => $r['account_company_address'] ?? null,
                        'account_billing_name' => $r['account_billing_name'] ?? null,
                        'account_billing_email' => $r['account_billing_email'] ?? null,
                        'account_billing_phone' => $r['account_billing_phone'] ?? null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });

        Schema::table('bookings', function (Blueprint $table) {
            $columns = [
                'account_id',
                'account_company_number',
                'account_company_name',
                'account_company_email',
                'account_company_phone',
                'account_company_address',
                'account_billing_name',
                'account_billing_email',
                'account_billing_phone',
            ];
            $toDrop = array_values(array_filter($columns, fn ($c) => Schema::hasColumn('bookings', $c)));
            if ($toDrop !== []) {
                $table->dropColumn($toDrop);
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'account_id')) {
                $table->unsignedBigInteger('account_id')->nullable()->index();
            }
            if (! Schema::hasColumn('bookings', 'account_company_number')) {
                $table->string('account_company_number', 20)->nullable();
            }
            if (! Schema::hasColumn('bookings', 'account_company_name')) {
                $table->string('account_company_name')->nullable();
            }
            if (! Schema::hasColumn('bookings', 'account_company_email')) {
                $table->string('account_company_email')->nullable();
            }
            if (! Schema::hasColumn('bookings', 'account_company_phone')) {
                $table->string('account_company_phone', 30)->nullable();
            }
            if (! Schema::hasColumn('bookings', 'account_company_address')) {
                $table->text('account_company_address')->nullable();
            }
            if (! Schema::hasColumn('bookings', 'account_billing_name')) {
                $table->string('account_billing_name')->nullable();
            }
            if (! Schema::hasColumn('bookings', 'account_billing_email')) {
                $table->string('account_billing_email')->nullable();
            }
            if (! Schema::hasColumn('bookings', 'account_billing_phone')) {
                $table->string('account_billing_phone', 30)->nullable();
            }
        });

        if (Schema::hasTable('booking_account_snapshots')) {
            $snapshots = DB::table('booking_account_snapshots')->get();
            foreach ($snapshots as $snap) {
                $s = (array) $snap;
                DB::table('bookings')->where('id', $s['booking_id'])->update([
                    'account_id' => $s['account_id'],
                    'account_company_number' => $s['account_company_number'],
                    'account_company_name' => $s['account_company_name'],
                    'account_company_email' => $s['account_company_email'],
                    'account_company_phone' => $s['account_company_phone'],
                    'account_company_address' => $s['account_company_address'],
                    'account_billing_name' => $s['account_billing_name'],
                    'account_billing_email' => $s['account_billing_email'],
                    'account_billing_phone' => $s['account_billing_phone'],
                ]);
            }
        }

        Schema::dropIfExists('booking_account_snapshots');
    }
};
