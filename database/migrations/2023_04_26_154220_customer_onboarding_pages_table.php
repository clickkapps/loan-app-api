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
        Schema::create('config_cusboarding_pages', function (Blueprint $table) {
            $table->id();
            $table->string('page_title')->nullable()->default(null);
            $table->string('page_description')->nullable()->default(null);
            $table->integer('page_position');
            $table->string('key')->nullable()->default(null)->comment('key pages cannot be deleted. The are generated and used by the system');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config_cusboarding_pages');
    }
};
