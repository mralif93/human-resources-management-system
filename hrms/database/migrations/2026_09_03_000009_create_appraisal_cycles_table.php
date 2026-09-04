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
        Schema::create('appraisal_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('name');                      // e.g. '2026 Q1 Review Cycle'
            $table->enum('cycle_type', ['quarterly', 'semi_annual', 'annual', 'probation'])->default('quarterly');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('due_date');
            $table->enum('status', ['upcoming', 'active', 'closed'])->default('active');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appraisal_cycles');
    }
};
