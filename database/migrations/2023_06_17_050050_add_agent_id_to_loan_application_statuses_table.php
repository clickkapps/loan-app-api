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
        Schema::table('loan_application_statuses', function (Blueprint $table) {
            $table->foreignId('agent_user_id')->nullable()->default(null);
            $table->text('extra')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_application_statuses', function (Blueprint $table) {
            $table->dropColumn(['agent_user_id', 'extra']);
        });
    }
};
