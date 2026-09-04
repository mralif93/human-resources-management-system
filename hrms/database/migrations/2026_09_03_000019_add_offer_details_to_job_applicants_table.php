<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Offer Letter Customization Terms per Candidate
     */
    public function up(): void
    {
        Schema::table('job_applicants', function (Blueprint $table) {
            $table->decimal('offered_salary', 10, 2)->nullable()->after('expected_salary');
            $table->date('joining_date')->nullable()->after('offered_salary');
            $table->unsignedSmallInteger('probation_months')->nullable()->after('joining_date');
            $table->unsignedSmallInteger('notice_period_months')->nullable()->after('probation_months');
            $table->decimal('allowances', 10, 2)->default(0.00)->after('notice_period_months');
            $table->text('offer_remarks')->nullable()->after('allowances'); // Custom clause or remote-work hybrid arrangement
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applicants', function (Blueprint $table) {
            $table->dropColumn([
                'offered_salary',
                'joining_date',
                'probation_months',
                'notice_period_months',
                'allowances',
                'offer_remarks',
            ]);
        });
    }
};
