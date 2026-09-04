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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_openings');
    }
};
