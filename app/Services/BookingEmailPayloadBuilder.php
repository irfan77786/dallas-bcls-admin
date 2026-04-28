<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\FlightDetail;

class BookingEmailPayloadBuilder
{
    /**
     * Same payload shape as ReservationController::sendBookingEmails (emails + PDF).
     */
    public static function build(Booking $booking): array
    {
        $booking->loadMissing(['vehicle', 'passengers', 'booker', 'breakdown']);
        $passenger = $booking->passengers->first();
        if (! $passenger) {
            throw new \RuntimeException('Booking has no passenger.');
        }

        $fd = FlightDetail::where('passenger_id', $passenger->id)->first();
        $forOthers = (bool) $passenger->is_booking_for_others;
        $booker = $booking->booker;

        $pickupDate = $booking->pickup_date;
        if ($pickupDate instanceof \Carbon\Carbon) {
            $pickupDate = $pickupDate->format('Y-m-d');
        } else {
            $pickupDate = (string) $pickupDate;
        }

        $pickupTime = $booking->pickup_time;
        if ($pickupTime instanceof \Carbon\Carbon) {
            $pickupTime = $pickupTime->format('H:i:s');
        } else {
            $pickupTime = (string) $pickupTime;
        }

        return [
            'booking_id' => $booking->booking_id,
            'isBookingForOthers' => $forOthers,
            'booker_first_name' => $booker?->first_name,
            'booker_last_name' => $booker?->last_name,
            'booker_number' => $booker?->phone_number,
            'booker_email' => $booker?->email,
            'passenger_name' => $passenger->first_name . ' ' . $passenger->last_name,
            'customer_name' => $passenger->first_name . ' ' . $passenger->last_name,
            'email' => $passenger->email,
            'phone' => $passenger->phone_number,
            'pickup_location' => $booking->pickup_location,
            'dropoff_location' => $booking->dropoff_location,
            'stop_locations' => array_values(array_filter((array) ($booking->stop_locations ?? []), fn ($v) => is_string($v) && trim($v) !== '')),
            'hours' => $booking->breakdown?->total_hours,
            'pickup_date' => $pickupDate,
            'pickup_time' => $pickupTime,
            'vehicle_type' => $booking->vehicle?->vehicle_name ?? 'Standard',
            'passengers' => $booking->pax_count ?? 1,
            'total_amount' => $booking->total_price,
            'buffer_amount' => $booking->buffer_amount,
            'payment_status' => $booking->payment_status,
            'special_instructions' => $booking->note,
            'child_seat' => $booking->child_seat_fee ? [
                'type' => $booking->child_seat_type,
                'quantity' => $booking->child_seat_quantity,
                'fee' => $booking->child_seat_fee,
            ] : null,
            'flight_details' => $fd ? [
                'passenger_id' => $fd->passenger_id,
                'pickup_flight_details' => $fd->pickup_flight_details,
                'flight_number' => $fd->flight_number,
                'meet_option' => $fd->meet_option,
                'no_flight_info' => $fd->no_flight_info,
            ] : null,
            'service_option_label' => self::serviceOptionLabel($booking->service_option),
            'luggage_count' => $booking->luggage_count,
            'account' => [
                'company_number' => $booking->account_company_number,
                'company_name' => $booking->account_company_name,
                'company_email' => $booking->account_company_email,
                'company_phone' => $booking->account_company_phone,
                'company_address' => $booking->account_company_address,
                'billing_name' => $booking->account_billing_name,
                'billing_email' => $booking->account_billing_email,
                'billing_phone' => $booking->account_billing_phone,
            ],
        ];
    }

    public static function serviceOptionLabel(?string $option): string
    {
        return match ($option) {
            'from_airport' => 'From Airport',
            'to_airport' => 'To Airport',
            'point_to_point' => 'Point to point',
            'hourly_as_directed' => 'Hourly / as directed',
            default => '',
        };
    }
}
