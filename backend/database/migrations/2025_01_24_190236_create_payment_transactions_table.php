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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('payment_department_id')->constrained('payment_departments')->onDelete('cascade'); // Foreign key referencing Payment_Departments
            $table->enum('charge_type', ['bill', 'fee'])->comment('Defines the transaction type as either bill or fee');
            $table->string('payment_method')->comment('The payment method used - credit, debit, e-check');
            $table->string('status')->comment('Represents the status of the transaction');
            $table->timestamp('status_at_tz')->nullable()->comment('Timestamp of the last status change in the merchant’s time zone');
            $table->string('status_code')->nullable()->comment('Response code of the transaction');
            $table->string('status_message')->nullable()->comment('Response message of the transaction');
            $table->decimal('amount', 10, 2)->comment('Amount processed in this transaction');
            $table->string('reference_number')->nullable()->comment('Payment provider reference to the transaction');
            $table->string('batch_id')->nullable()->comment('Specific batch of transactions, received by the Payment provider');
            $table->timestamp('settled_at_tz')->nullable()->comment('Timestamp of the settlement in the merchant’s time zone');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
