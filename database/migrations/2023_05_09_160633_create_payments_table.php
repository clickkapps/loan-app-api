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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->default(null);
            $table->foreignId('loan_application_id')->nullable()->default(null);
            $table->string('client_ref')->nullable()->default(null);
            $table->string('server_ref')->nullable()->default(null);
            $table->decimal('amount')->nullable()->default(null);
            $table->string('account_number')->nullable()->default(null);
            $table->string('account_name')->nullable()->default(null);
            $table->string('network_type')->nullable()->default(null);
            $table->string('title')->nullable()->default(null);
            $table->string('description')->nullable()->default(null);
            $table->string('response_message')->nullable()->default(null);
            $table->string('response_code')->nullable()->default(null);
            $table->string('status')->nullable()->default(null);
            $table->text('extra')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
