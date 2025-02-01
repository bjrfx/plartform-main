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
        Schema::create('notification_bodies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('notification_id')->constrained('notifications')->onDelete('cascade');
            $table->longText('body');  // Large email body support
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_bodies');
    }
};
