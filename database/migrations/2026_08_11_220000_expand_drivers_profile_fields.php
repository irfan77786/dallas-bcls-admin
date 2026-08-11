<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            if (! Schema::hasColumn('drivers', 'address')) {
                $table->string('address', 500)->nullable()->after('phone');
            }
            if (! Schema::hasColumn('drivers', 'vehicle_type')) {
                $table->string('vehicle_type')->nullable()->after('picture');
            }
            if (! Schema::hasColumn('drivers', 'car_make')) {
                $table->string('car_make')->nullable()->after('vehicle_type');
            }
            if (! Schema::hasColumn('drivers', 'car_model')) {
                $table->string('car_model')->nullable()->after('car_make');
            }
            if (! Schema::hasColumn('drivers', 'year')) {
                $table->string('year', 10)->nullable()->after('car_model');
            }
            if (! Schema::hasColumn('drivers', 'color')) {
                $table->string('color', 50)->nullable()->after('year');
            }
            if (! Schema::hasColumn('drivers', 'capacity')) {
                $table->unsignedSmallInteger('capacity')->nullable()->after('color');
            }
            if (! Schema::hasColumn('drivers', 'plate_number')) {
                $table->string('plate_number', 50)->nullable()->after('capacity');
            }
            if (! Schema::hasColumn('drivers', 'vin')) {
                $table->string('vin', 64)->nullable()->after('plate_number');
            }
        });

        // Preserve existing data into the new columns.
        if (Schema::hasColumn('drivers', 'vehicle_model') && Schema::hasColumn('drivers', 'car_model')) {
            DB::table('drivers')
                ->whereNotNull('vehicle_model')
                ->where(function ($q) {
                    $q->whereNull('car_model')->orWhere('car_model', '');
                })
                ->update([
                    'car_model' => DB::raw('vehicle_model'),
                ]);
        }

        if (Schema::hasColumn('drivers', 'car_number') && Schema::hasColumn('drivers', 'plate_number')) {
            DB::table('drivers')
                ->whereNotNull('car_number')
                ->where(function ($q) {
                    $q->whereNull('plate_number')->orWhere('plate_number', '');
                })
                ->update([
                    'plate_number' => DB::raw('car_number'),
                ]);
        }

        Schema::table('drivers', function (Blueprint $table) {
            if (Schema::hasColumn('drivers', 'vehicle_model')) {
                $table->dropColumn('vehicle_model');
            }
            if (Schema::hasColumn('drivers', 'car_number')) {
                $table->dropColumn('car_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            if (! Schema::hasColumn('drivers', 'vehicle_model')) {
                $table->string('vehicle_model')->nullable();
            }
            if (! Schema::hasColumn('drivers', 'car_number')) {
                $table->string('car_number', 50)->nullable();
            }
        });

        if (Schema::hasColumn('drivers', 'car_model') && Schema::hasColumn('drivers', 'vehicle_model')) {
            DB::table('drivers')->update(['vehicle_model' => DB::raw('car_model')]);
        }
        if (Schema::hasColumn('drivers', 'plate_number') && Schema::hasColumn('drivers', 'car_number')) {
            DB::table('drivers')->update(['car_number' => DB::raw('plate_number')]);
        }

        Schema::table('drivers', function (Blueprint $table) {
            foreach (['address', 'vehicle_type', 'car_make', 'car_model', 'year', 'color', 'capacity', 'plate_number', 'vin'] as $col) {
                if (Schema::hasColumn('drivers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
