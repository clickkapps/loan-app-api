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
        Schema::create('config_cusboarding_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('config_cusboarding_field_id')->constrained();
            $table->string('type')->default('text');
            $table->boolean('required')->default(true);
            $table->string('title')->nullable()->default(null);
            $table->string('placeholder')->nullable()->default(null);
            $table->string('key')->nullable()->default(null)->comment('This field is not exposed to admin users. Used internally');
            $table->timestamps();
            $table->text('extra')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config_cusboarding_fields');
    }
};
