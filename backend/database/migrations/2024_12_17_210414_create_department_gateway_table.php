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
        Schema::create('department_gateway', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('department_id')->constrained('departments');
            $table->foreignUuid('gateway_id')->nullable()->constrained('gateways');
            $table->string('type');// e.g., 'PaymentX', 'PaymentY', 'InvoiceX'
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('external_identifier')->nullable()
                ->comment('Stores tokens or merchant IDs depending on the integration');
            $table->json('additional_data')->nullable(); // Additional properties
            $table->string('custom_url')->nullable(); // Custom URL for InvoiceX gateways
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_gateway');
    }
};
