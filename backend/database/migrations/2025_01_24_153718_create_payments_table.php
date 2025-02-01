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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Primary key as UUID
            $table->string('payment_method')->comment('e.g., Credit Card, Debit Card, etc.');
            $table->timestamp('created_at_tz')->comment('Payment creation time in merchant\'s time zone');
            $table->uuid('user_id')->nullable();
            $table->string('card_owner')->nullable()
                ->comment('When the payment method is credit card this will be the name on the card');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('address_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->ipAddress()->nullable();
            $table->text('user_agent')->nullable();
            $table->string('payment_reference')->unique()
                ->comment('A unique alphanumeric string representing visual internal reference');

            // Timestamps (created_at, updated_at)
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
