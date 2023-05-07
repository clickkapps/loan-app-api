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
        Schema::create('loan_application_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_application_id')->constrained()->onDelete('cascade');
            $table->string('status')->nullable()->default(null);
            $table->foreignId('user_id')->nullable()->default(null);
            $table->string('created_by')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_application_statuses');
    }
};
