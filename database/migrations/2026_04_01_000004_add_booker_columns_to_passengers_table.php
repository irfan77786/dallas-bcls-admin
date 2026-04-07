<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Shared DB may omit booker fields on `passengers` used when booking for someone else.
     */
    public function up(): void
    {
        if (! Schema::hasTable('passengers')) {
            return;
        }

        if (! Schema::hasColumn('passengers', 'booker_first_name')) {
            Schema::table('passengers', function (Blueprint $table) {
                $table->string('booker_first_name', 255)->nullable();
            });
        }
        if (! Schema::hasColumn('passengers', 'booker_last_name')) {
            Schema::table('passengers', function (Blueprint $table) {
                $table->string('booker_last_name', 255)->nullable();
            });
        }
        if (! Schema::hasColumn('passengers', 'booker_email')) {
            Schema::table('passengers', function (Blueprint $table) {
                $table->string('booker_email', 255)->nullable();
            });
        }
        if (! Schema::hasColumn('passengers', 'booker_number')) {
            Schema::table('passengers', function (Blueprint $table) {
                $table->string('booker_number', 30)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('passengers')) {
            return;
        }

        $drop = [];
        foreach (['booker_first_name', 'booker_last_name', 'booker_email', 'booker_number'] as $c) {
            if (Schema::hasColumn('passengers', $c)) {
                $drop[] = $c;
            }
        }
        if ($drop === []) {
            return;
        }

        Schema::table('passengers', function (Blueprint $table) use ($drop) {
            $table->dropColumn($drop);
        });
    }
};
