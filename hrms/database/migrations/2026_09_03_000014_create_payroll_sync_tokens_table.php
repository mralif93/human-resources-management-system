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
        Schema::create('payroll_sync_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('name');                      // e.g. "PayFlow MY Production Server"
            $table->string('token', 80)->unique();       // Bearer token (payflow_...)
            $table->timestamp('last_used_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_sync_tokens');
    }
};
