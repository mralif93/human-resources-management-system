<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Module 5: Performance Appraisals, OKRs & Evaluation Reviews.
     */
    public function up(): void
    {
        // 1. Appraisal Review Cycles
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

        // 2. Employee Quarterly Objectives & Key Results (OKRs)
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

        // 3. Performance Review Evaluations & Ratings
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appraisal_cycle_id')->constrained('appraisal_cycles')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('employees')->nullOnDelete();
            
            // Evaluation Scores (1.0 to 5.0)
            $table->decimal('self_score', 2, 1)->nullable();
            $table->decimal('manager_score', 2, 1)->nullable();
            $table->enum('final_rating', ['Outstanding', 'Exceeds Expectations', 'Meets Expectations', 'Needs Improvement', 'Unsatisfactory'])->nullable();
            
            // Qualitative Feedback
            $table->text('self_remarks')->nullable();
            $table->text('manager_feedback')->nullable();
            $table->text('key_achievements')->nullable();
            $table->text('areas_for_improvement')->nullable();

            // Status Workflow
            $table->enum('status', ['draft', 'submitted', 'reviewed', 'acknowledged'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['appraisal_cycle_id', 'employee_id'], 'cycle_emp_review_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
        Schema::dropIfExists('employee_okrs');
        Schema::dropIfExists('appraisal_cycles');
    }
};
