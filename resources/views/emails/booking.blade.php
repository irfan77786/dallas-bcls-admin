<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking {{ $isAdmin ? 'Notification' : 'Confirmation' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Abel&display=swap" rel="stylesheet">
</head>

<body style="font-family: 'Abel', 'Helvetica', 'Arial', sans-serif; line-height: 1.6; color: #333333; margin: 0; padding: 0; background-color: #f4f4f4;">
    <div class="container" style="max-width: 600px; margin: 0 auto; padding: 6px; background-color: #ffffff;">
        <div class="header" style="padding: 20px 10px; text-align: center;">
            <img src="https://dallasblackcarslimoservice.com/img/black-car-service-dallas-logo.PNG" alt="Dallas Black Cars Limo Service" width="250" style="max-width: 250px; margin-bottom: 10px; height: auto; display: inline-block; border: 0;">
            <h2 style="margin: 0; font-size: 22px; color: #222;">Booking {{ $isAdmin ? 'Notification' : 'Confirmation' }}</h2>
            <p style="margin: 5px 0 0; font-size: 15px; color: #555;">{{ $isAdmin ? 'New booking received' : 'Your reservation has been confirmed!' }}</p>
        </div>

        <div class="content" style="padding: 10px 4px 20px;">
            {{-- Greeting / Intro --}}
            @if($isAdmin)
            <p style="font-size: 12px; margin: 0 0 10px;"><b>Dear Admin,</b></p>
            <p style="font-size: 12px; margin: 0 0 10px;">A new booking has been received. Please find the details below:</p>
            <div class="admin-note" style="background-color: #fff3cd; border-left: 4px solid #9e7c1e; padding: 12px; margin: 15px 0; font-size: 15px;">
                <strong>Action Required:</strong> Please review and confirm this booking at your earliest convenience.
            </div>
            @elseif($sendToBooker)
            <p style="font-size: 12px; margin: 0 0 10px;"><b>Dear {{ $bookingData['booker_first_name'] . ' ' . $bookingData['booker_last_name'] ?? 'Booker' }},</b></p>
            <p style="font-size: 12px; margin: 0 0 10px;">Thank you for booking on behalf of {{ $bookingData['passenger_name'] ?? 'the passenger' }}. Your booking has been successfully confirmed. Below are the booking details:</p>
            @else
            <p style="font-size: 12px; margin: 0 0 10px;"><b>Dear {{ $bookingData['passenger_name'] ?? 'Valued Customer' }},</b></p>
            <p style="font-size: 12px; margin: 0 0 10px;">Thank u for choosing our service. Your booking has been successfully confirmed. Below are your booking details:</p>
            @endif

            {{-- Booker Information (if not sending directly to booker) --}}
            @if($bookingData['isBookingForOthers'] && !$sendToBooker)
            <div class="booking-details" style="background-color: #f8f9fa; border-radius: 4px; margin: 20px 0; ">
                <h3 style="background: #9e7c1e; margin: -10px -10px 10px -10px; padding: 6px; font-size: 14px; color: #fff; border-radius: 4px 4px 0 0;">Booker Information</h3>
                <table cellpadding="0" cellspacing="0" width="100%" style="font-size: 12px; padding: 6px;">
                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #555; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top;">Booker Name:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top;">{{ $bookingData['booker_first_name'] . ' ' . $bookingData['booker_last_name'] }}</td>
                    </tr>
                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #555; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top;">Booker Email:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top;">{{ $bookingData['booker_email'] }}</td>
                    </tr>
                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #555; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top; padding-bottom: 12px;">Booker Phone:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top; padding-bottom: 12px;">{{ $bookingData['booker_number'] }}</td>
                    </tr>
                </table>
            </div>
            @endif

            {{-- Booking Information --}}
            <div class="booking-details" style="background-color: #f8f9fa; border-radius: 4px; margin: 20px 0;">
                <h3 style="background: #9e7c1e; margin: -10px -10px 10px -10px; padding: 6px; font-size: 14px; color: #fff; border-radius: 4px 4px 0 0;">Booking Information #{{ $bookingData['booking_id'] }}</h3>

                <table cellpadding="0" cellspacing="0" width="100%" style="font-size: 12px; padding: 6px;">
                    {{-- Pickup date/time FIRST --}}
                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #555; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top;">Pickup Date & Time:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top;">
                            @if(isset($bookingData['pickup_date']) && isset($bookingData['pickup_time']))
                            {{ \Carbon\Carbon::parse($bookingData['pickup_date'].' '.$bookingData['pickup_time'])->format('F j, Y \a\t g:i A') }}
                            @else N/A @endif
                        </td>
                    </tr>

                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #555; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top;">Service Type:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top;">
                            @if(!empty($bookingData['hours']))
                            Hourly/As Directed
                            @else
                            To Airport
                            @endif
                        </td>
                    </tr>

                    @if(!empty($bookingData['passenger_name']))
                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #555; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top;">Passenger:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top;">{{ $bookingData['passenger_name'] }}</td>
                    </tr>
                    @endif

                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #555; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top;">Client Ref#:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top;">N/A</td>
                    </tr>

                    @if(!empty($bookingData['phone']))
                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #555; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top;">Phone Number:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top;">{{ $bookingData['phone'] }}</td>
                    </tr>
                    @endif

                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #555; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top;">Passenger Email:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top;">{{ $bookingData['email'] }}</td>
                    </tr>

                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #555; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top;">No. of Pass:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top;">{{ $bookingData['passengers'] ?? '1' }}</td>
                    </tr>

                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #555; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top;">Vehicle Type:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top;">{{ $bookingData['vehicle_type'] ?? 'Standard' }}</td>
                    </tr>

                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #555; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top;">Pickup Location:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top;">{{ $bookingData['pickup_location'] ?? 'N/A' }}</td>
                    </tr>

                    @if($bookingData['dropoff_location'])
                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #555; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top;">Dropoff Location:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top;">{{ $bookingData['dropoff_location'] ?? 'N/A' }}</td>
                    </tr>

                    @elseif($bookingData['hours'])
                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #555; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top;">Hours:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top;">{{ $bookingData['hours'] ?? 'N/A' }}</td>
                    </tr>
                    @endif

                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #555; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top;">Fare (All inclusive):</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top;">${{ number_format($bookingData['total_amount'] ?? 0, 2) }}</td>
                    </tr>

                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #555; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top;">Other charges:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top;">$0.00</td>
                    </tr>

                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #28a745; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top;">Payment/Deposit:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top;">$0.00</td>
                    </tr>

                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: red; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top; padding-bottom: 12px;">Total Due:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top; padding-bottom: 12px;"><b>${{ number_format($bookingData['total_amount'] ?? 0, 2) }}</b></td>
                    </tr>

                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #555; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top;">Notes/Comments:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top;">******  {{ $bookingData['special_instructions'] ? $bookingData['special_instructions'] : 'Information not provided' }}  ******</td>
                    </tr>
                </table>
            </div>

            {{-- Booker Information (if sending directly to booker) --}}
            @if($bookingData['isBookingForOthers'] && $sendToBooker)
            <div class="booking-details" style="background-color: #f8f9fa; border-radius: 4px; margin: 20px 0;">
                <h3 style="background: #9e7c1e; margin: -10px -10px 10px -10px; padding: 6px; font-size: 14px; color: #fff; border-radius: 4px 4px 0 0;">Booker Information</h3>
                <table cellpadding="0" cellspacing="0" width="100%" style="font-size: 12px; padding: 6px;">
                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #555; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top;">Booker Name:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top;">{{ $bookingData['booker_first_name'] . ' ' . $bookingData['booker_last_name'] }}</td>
                    </tr>
                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #555; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top;">Booker Email:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top;">{{ $bookingData['booker_email'] }}</td>
                    </tr>
                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #555; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top; padding-bottom: 12px;">Booker Phone:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top; padding-bottom: 12px;">{{ $bookingData['booker_number'] }}</td>
                    </tr>
                </table>
            </div>
            @endif

            {{-- Trip Routing Information --}}
            @if(!empty($bookingData['pickup_location']) || !empty($bookingData['dropoff_location']) || !empty($bookingData['hours']))
            <div class="booking-details" style="background-color: #f8f9fa; border-radius: 4px; margin: 20px 0;">
                <h3 style="background: #9e7c1e; margin: -10px -10px 10px -10px; padding: 6px; font-size: 14px; color: #fff; border-radius: 4px 4px 0 0;">Trip Routing Information</h3>

                <table cellpadding="0" cellspacing="0" width="100%" style="font-size: 12px; padding: 6px;">
                    @if(!empty($bookingData['pickup_location']))
                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #555; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top;">Pick-up Location:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top;">{{ $bookingData['pickup_location'] }}</td>
                    </tr>
                    @endif

                    @if(!empty($bookingData['hours']))
                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #555; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top;">Stop Location:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top;">STOP AS DIRECTED</td>
                    </tr>
                    @endif

                    @if(!empty($bookingData['dropoff_location']))
                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #555; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top; padding-bottom: 12px;">Drop-off Location:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top; padding-bottom: 12px;">{{ $bookingData['dropoff_location'] }}</td>
                    </tr>
                    @endif
                </table>
            </div>
            @endif

            {{-- Flight Details --}}
            @if($bookingData['flight_details'] && $bookingData['flight_details']['flight_number'] && $bookingData['flight_details']['pickup_flight_details'])
            <div class="booking-details" style="background-color: #f8f9fa; border-radius: 4px; margin: 20px 0;">
                <h3 style="background: #9e7c1e; margin: -10px -10px 10px -10px; padding: 6px; font-size: 14px; color: #fff; border-radius: 4px 4px 0 0;">Flight/Airport Information</h3>
                <table cellpadding="0" cellspacing="0" width="100%" style="font-size: 12px; padding: 6px;">
                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #555; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top;">Flight Number:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top;">{{ $bookingData['flight_details']['flight_number'] }}</td>
                    </tr>
                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #555; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top;">Pickup Flight Details:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top;">{{ $bookingData['flight_details']['pickup_flight_details'] }}</td>
                    </tr>
                    <tr class="detail-row">
                        <td class="detail-label" style="font-weight: bold; color: #555; width: 40%; min-width: 140px; padding: 3px 5px 3px 0; box-sizing: border-box; vertical-align: top; padding-bottom: 12px;">Meet Option:</td>
                        <td class="detail-value" style="width: 60%; color: #333; padding: 3px 0; box-sizing: border-box; word-wrap: break-word; vertical-align: top; padding-bottom: 12px;">{{ $bookingData['flight_details']['meet_option'] ?? 'Not Specified!' }}</td>
                    </tr>
                </table>
            </div>
            @endif

            {{-- Closing --}}
            @if($isAdmin)
            <p style="font-size: 12px; margin: 0 0 10px;">You can view and manage this booking in the admin panel.</p>
            @else
            <p style="font-size: 12px; margin: 0 0 10px;">If you have any questions or need to make changes to your booking, please contact our customer support team.</p>
            <p style="font-size: 12px; margin: 0 0 10px;">Thank you for choosing our service!</p>
            @endif

            <p style="font-size: 12px; margin: 0 0 10px;"><b>Best regards,<br>{{ config('app.name') }} Team</b></p>
        </div>

        <div class="footer" style="text-align: center; padding: 20px 10px; font-size: 13px; color: #777; border-top: 1px solid #e1e1e1;">
            <p style="margin: 0 0 5px;">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p style="margin: 0;">This is an automated message, please do not reply directly to this email.</p>
        </div>
    </div>
</body>

</html>