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
        Schema::table('users', function (Blueprint $table) {
            $table->timestampTz('email_verified_at_tz')->nullable()->after('is_enabled');
            $table->timestampTz('ebilling_opt_at_tz')->nullable()->after('is_ebilling_enabled');
            $table->timestamp('profile_updated_at')->nullable();
            $table->timestampTz('only_card_payment_updated_at_tz')->nullable()->after('is_card_payment_only');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'email_verified_at_tz',
                'ebilling_opt_at_tz',
                'profile_updated_at',
                'only_card_payment_updated_at_tz'
            ]);
        });
    }
};
