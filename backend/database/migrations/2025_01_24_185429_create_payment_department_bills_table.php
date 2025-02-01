<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_department_bills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payment_department_id')->constrained('payment_departments')->onDelete('cascade');
            $table->uuid('sub_department_id')->nullable();
            $table->string('bill_reference')->comment('An identifier for the merchant bill-number or parcel-id');
            $table->decimal('amount', 10, 2)->comment('The bill paid amount');
            $table->json('bill_payload')->nullable()->comment('Stores custom fields specific to the department payment');

            $table->timestamps();

            $table->foreign('sub_department_id')->references('id')->on('sub_departments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_department_bills');
    }
};
