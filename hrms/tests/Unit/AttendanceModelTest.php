<?php

namespace Tests\Unit;

use App\Models\Attendance;
use PHPUnit\Framework\TestCase;

class AttendanceModelTest extends TestCase
{
    /**
     * Test Haversine distance within geofence perimeter (TC-ATT-01).
     */
    public function test_coordinates_within_geofence_perimeter(): void
    {
        // Office HQ is at 3.1390, 101.6869
        // 3.1391, 101.6870 is roughly 15-20 meters away
        $withinLat = 3.1391;
        $withinLng = 101.6870;

        $distance = Attendance::calculateDistance($withinLat, $withinLng);
        $this->assertLessThan(100, $distance);
        $this->assertTrue(Attendance::isWithinGeofence($withinLat, $withinLng));
    }

    /**
     * Test Geofence restriction failure beyond 100m (TC-ATT-02).
     */
    public function test_coordinates_outside_geofence_perimeter(): void
    {
        // Cyberjaya / 5km+ away (e.g. 2.9213, 101.6559)
        $outsideLat = 2.9213;
        $outsideLng = 101.6559;

        $distance = Attendance::calculateDistance($outsideLat, $outsideLng);
        $this->assertGreaterThan(1000, $distance);
        $this->assertFalse(Attendance::isWithinGeofence($outsideLat, $outsideLng));
    }
}
