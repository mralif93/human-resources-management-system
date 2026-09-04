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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained('designations')->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('employees')->nullOnDelete();

            // Employee Identifiers
            $table->string('employee_code')->unique(); // EMP-YYYY-XXXX (TC-PIM-01)
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            
            // Encrypted PII Fields (REQ-PIM-01, TC-PIM-03)
            $table->text('national_id')->nullable(); // Encrypted NRIC/SSN/Passport
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->default('Male');

            // Employment Lifecycle
            $table->enum('employment_status', ['Permanent', 'Probation', 'Contract', 'Intern', 'Terminated'])->default('Probation');
            $table->date('joining_date');
            $table->date('confirmation_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->string('branch_location')->default('Headquarters (Kuala Lumpur)');

            // Compensation & Bank details (External Payroll Feeder)
            $table->string('bank_name')->nullable();
            $table->text('bank_account_number')->nullable(); // Encrypted at rest
            $table->decimal('basic_salary', 12, 2)->default(0.00);

            $table->softDeletes(); // TC-PIM-02
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
