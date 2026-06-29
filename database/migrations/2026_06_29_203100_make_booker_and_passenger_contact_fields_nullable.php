<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('booker')) {
            if (Schema::hasColumn('booker', 'email')) {
                DB::statement('ALTER TABLE `booker` MODIFY `email` VARCHAR(255) NULL');
            }
            if (Schema::hasColumn('booker', 'phone_number')) {
                DB::statement('ALTER TABLE `booker` MODIFY `phone_number` VARCHAR(30) NULL');
            }
        }

        if (Schema::hasTable('passengers')) {
            if (Schema::hasColumn('passengers', 'email')) {
                DB::statement('ALTER TABLE `passengers` MODIFY `email` VARCHAR(255) NULL');
            }
            if (Schema::hasColumn('passengers', 'phone_number')) {
                DB::statement('ALTER TABLE `passengers` MODIFY `phone_number` VARCHAR(30) NULL');
            }
        }
    }

    public function down(): void
    {
        //
    }
};
