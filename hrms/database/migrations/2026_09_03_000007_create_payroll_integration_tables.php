<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Module 7: Payroll Integration, Sync Tokens & External Feeder Logs.
     */
    public function up(): void
    {
        // 1. Payroll Sync API Bearer Tokens
        Schema::create('payroll_sync_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('name');                      // e.g. "PayFlow MY Production Server"
            $table->string('token', 80)->unique();       // Bearer token (payflow_...)
            $table->timestamp('last_used_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Historical Payroll Export Logs
        Schema::create('payroll_export_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('period', 7);                  // YYYY-MM e.g. "2026-09"
            $table->enum('export_format', ['csv', 'json'])->default('csv');
            $table->unsignedInteger('records_count')->default(0);
            $table->string('file_name');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_export_logs');
        Schema::dropIfExists('payroll_sync_tokens');
    }
};
