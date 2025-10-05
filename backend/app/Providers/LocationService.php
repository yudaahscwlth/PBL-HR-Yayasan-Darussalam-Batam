<?php

namespace App\Services;

class LocationService
{
    /**
     * Calculate distance between two coordinates using Haversine formula
     * 
     * @param float $lat1
     * @param float $lon1  
     * @param float $lat2
     * @param float $lon2
     * @return float Distance in meters
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Earth's radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) + 
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }

    /**
     * Check if coordinates are within radius
     * 
     * @param float $userLat
     * @param float $userLon
     * @param float $officeLat
     * @param float $officeLon
     * @param int $radiusMeters
     * @return bool
     */
    public static function isWithinRadius($userLat, $userLon, $officeLat, $officeLon, $radiusMeters)
    {
        $distance = self::calculateDistance($userLat, $userLon, $officeLat, $officeLon);
        return $distance <= $radiusMeters;
    }
}