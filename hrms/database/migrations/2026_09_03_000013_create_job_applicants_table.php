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
        Schema::create('job_applicants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_opening_id')->constrained('job_openings')->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete(); // Populated on one-click conversion (REQ-ATS-03)
            
            // Candidate Profile
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            
            // Experience & Credentials
            $table->string('current_company')->nullable();
            $table->string('current_title')->nullable();
            $table->decimal('experience_years', 3, 1)->default(0.0);
            $table->decimal('expected_salary', 10, 2)->nullable();
            $table->string('resume_path')->nullable();
            
            // ATS Pipeline Stage
            $table->enum('stage', ['applied', 'screened', 'interview', 'offer', 'hired', 'rejected'])->default('applied');
            $table->unsignedTinyInteger('rating')->nullable(); // 1 to 5 stars
            $table->text('interview_notes')->nullable();
            
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applicants');
    }
};
