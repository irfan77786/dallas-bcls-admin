<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('reservation_routing_drafts')) {
            return;
        }

        Schema::create('reservation_routing_drafts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->json('routing_information')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reservation_routing_drafts');
    }
};
