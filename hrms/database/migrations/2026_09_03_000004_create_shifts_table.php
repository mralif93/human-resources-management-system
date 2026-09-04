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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., Standard Office Shift, Night Rotational Shift
            $table->string('code')->unique(); // STD-01, SFT-NIGHT
            $table->time('start_time')->default('09:00:00');
            $table->time('end_time')->default('18:00:00');
            $table->integer('late_grace_minutes')->default(15); // Grace period (REQ-ATT-02, TC-ATT-03)
            $table->integer('half_day_threshold_minutes')->default(240); // 4 hours
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
