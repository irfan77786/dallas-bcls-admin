<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('reviews')) {
            return;
        }

        Schema::table('reviews', function (Blueprint $table) {
            if (! Schema::hasColumn('reviews', 'email')) {
                $table->string('email')->after('comment');
            }
            if (! Schema::hasColumn('reviews', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
        });
    }
    
    public function down()
    {
        if (! Schema::hasTable('reviews')) {
            return;
        }

        Schema::table('reviews', function (Blueprint $table) {
            $drop = [];
            if (Schema::hasColumn('reviews', 'email')) {
                $drop[] = 'email';
            }
            if (Schema::hasColumn('reviews', 'phone')) {
                $drop[] = 'phone';
            }
            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
    
};
