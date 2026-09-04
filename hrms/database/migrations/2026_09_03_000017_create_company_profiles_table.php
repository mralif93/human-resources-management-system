<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Company Profile & Corporate HR Defaults
     */
    public function up(): void
    {
        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('PulseHR Enterprise Solutions Sdn Bhd');
            $table->string('registration_number')->default('202601008899 (152019-W)')->comment('SSM or Corporate Registration');
            $table->string('phone')->default('+60 3-8899 7788');
            $table->string('email')->default('hr@pulsehr.my');
            $table->string('website')->default('https://pulsehr.my');
            $table->text('address')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('currency_symbol')->default('MYR');
            $table->unsignedSmallInteger('default_probation_months')->default(3);
            $table->unsignedSmallInteger('default_notice_period_months')->default(2);
            $table->unsignedSmallInteger('default_annual_leave_days')->default(14);
            $table->string('hr_director_name')->default('Datuk Seri Dr. Ariff Rahman');
            $table->string('hr_director_title')->default('Chief Human Resources Officer');
            $table->text('contract_terms')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_profiles');
    }
};
