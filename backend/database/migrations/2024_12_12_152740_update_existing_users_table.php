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
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key constraints referencing the `id` column (if any)
            // You can add this step if foreign key constraints exist in other tables:
            // $table->dropForeign(['user_id']); // Example foreign key drop

            // Drop the existing auto-incrementing id column
            $table->dropColumn('id');
        });

        Schema::table('users', function (Blueprint $table) {
            // Add the new UUID column and set it as the primary key
            $table->uuid('id')->primary()->first(); // Add `id` as UUID
        });

        Schema::table('sessions', function (Blueprint $table) {
            // Ensure the sessions table's user_id is a UUID
            $table->dropColumn('user_id');
            $table->uuid('user_id')->nullable()->index();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('role')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_ebilling_enabled')->default(false);
            $table->boolean('is_notifications_enabled')->default(false);
            $table->boolean('is_card_payment_only')->default(false);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->foreignId('user_id')->nullable()->index();
        });

        Schema::table('users', function (Blueprint $table) {
            // Drop the UUID column
            $table->dropColumn('id');

            // Revert back to auto-incrementing id column
            $table->id();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('first_name');
            $table->dropColumn('middle_name');
            $table->dropColumn('last_name');
            $table->dropColumn('phone');
            $table->dropColumn('street');
            $table->dropColumn('city');
            $table->dropColumn('state');
            $table->dropColumn('zip_code');
            $table->dropColumn('role');
            $table->dropColumn('is_enabled');
            $table->dropColumn('is_ebilling_enabled');
            $table->dropColumn('is_notifications_enabled');
            $table->dropColumn('is_card_payment_only');
            $table->dropSoftDeletes(); // Removes 'deleted_at' column
        });
    }
};
