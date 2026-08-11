<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip status update</title>
    <link href="https://fonts.googleapis.com/css2?family=Abel&display=swap" rel="stylesheet">
</head>
<body style="font-family: 'Abel', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; padding: 6px; background: #fff;">
        <div style="padding: 20px 10px; text-align: center;">
            <img src="{{ config('booking.reservation_logo_url') }}" alt="Dallas Black Cars Limo Service" width="250" style="max-width: 250px; margin-bottom: 10px; height: auto; border: 0;">
            <h2 style="margin: 0; font-size: 22px; color: #222;">Trip status updated</h2>
            <p style="margin: 5px 0 0; font-size: 15px; color: #555;">Reservation #{{ $bookingData['booking_id'] ?? '' }}</p>
        </div>

        <div style="padding: 10px 16px 24px;">
            @if(!empty($isAdmin))
                <p style="font-size: 14px; margin: 0 0 14px;">Trip status was changed on dispatch for this reservation.</p>
            @else
                <p style="font-size: 14px; margin: 0 0 14px;">
                    Dear {{ $bookingData['passenger_name'] ?? 'Valued Customer' }},
                </p>
                <p style="font-size: 14px; margin: 0 0 14px;">
                    The status of your trip has been updated. Details are below.
                </p>
            @endif

            <div style="background: #f8f9fa; border-radius: 4px; margin: 0 0 18px;">
                <h3 style="background: #9e7c1e; margin: 0; padding: 8px 10px; font-size: 14px; color: #fff; border-radius: 4px 4px 0 0;">Status</h3>
                <table cellpadding="0" cellspacing="0" width="100%" style="font-size: 13px;">
                    <tr>
                        <td style="font-weight: bold; color: #555; width: 40%; padding: 10px;">Previous</td>
                        <td style="padding: 10px; color: #333;">{{ $bookingData['previous_trip_status_label'] ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; color: #555; padding: 10px 10px 14px;">New status</td>
                        <td style="padding: 10px 10px 14px; color: #111; font-size: 16px; font-weight: 700;">
                            {{ $bookingData['trip_status_label'] ?? 'Updated' }}
                        </td>
                    </tr>
                </table>
            </div>

            @include('emails.partials.assigned-driver-card', ['driver' => $bookingData['driver'] ?? null])

            <div style="background: #f8f9fa; border-radius: 4px; margin: 0 0 18px;">
                <h3 style="background: #9e7c1e; margin: 0; padding: 8px 10px; font-size: 14px; color: #fff; border-radius: 4px 4px 0 0;">Trip details</h3>
                <table cellpadding="0" cellspacing="0" width="100%" style="font-size: 12px;">
                    <tr>
                        <td style="font-weight: bold; color: #555; width: 40%; padding: 6px 10px;">Pickup date &amp; time</td>
                        <td style="padding: 6px 10px; color: #333;">
                            @if(!empty($bookingData['pickup_date']) && !empty($bookingData['pickup_time']))
                                {{ \Carbon\Carbon::parse($bookingData['pickup_date'].' '.$bookingData['pickup_time'])->format('F j, Y \a\t g:i A') }}
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; color: #555; padding: 6px 10px;">Service</td>
                        <td style="padding: 6px 10px;">{{ $bookingData['service_option_label'] ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; color: #555; padding: 6px 10px;">Passenger</td>
                        <td style="padding: 6px 10px;">{{ $bookingData['passenger_name'] ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; color: #555; padding: 6px 10px;">Passenger phone</td>
                        <td style="padding: 6px 10px;">{{ $bookingData['phone'] ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; color: #555; padding: 6px 10px;">Passenger email</td>
                        <td style="padding: 6px 10px;">{{ $bookingData['email'] ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; color: #555; padding: 6px 10px;">Vehicle</td>
                        <td style="padding: 6px 10px;">{{ $bookingData['vehicle_type'] ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; color: #555; padding: 6px 10px; vertical-align: top;">Pickup</td>
                        <td style="padding: 6px 10px;">{{ $bookingData['pickup_location'] ?? '—' }}</td>
                    </tr>
                    @foreach(($bookingData['stop_locations'] ?? []) as $i => $stop)
                    <tr>
                        <td style="font-weight: bold; color: #555; padding: 6px 10px;">Stop {{ $i + 1 }}</td>
                        <td style="padding: 6px 10px;">{{ $stop }}</td>
                    </tr>
                    @endforeach
                    @if(!empty($bookingData['dropoff_location']))
                    <tr>
                        <td style="font-weight: bold; color: #555; padding: 6px 10px; vertical-align: top;">Drop-off</td>
                        <td style="padding: 6px 10px;">{{ $bookingData['dropoff_location'] }}</td>
                    </tr>
                    @endif
                    @if(!empty($bookingData['hours']))
                    <tr>
                        <td style="font-weight: bold; color: #555; padding: 6px 10px;">Hours</td>
                        <td style="padding: 6px 10px;">{{ $bookingData['hours'] }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="font-weight: bold; color: #555; padding: 6px 10px;">Passengers</td>
                        <td style="padding: 6px 10px;">{{ $bookingData['passengers'] ?? 1 }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; color: #555; padding: 6px 10px;">Luggage</td>
                        <td style="padding: 6px 10px;">{{ $bookingData['luggage_count'] ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; color: #555; padding: 6px 10px;">Total</td>
                        <td style="padding: 6px 10px;">${{ number_format((float) ($bookingData['total_amount'] ?? 0), 2) }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; color: #555; padding: 6px 10px;">Payment status</td>
                        <td style="padding: 6px 10px;">{{ $bookingData['payment_status'] ?? '—' }}</td>
                    </tr>
                    @if(!empty($bookingData['special_instructions']))
                    <tr>
                        <td style="font-weight: bold; color: #555; padding: 6px 10px 12px; vertical-align: top;">Notes</td>
                        <td style="padding: 6px 10px 12px;">{{ $bookingData['special_instructions'] }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            @if(!empty($bookingData['flight_details']) && empty($bookingData['flight_details']['no_flight_info']))
            <div style="background: #f8f9fa; border-radius: 4px; margin: 0 0 18px;">
                <h3 style="background: #9e7c1e; margin: 0; padding: 8px 10px; font-size: 14px; color: #fff; border-radius: 4px 4px 0 0;">Flight</h3>
                <table cellpadding="0" cellspacing="0" width="100%" style="font-size: 12px;">
                    <tr>
                        <td style="font-weight: bold; color: #555; width: 40%; padding: 6px 10px;">Flight #</td>
                        <td style="padding: 6px 10px;">{{ $bookingData['flight_details']['flight_number'] ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; color: #555; padding: 6px 10px;">Meet option</td>
                        <td style="padding: 6px 10px;">{{ $bookingData['flight_details']['meet_option'] ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; color: #555; padding: 6px 10px 12px;">Details</td>
                        <td style="padding: 6px 10px 12px;">{{ $bookingData['flight_details']['pickup_flight_details'] ?? '—' }}</td>
                    </tr>
                </table>
            </div>
            @endif

            <p style="font-size: 12px; color: #888; margin: 0;">Dallas Black Cars Limo Service</p>
        </div>
    </div>
</body>
</html>
