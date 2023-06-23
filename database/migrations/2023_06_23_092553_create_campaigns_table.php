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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->text('message')->nullable()->default(null);
            $table->boolean('use_sms')->default(true);
            $table->boolean('use_push')->default(false);
            $table->string('status')->nullable()->default('pending');
            $table->integer('total')->nullable()->default(null);
            $table->foreignId('author')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
