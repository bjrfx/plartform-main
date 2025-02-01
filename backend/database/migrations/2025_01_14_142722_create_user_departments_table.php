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
        Schema::create('user_departments', function (Blueprint $table) {
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('department_id')->constrained('departments')->onDelete('cascade');
            $table->timestamps();

            $table->primary(['user_id', 'department_id']); // Composite primary key
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_departments');
    }
};
