<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BookingPricingService
{
    /**
     * Match customer site logic (dallas-bcls BookingController::calculateDistanceWithStops).
     * Distances use miles; pricing = base_fare + (miles * per_km_rate) — same as live site.
     */
    public function calculateDistanceWithStops(
        ?string $pickup,
        ?string $dropoff,
        array $stops,
        float $baseFare,
        ?float $hourlyFare,
        float $perKmRate,
        ?int $hours = null
    ): array {
        if ($dropoff === null || $dropoff === '') {
            $price = ($hourlyFare ?? 0) * ($hours ?? 1);

            return [
                'distance_km' => 0,
                'price' => round($price, 2),
                'baseFare' => $baseFare,
                'hourlyFare' => $hourlyFare,
                'hours' => $hours,
                'type' => 'Hourly',
            ];
        }

        $locations = array_values(array_filter([$pickup, ...$stops, $dropoff]));
        $totalDistance = 0.0;
        $apiKey = config('services.google_maps.api_key');

        if (!$apiKey) {
            return ['error' => 'Google Maps API key is not configured. Set GOOGLE_MAPS_API_KEY in .env'];
        }

        for ($i = 0; $i < count($locations) - 1; $i++) {
            $origin = $locations[$i];
            $destination = $locations[$i + 1];

            $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                'origins' => $origin,
                'destinations' => $destination,
                'key' => $apiKey,
            ]);

            $data = $response->json();

            if (($data['status'] ?? '') !== 'OK' || empty($data['rows'][0]['elements'][0]['distance'])) {
                return ['error' => 'Distance calculation failed between ' . $origin . ' and ' . $destination];
            }

            $totalDistance += (float) $data['rows'][0]['elements'][0]['distance']['value'];
        }

        $distanceMiles = $totalDistance / 1609.34;
        $price = $baseFare + ($distanceMiles * $perKmRate);
        $totalPrice = $price + ($hours ? ($hourlyFare ?? 0) * $hours : 0);

        return [
            'distance_km' => round($distanceMiles, 2),
            'price' => round($totalPrice, 2),
            'baseFare' => $baseFare,
            'hourlyFare' => $hourlyFare,
            'perKmRate' => $perKmRate,
            'hours' => $hours,
            'type' => !empty($dropoff) ? 'PointToPoint' : 'Hourly',
        ];
    }
}
