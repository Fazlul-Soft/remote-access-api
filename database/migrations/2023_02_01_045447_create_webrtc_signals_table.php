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
        Schema::create('webrtc_signals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_device_id');
            $table->unsignedBigInteger('target_device_id'); // Who is this signal for?
            $table->string('type'); // 'offer', 'answer', or 'candidate'
            $table->json('data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webrtc_signals');
    }
};
