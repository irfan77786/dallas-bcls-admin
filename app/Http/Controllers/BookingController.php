<?php
// app/Http/Controllers/BookingController.php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['vehicle', 'passengers']) // eager load relationships
            ->latest()
            ->paginate(10); // or use get() if pagination is not needed
 
        return view('pages.bookings.index', compact('bookings'));
    }
public function show($id)
{
    $booking = Booking::with([
        'vehicle',
        'passengers.flightDetail',
        'booker',
        'returnService',
        'breakdown'
    ])->findOrFail($id);

    $breakdown = $booking->breakdown;

    $travelInfo = null;

    if (!$breakdown) {
        // No breakdown data available, fallback or handle error as needed
        return view('pages.bookings.show', compact('booking', 'travelInfo'));
    }

    if (empty($breakdown->total_kms)) {
        // Hourly booking (assuming total_kms empty means hourly)
        $hours = (int) ($breakdown->total_hours ?? 0);
        $fare = (float) ($breakdown->hourly_fare ?? 0);

        $travelInfo = [
            'type' => 'hourly',
            'hours' => $hours,
            'fare' => $fare,
        ];
    } else {
        // Point-to-point booking
        $distance = (float) $breakdown->total_kms;
        $fare = (float) ($breakdown->base_fare ?? 0) + ($distance * (float) ($breakdown->per_km_rate ?? 0));

        $travelInfo = [
            'type' => 'point_to_point',
            'distance' => $distance,
            'fare' => $fare,
        ];
    }

    return view('pages.bookings.show', compact('booking', 'travelInfo'));
}


    
    
    
private function getDistanceBetweenAddresses(string $origin, string $destination): ?float
{
    $apiKey = config('services.google_maps.api_key'); // Make sure you set this in config/services.php and .env

    $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
        'origins' => $origin,
        'destinations' => $destination,
        'key' => 'AIzaSyBtkrakOJ7xvcL2FIF5XbhCV1PIaNKz4zQ',
        'units' => 'metric',
    ]);

    if ($response->ok()) {
        $data = $response->json();

        if (
            isset($data['status'], $data['rows'][0]['elements'][0]['status']) &&
            $data['status'] === 'OK' &&
            $data['rows'][0]['elements'][0]['status'] === 'OK'
        ) {
            $distanceMeters = $data['rows'][0]['elements'][0]['distance']['value'];
            $distanceKm = $distanceMeters / 1000; // Convert meters to kilometers
            return round($distanceKm, 2);
        }
    }

    return null; // Return null if something went wrong
}
}


