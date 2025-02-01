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
        Schema::table('hosted_payments_form_fields', function (Blueprint $table) {
            Schema::rename('blind_payments_form_fields', 'hosted_payments_form_fields');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hosted_payments_form_fields', function (Blueprint $table) {
            Schema::rename('hosted_payments_form_fields', 'blind_payments_form_fields');
        });
    }
};
