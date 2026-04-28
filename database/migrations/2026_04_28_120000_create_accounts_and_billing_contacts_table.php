<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     * Normalized: accounts (company) + billing_contacts (1:1 via unique account_id).
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('company_number', 20)->nullable()->unique();
            $table->string('company_name');
            $table->string('email');
            $table->text('address');
            /** US E.164-style storage: 10 digits only */
            $table->string('phone', 20);
            $table->timestamps();
        });

        Schema::create('billing_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 20);
            $table->timestamps();
            $table->unique('account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_contacts');
        Schema::dropIfExists('accounts');
    }
};
