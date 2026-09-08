<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Module 3: Attendance, Shifts, Schedules & Geofenced Tracking.
     */
    public function up(): void
    {
        // 1. Shifts & Rotational Schedules
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

        // 2. Attendance & Geofenced Logs
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->date('date');
            
            // Punch In/Out
            $table->timestamp('clock_in')->nullable();
            $table->timestamp('clock_out')->nullable();
            $table->decimal('total_work_hours', 5, 2)->default(0.00);
            
            // Status & Flags
            $table->enum('status', ['on_time', 'late', 'half_day', 'absent', 'on_leave'])->default('on_time');
            $table->boolean('is_late')->default(false);
            $table->integer('late_minutes')->default(0);
            $table->decimal('overtime_hours', 4, 2)->default(0.00);

            // Geofence & Verification (REQ-ATT-01, TC-ATT-01, TC-ATT-02)
            $table->decimal('clock_in_latitude', 10, 7)->nullable();
            $table->decimal('clock_in_longitude', 10, 7)->nullable();
            $table->decimal('clock_out_latitude', 10, 7)->nullable();
            $table->decimal('clock_out_longitude', 10, 7)->nullable();
            $table->string('clock_in_ip')->nullable();
            $table->string('clock_out_ip')->nullable();
            $table->boolean('is_within_geofence')->default(true);

            $table->text('remarks')->nullable();
            $table->timestamps();

            // Prevent duplicate records for the same employee on the same date (TC-ATT-04)
            $table->unique(['employee_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('shifts');
    }
};
