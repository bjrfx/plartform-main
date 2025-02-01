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
        Schema::create('payment_departments', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Primary key as UUID
            $table->foreignUuid('payment_id')->constrained('payments')->onDelete('cascade');
            $table->foreignUuid('department_id')->constrained('departments')->onDelete('cascade');
            $table->decimal('total_paid_amount', 10, 2)->comment('The total bill + fee amount');
            $table->decimal('total_bill_amount', 10, 2)->comment('The total bill amount');
            $table->decimal('total_fee_amount', 10, 2)->default(0)->comment('The total fee amount');
            $table->decimal('base_fee_amount', 10, 2)->default(0)->comment('Copied from department gateway');
            $table->decimal('base_fee_percentage', 10, 2)->default(0)->comment('Copied from department gateway');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_departments');
    }
};
