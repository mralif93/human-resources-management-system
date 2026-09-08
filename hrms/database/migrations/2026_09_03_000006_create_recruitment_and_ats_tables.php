<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Module 6: Recruitment, Job Openings & Applicant Tracking System (ATS).
     */
    public function up(): void
    {
        // 1. Published Job Openings
        Schema::create('job_openings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained('designations')->nullOnDelete();
            
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('employment_type', ['Full-Time', 'Part-Time', 'Contract', 'Internship'])->default('Full-Time');
            $table->enum('experience_level', ['Junior', 'Mid-Level', 'Senior', 'Lead', 'Executive'])->default('Mid-Level');
            $table->string('location')->default('Kuala Lumpur, Malaysia (Hybrid)');
            $table->unsignedSmallInteger('openings_count')->default(1);
            
            $table->text('description');
            $table->text('requirements')->nullable();
            
            $table->enum('status', ['draft', 'published', 'closed'])->default('published');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        // 2. Job Applicants & Offer Terms (REQ-ATS-01, REQ-ATS-03)
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

            // Offer Letter Terms & Onboarding Parameters
            $table->decimal('offered_salary', 10, 2)->nullable();
            $table->date('joining_date')->nullable();
            $table->unsignedSmallInteger('probation_months')->nullable();
            $table->unsignedSmallInteger('notice_period_months')->nullable();
            $table->decimal('allowances', 10, 2)->default(0.00);
            $table->text('offer_remarks')->nullable();
            
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
        Schema::dropIfExists('job_openings');
    }
};
