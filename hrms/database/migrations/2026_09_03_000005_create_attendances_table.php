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
    }
};
