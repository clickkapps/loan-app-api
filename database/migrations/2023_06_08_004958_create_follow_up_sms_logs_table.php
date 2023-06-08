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
        Schema::create('follow_up_sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_application_id');
            $table->text('message')->nullable()->default(null);
            $table->foreignId('agent_user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follow_up_sms_logs');
    }
};
