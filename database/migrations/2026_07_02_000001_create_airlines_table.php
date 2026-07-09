<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('airlines')) {
            return;
        }

        Schema::create('airlines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('iata_code', 3)->nullable()->index();
            $table->string('city')->nullable();
            $table->timestamps();
        });

        if (! Schema::hasTable('airports')) {
            return;
        }

        $rows = DB::table('airports')->orderBy('id')->get();
        if ($rows->isEmpty()) {
            return;
        }

        $now = now();
        $payload = $rows->map(fn ($row) => [
            'name' => $row->name,
            'iata_code' => $row->iata_code,
            'city' => $row->city,
            'created_at' => $row->created_at ?? $now,
            'updated_at' => $row->updated_at ?? $now,
        ])->all();

        DB::table('airlines')->insert($payload);
    }

    public function down(): void
    {
        Schema::dropIfExists('airlines');
    }
};
