<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Module 9: Corporate Company Profile, Geofencing & Template Customization.
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

            // Dynamic Geofence Parameters (REQ-ATT-01)
            $table->decimal('office_latitude', 10, 7)->default(3.1390000);
            $table->decimal('office_longitude', 10, 7)->default(101.6869000);
            $table->unsignedInteger('geofence_radius_meters')->default(100);

            $table->string('logo_path')->nullable();
            $table->string('currency_symbol')->default('MYR');
            $table->unsignedSmallInteger('default_probation_months')->default(3);
            $table->unsignedSmallInteger('default_notice_period_months')->default(2);
            $table->unsignedSmallInteger('default_annual_leave_days')->default(14);
            $table->string('hr_director_name')->default('Datuk Seri Dr. Ariff Rahman');
            $table->string('hr_director_title')->default('Chief Human Resources Officer');
            $table->string('signature_path')->nullable();
            $table->text('contract_terms')->nullable();

            // Offer Letter Template Customization (REQ-ATS-03)
            $table->string('offer_letter_subject')->default('Conditional Letter of Employment Offer');
            $table->text('offer_letter_intro')->nullable();
            $table->text('offer_letter_benefits')->nullable();
            $table->unsignedSmallInteger('offer_validity_days')->default(5);

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
