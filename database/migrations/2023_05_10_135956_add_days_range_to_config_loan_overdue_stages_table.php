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
        Schema::table('config_loan_overdue_stages', function (Blueprint $table) {
//          $table->integer('days_after_deadline')->nullable()->default(null)->comment('if number of days is null, then its infinite');
            $table->integer('from_days_after_deadline')->nullable()->default(null)->comment('if number of days is null, its from the day loan was disbursed');
            $table->integer('to_days_after_deadline')->nullable()->default(null)->comment('if number of days is null, then its infinite');
//            $table->dropColumn('days_after_deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('config_loan_overdue_stages', function (Blueprint $table) {
            $table->dropColumn(['from_days_after_deadline', 'to_days_after_deadline']);
//            $table->integer('days_after_deadline')->nullable()->default(null)->comment('if number of days is null, then its infinite');
        });
    }
};
