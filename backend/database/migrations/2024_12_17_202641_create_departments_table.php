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
        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('merchant_id')
                ->constrained('merchants');
            $table->string('name');
            $table->string('email')->nullable()
                ->comment('for nightly transaction report');
            $table->string('slug')->nullable()
                ->comment('slug is a part of the URL that identifies with that department');
            $table->foreignUuid('icon_id')
                ->nullable()
                ->constrained('icons');
            $table->string('logo')
                ->nullable();
            $table->string('person_name')
                ->nullable();
            $table->boolean('is_enabled')
                ->default(false)
                ->comment('Toggle to enable or disable the department');
            $table->boolean('is_visible')
                ->default(false)
                ->comment('Toggle to show or hide the department from payers');
            $table->boolean('is_public')
                ->default(false)
                ->comment('Toggle if a guest can access the department');
            $table->tinyInteger('display_order')
                ->default(0)
                ->unsigned()
                ->comment('Order number');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
