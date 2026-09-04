<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Dynamic Geofence Parameters on Company Profile
     */
    public function up(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->decimal('office_latitude', 10, 7)->default(3.1390000)->after('address');
            $table->decimal('office_longitude', 10, 7)->default(101.6869000)->after('office_latitude');
            $table->unsignedInteger('geofence_radius_meters')->default(100)->after('office_longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropColumn(['office_latitude', 'office_longitude', 'geofence_radius_meters']);
        });
    }
};
