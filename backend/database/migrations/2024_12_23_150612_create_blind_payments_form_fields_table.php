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
        Schema::create('blind_payments_form_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('department_id')->nullable();  // NULL for default
            $table->uuid('parent_id')->nullable();
            $table->string('label');
            $table->boolean('is_required')->default(false);
            $table->integer('display_order')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('department_id')->references('id')->on('departments');
            $table->foreign('parent_id')->references('id')->on('blind_payments_form_fields')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blind_payments_form_fields');
    }
};
