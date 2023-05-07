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
        Schema::create('loan_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('account_number');
            $table->string('account_name');
            $table->string('network_type');
            $table->timestamp('deadline')->nullable()->nullable();
            $table->double('amount_requested')->nullable()->default(null)->comment('excluding processing fees');
            $table->double('amount_disbursed')->nullable()->default(0.0);
            $table->double('fee_charged')->nullable()->default(0.0);
            $table->double('amount_to_pay')->nullable()->default(null);
            $table->foreignId('loan_overdue_stage_id')->nullable()->default(null);
            $table->foreignId('closed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_applications');
    }
};
