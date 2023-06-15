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
        Schema::create('commission_configs', function (Blueprint $table) {
            $table->id();
            $table->double('percentage_on_repayment_weekdays')->nullable()->default(0.0);
            $table->double('percentage_on_repayment_weekends')->nullable()->default(0.0);
            $table->double('percentage_on_deferment_weekdays')->nullable()->default(0.0);
            $table->double('percentage_on_deferment_weekends')->nullable()->default(0.0);
            $table->double('percentage_on_deferment_holidays')->nullable()->default(0.0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_configs');
    }
};
