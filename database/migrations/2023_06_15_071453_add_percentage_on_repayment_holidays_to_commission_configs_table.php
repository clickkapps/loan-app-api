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
        Schema::table('commission_configs', function (Blueprint $table) {
            $table->double('percentage_on_repayment_holidays')->nullable()->default(0.0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commission_configs', function (Blueprint $table) {
            $table->dropColumn('percentage_on_repayment_holidays');
        });
    }
};
