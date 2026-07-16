<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('fbos')) {
            Schema::create('fbos', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('airport_code', 8)->nullable()->index();
                $table->string('address_line1')->nullable();
                $table->string('address_line2')->nullable();
                $table->string('city')->nullable();
                $table->string('state', 64)->nullable();
                $table->string('zip', 32)->nullable();
                $table->string('country', 64)->default('United States');
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('fbos') && DB::table('fbos')->count() === 0) {
            $now = now();
            DB::table('fbos')->insert([
                [
                    'name' => 'Signature Flight Support - DAL',
                    'airport_code' => 'DAL',
                    'address_line1' => '8001 Lemmon Ave',
                    'address_line2' => null,
                    'city' => 'Dallas',
                    'state' => 'Texas',
                    'zip' => '75209',
                    'country' => 'United States',
                    'is_active' => true,
                    'sort_order' => 10,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'Atlantic Aviation - DAL',
                    'airport_code' => 'DAL',
                    'address_line1' => '7515 Lemmon Ave',
                    'address_line2' => 'Building J',
                    'city' => 'Dallas',
                    'state' => 'Texas',
                    'zip' => '75209',
                    'country' => 'United States',
                    'is_active' => true,
                    'sort_order' => 20,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'Million Air Dallas - DAL',
                    'airport_code' => 'DAL',
                    'address_line1' => '8007 Herb Kelleher Way',
                    'address_line2' => null,
                    'city' => 'Dallas',
                    'state' => 'Texas',
                    'zip' => '75235',
                    'country' => 'United States',
                    'is_active' => true,
                    'sort_order' => 30,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'Signature Flight Support - DFW',
                    'airport_code' => 'DFW',
                    'address_line1' => '2380 south 20th Ave',
                    'address_line2' => null,
                    'city' => 'Dallas',
                    'state' => 'Texas',
                    'zip' => '75261',
                    'country' => 'United States',
                    'is_active' => true,
                    'sort_order' => 40,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'Atlantic Aviation - DFW',
                    'airport_code' => 'DFW',
                    'address_line1' => '2000 International Pkwy',
                    'address_line2' => null,
                    'city' => 'Dallas',
                    'state' => 'Texas',
                    'zip' => '75261',
                    'country' => 'United States',
                    'is_active' => true,
                    'sort_order' => 50,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'Jet Aviation - DFW',
                    'airport_code' => 'DFW',
                    'address_line1' => '1523 W 20th St',
                    'address_line2' => null,
                    'city' => 'Dallas',
                    'state' => 'Texas',
                    'zip' => '75261',
                    'country' => 'United States',
                    'is_active' => true,
                    'sort_order' => 60,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'Clay Lacy Aviation - DAL',
                    'airport_code' => 'DAL',
                    'address_line1' => '8611 Lemmon Ave',
                    'address_line2' => null,
                    'city' => 'Dallas',
                    'state' => 'Texas',
                    'zip' => '75209',
                    'country' => 'United States',
                    'is_active' => true,
                    'sort_order' => 70,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fbos');
    }
};
