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
        Schema::create('sub_departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->boolean('is_active')->default(true);
            $table->string('name');
            $table->uuid('department_id');
            $table->timestamps();

            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_departments');
    }
};
