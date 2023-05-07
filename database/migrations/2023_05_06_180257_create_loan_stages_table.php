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
        Schema::create('config_loan_overdue_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('desc')->nullable()->default(null);
            $table->integer('days_after_deadline')->nullable()->default(null)->comment('if number of days is null, then its infinite');
            $table->double('interest_percentage_per_day');
            $table->boolean('installment_enabled')->default(false);
            $table->boolean('auto_deduction_enabled')->default(false);
            $table->double('percentage_raise_on_next_loan_request')->default(0.0);
            $table->boolean('eligible_for_next_loan_request')->default(true);
            $table->string('key')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config_loan_overdue_stages');
    }
};
