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
        Schema::create('employee_okrs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('quarter', 10)->default('Q1'); // Q1, Q2, Q3, Q4, Annual
            $table->year('year');
            $table->string('title');
            $table->string('key_result_metric');          // e.g. "Deliver 99.9% uptime", "Hire 5 engineers"
            $table->decimal('target_value', 10, 2)->default(100.00);
            $table->decimal('current_value', 10, 2)->default(0.00);
            $table->unsignedTinyInteger('progress_percentage')->default(0); // 0 - 100
            $table->enum('status', ['on_track', 'at_risk', 'behind', 'completed'])->default('on_track');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_okrs');
    }
};
