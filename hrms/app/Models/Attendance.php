<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    // Office HQ Coordinates (Kuala Lumpur: 3.1390, 101.6869)
    public const OFFICE_LATITUDE = 3.1390;
    public const OFFICE_LONGITUDE = 101.6869;
    public const GEOFENCE_RADIUS_METERS = 100;

    protected $fillable = [
        'employee_id',
        'shift_id',
        'date',
        'clock_in',
        'clock_out',
        'total_work_hours',
        'status',
        'is_late',
        'late_minutes',
        'overtime_hours',
        'clock_in_latitude',
        'clock_in_longitude',
        'clock_out_latitude',
        'clock_out_longitude',
        'clock_in_ip',
        'clock_out_ip',
        'is_within_geofence',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'clock_in' => 'datetime',
            'clock_out' => 'datetime',
            'is_late' => 'boolean',
            'is_within_geofence' => 'boolean',
            'total_work_hours' => 'decimal:2',
            'overtime_hours' => 'decimal:2',
            'late_minutes' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Calculate Distance in meters using Haversine formula (TC-ATT-01, TC-ATT-02).
     */
    public static function calculateDistance(float $lat1, float $lon1, ?float $lat2 = null, ?float $lon2 = null): float
    {
        $defaultLat = self::OFFICE_LATITUDE;
        $defaultLon = self::OFFICE_LONGITUDE;

        try {
            if (class_exists(CompanyProfile::class)) {
                $profile = CompanyProfile::current();
                $defaultLat = $profile->office_latitude ?? self::OFFICE_LATITUDE;
                $defaultLon = $profile->office_longitude ?? self::OFFICE_LONGITUDE;
            }
        } catch (\Throwable $e) {
            // In isolated unit tests without booted database connection
            $defaultLat = self::OFFICE_LATITUDE;
            $defaultLon = self::OFFICE_LONGITUDE;
        }

        $targetLat = $lat2 ?? $defaultLat;
        $targetLon = $lon2 ?? $defaultLon;

        $earthRadius = 6371000; // in meters

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($targetLat);
        $lonTo = deg2rad($targetLon);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    /**
     * Check if coordinates fall within the designated office perimeter.
     */
    public static function isWithinGeofence(float $latitude, float $longitude, ?float $maxRadiusMeters = null): bool
    {
        $allowedRadius = $maxRadiusMeters;
        if ($allowedRadius === null) {
            try {
                $allowedRadius = CompanyProfile::current()->geofence_radius_meters ?? self::GEOFENCE_RADIUS_METERS;
            } catch (\Throwable $e) {
                $allowedRadius = self::GEOFENCE_RADIUS_METERS;
            }
        }

        return self::calculateDistance($latitude, $longitude) <= $allowedRadius;
    }
}
