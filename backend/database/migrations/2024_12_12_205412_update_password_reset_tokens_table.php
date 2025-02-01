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
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            // Add the user_id UUID column as a foreign key
            $table->uuid('user_id')->after('email')->index();
            // Drop the email column
            $table->dropColumn('email');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            // Re-add the email column
            $table->string('email')->after('user_id')->index();
            // Drop the user_id foreign key and column
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
