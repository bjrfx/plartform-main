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
        Schema::table('blind_payments_form_fields', function (Blueprint $table) {
            $table->string('type')->after('label')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blind_payments_form_fields', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
