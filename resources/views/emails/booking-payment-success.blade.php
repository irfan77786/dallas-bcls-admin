<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment received</title>
    <link href="https://fonts.googleapis.com/css2?family=Abel&display=swap" rel="stylesheet">
</head>
<body style="font-family: 'Abel', 'Helvetica', 'Arial', sans-serif; line-height: 1.6; color: #333333; margin: 0; padding: 0; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; padding: 6px; background-color: #ffffff;">
        <div style="padding: 20px 10px; text-align: center;">
            <img src="{{ config('booking.reservation_logo_url') }}" alt="Dallas Black Cars Limo Service" width="250" style="max-width: 250px; margin-bottom: 10px; height: auto; display: inline-block; border: 0;">
            <h2 style="margin: 0; font-size: 22px; color: #228b22;">Payment received</h2>
            <p style="margin: 5px 0 0; font-size: 15px; color: #555;">Reservation #{{ $payload['booking_id'] ?? '' }}</p>
        </div>

        <div style="padding: 10px 16px 24px;">
            @if($isAdmin)
                <p style="font-size: 14px; margin: 0 0 12px;">
                    A customer payment was completed via Stripe for reservation #{{ $payload['booking_id'] ?? '' }}.
                </p>
            @else
                <p style="font-size: 14px; margin: 0 0 12px;">
                    Dear {{ $payload['customer_name'] ?? 'Valued Customer' }},
                </p>
                <p style="font-size: 14px; margin: 0 0 16px;">
                    Thank you — your payment has been received and your reservation is marked as <strong>Paid</strong>.
                </p>
            @endif

            <table cellpadding="0" cellspacing="0" width="100%" style="font-size: 13px; background: #f8f9fa; border-radius: 6px; margin: 0 0 20px;">
                <tr>
                    <td style="padding: 10px 14px; font-weight: bold; color: #555; width: 40%;">Amount paid</td>
                    <td style="padding: 10px 14px; color: #111; font-size: 18px; font-weight: 700;">${{ number_format((float) ($payload['amount'] ?? 0), 2) }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 14px; font-weight: bold; color: #555;">Status</td>
                    <td style="padding: 6px 14px; color: #228b22; font-weight: 700;">Paid</td>
                </tr>
                @if(!empty($payload['pickup_date']))
                <tr>
                    <td style="padding: 6px 14px; font-weight: bold; color: #555;">Pickup</td>
                    <td style="padding: 6px 14px; color: #333;">{{ $payload['pickup_date'] }}{{ !empty($payload['pickup_time']) ? ' at ' . $payload['pickup_time'] : '' }}</td>
                </tr>
                @endif
                @if(!empty($payload['pickup_location']))
                <tr>
                    <td style="padding: 6px 14px 12px; font-weight: bold; color: #555; vertical-align: top;">From</td>
                    <td style="padding: 6px 14px 12px; color: #333;">{{ $payload['pickup_location'] }}</td>
                </tr>
                @endif
                @if(!empty($payload['dropoff_location']))
                <tr>
                    <td style="padding: 6px 14px 12px; font-weight: bold; color: #555; vertical-align: top;">To</td>
                    <td style="padding: 6px 14px 12px; color: #333;">{{ $payload['dropoff_location'] }}</td>
                </tr>
                @endif
            </table>

            @if(!$isAdmin)
                <p style="font-size: 13px; color: #555; margin: 0 0 8px;">
                    If you have questions about your trip, reply to this email or contact us.
                </p>
            @endif

            <p style="font-size: 12px; color: #888; margin: 16px 0 0;">
                Dallas Black Cars Limo Service
            </p>
        </div>
    </div>
</body>
</html>
