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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('default_momo_account_number')->nullable()->default(null);
            $table->string('default_momo_account_name')->nullable()->default(null);
            $table->string('default_momo_network')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['default_momo_account_number', 'default_momo_account_name', 'default_momo_network']);
        });
    }
};
