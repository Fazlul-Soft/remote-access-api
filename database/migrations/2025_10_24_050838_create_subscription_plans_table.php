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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., 'Plan A'
            $table->text('description')->nullable();
            $table->integer('max_devices'); // e.g., 2
            $table->decimal('price', 8, 2);
            $table->string('payment_method')->nullable();
            $table->integer('duration')->nullable(); // e.g., 1 means 1 month =30 days, 2 means 2 months=60 days
            $table->integer('grace_period_days')->nullable(); // e.g., 7
            $table->integer('hide_data_after_days')->nullable(); // e.g., 30
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
