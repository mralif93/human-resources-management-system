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
    }
};
