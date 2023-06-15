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
        Schema::table('configurations', function (Blueprint $table) {
            $table->boolean('pause_all_interests')->default(false);
            $table->boolean('today_is_holiday')->default(false);
            $table->boolean('show_customer_call_logs')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn(['pause_all_interests', 'today_is_holiday', 'show_customer_call_logs']);
        });
    }
};
