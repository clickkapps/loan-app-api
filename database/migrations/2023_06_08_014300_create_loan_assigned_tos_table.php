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
        Schema::create('loan_assigned_tos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_application_id');
            $table->foreignId('user_id');
            $table->foreignId('stage_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_assigned_tos');
    }
};
