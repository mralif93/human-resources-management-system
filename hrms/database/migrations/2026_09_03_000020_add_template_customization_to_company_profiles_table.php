<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Offer Letter Template Content Customization
     */
    public function up(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->string('offer_letter_subject')->default('Conditional Letter of Employment Offer')->after('contract_terms');
            $table->text('offer_letter_intro')->nullable()->after('offer_letter_subject');
            $table->text('offer_letter_benefits')->nullable()->after('offer_letter_intro');
            $table->unsignedSmallInteger('offer_validity_days')->default(5)->after('offer_letter_benefits');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'offer_letter_subject',
                'offer_letter_intro',
                'offer_letter_benefits',
                'offer_validity_days',
            ]);
        });
    }
};
