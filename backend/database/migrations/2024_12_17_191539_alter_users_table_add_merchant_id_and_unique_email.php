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
            // Add the nullable foreign key column
            $table->foreignUuid('merchant_id')
                ->nullable()
                ->constrained('merchants');

            // Drop the old unique constraint on email
            $table->dropUnique('users_email_unique');

            // Add a new unique constraint on the combination of email and merchant_id
            $table->unique(['email', 'merchant_id'], 'users_email_merchant_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the combined unique constraint
            $table->dropUnique('users_email_merchant_id_unique');

            // Restore the old unique constraint on email
            $table->unique('email');

            // Drop the foreign key and column
            $table->dropForeign(['merchant_id']);
            $table->dropColumn('merchant_id');
        });
    }
};
