<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Module 4: Leave, Absences, Accruals & Approvals.
     */
    public function up(): void
    {
        // 1. Leave Policies & Types
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');                      // Annual Leave, Medical Leave, etc.
            $table->string('code')->unique();            // AL, SL, HL, ML, EL, UL
            $table->decimal('days_allowed', 4, 1)->default(14.0);
            $table->boolean('is_paid')->default(true);
            $table->boolean('requires_attachment')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('color')->default('indigo');  // indigo, emerald, rose, amber, sky
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Employee Annual Balances & Accruals
        Schema::create('employee_leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->year('year');
            $table->decimal('entitled_days', 4, 1)->default(0.0);
            $table->decimal('used_days', 4, 1)->default(0.0);
            $table->decimal('pending_days', 4, 1)->default(0.0);
            $table->decimal('remaining_days', 4, 1)->default(0.0);
            $table->timestamps();

            $table->unique(['employee_id', 'leave_type_id', 'year'], 'emp_leave_year_unique');
        });

        // 3. Leave Applications & Workflow Sign-Off
        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->foreignId('approver_id')->nullable()->constrained('employees')->nullOnDelete();
            
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_days', 4, 1);
            $table->text('reason');
            $table->string('attachment_path')->nullable();
            
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_applications');
        Schema::dropIfExists('employee_leave_balances');
        Schema::dropIfExists('leave_types');
    }
};
