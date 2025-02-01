<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('merchants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('subdomain')->unique();
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('zip', 10);
            $table->string('phone', 20);
            $table->string('logo')->nullable();
            $table->string('fax', 20)->nullable();
            $table->string('time_zone');
            $table->boolean('is_enabled')->default(false);
            $table->boolean('is_bulk_notifications_enabled')->default(false);
            $table->boolean('is_payment_service_disabled')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchants');
    }
};
