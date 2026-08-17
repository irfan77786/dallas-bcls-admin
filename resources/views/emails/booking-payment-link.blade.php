<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment link</title>
    <link href="https://fonts.googleapis.com/css2?family=Abel&display=swap" rel="stylesheet">
</head>
<body style="font-family: 'Abel', 'Helvetica', 'Arial', sans-serif; line-height: 1.6; color: #333333; margin: 0; padding: 0; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; padding: 6px; background-color: #ffffff;">
        <div style="padding: 20px 10px; text-align: center;">
            <img src="https://dallasblackcarslimoservice.com/img/black-car-service-dallas-logo.PNG" alt="Dallas Black Cars Limo Service" width="250" style="max-width: 250px; margin-bottom: 10px; height: auto; display: inline-block; border: 0;">
            <h2 style="margin: 0; font-size: 22px; color: #222;">Complete your payment</h2>
            <p style="margin: 5px 0 0; font-size: 15px; color: #555;">Reservation #{{ $payload['booking_id'] ?? '' }}</p>
        </div>

        <div style="padding: 10px 16px 24px;">
            <p style="font-size: 14px; margin: 0 0 12px;">
                Dear {{ $payload['customer_name'] ?? 'Valued Customer' }},
            </p>
            <p style="font-size: 14px; margin: 0 0 16px;">
                Please use the secure Stripe link below to pay for your reservation.
            </p>

            @if(!empty($payload['personal_message']))
            <div style="padding: 14px 16px; margin: 0 0 18px; background: #faf8f3; border: 1px solid #e8e0d0; border-radius: 8px; font-size: 13px; color: #333;">
                <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #9e7c1e; margin-bottom: 8px;">Message from us</div>
                <div style="white-space: pre-wrap; line-height: 1.5;">{{ $payload['personal_message'] }}</div>
            </div>
            @endif

            <table cellpadding="0" cellspacing="0" width="100%" style="font-size: 13px; background: #f8f9fa; border-radius: 6px; margin: 0 0 20px;">
                <tr>
                    <td style="padding: 10px 14px; font-weight: bold; color: #555; width: 40%;">Amount due</td>
                    <td style="padding: 10px 14px; color: #111; font-size: 18px; font-weight: 700;">${{ number_format((float) ($payload['amount'] ?? 0), 2) }}</td>
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
            </table>

            <div style="text-align: center; margin: 24px 0;">
                <a href="{{ $payload['payment_url'] }}"
                   style="display: inline-block; background: #9e7c1e; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 6px; font-size: 16px; font-weight: 700;">
                    Pay now
                </a>
            </div>

            <p style="font-size: 12px; color: #666; margin: 0 0 8px; word-break: break-all;">
                Or copy this link:<br>
                <a href="{{ $payload['payment_url'] }}" style="color: #0f3460;">{{ $payload['payment_url'] }}</a>
            </p>
            <p style="font-size: 12px; color: #888; margin: 16px 0 0;">
                This payment link expires in 24 hours. If you have questions, reply to this email or contact us.
            </p>
        </div>
    </div>
</body>
</html>
