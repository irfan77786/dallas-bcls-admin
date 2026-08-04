<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment {{ ($status ?? '') === 'success' ? 'successful' : 'update' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Abel&display=swap" rel="stylesheet">
    <style>
        body { font-family: Abel, Helvetica, Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 2rem 1rem; color: #222; }
        .card { max-width: 480px; margin: 0 auto; background: #fff; border-radius: 10px; padding: 2rem 1.5rem; text-align: center; box-shadow: 0 8px 28px rgba(17,37,62,.08); }
        h1 { font-size: 1.6rem; margin: 0 0 .75rem; }
        p { margin: 0 0 .5rem; color: #555; line-height: 1.5; }
        .ok { color: #157347; }
        .warn { color: #8f6500; }
        .amount { font-size: 1.35rem; font-weight: 700; color: #17324d; margin: 1rem 0; }
    </style>
</head>
<body>
    <div class="card">
        @if(($status ?? '') === 'success')
            <h1 class="ok">Payment successful</h1>
            <p>Thank you. Your payment for reservation <strong>#{{ $bookingId ?? '' }}</strong> was received.</p>
            @if(isset($amount))
                <div class="amount">${{ number_format((float) $amount, 2) }}</div>
            @endif
            <p>You can close this window.</p>
        @elseif(($status ?? '') === 'cancel')
            <h1 class="warn">Payment cancelled</h1>
            <p>No charge was made. You can use the payment link from your email again when you are ready.</p>
            @if(!empty($bookingId))
                <p>Reservation <strong>#{{ $bookingId }}</strong></p>
            @endif
        @else
            <h1>Payment status</h1>
            <p>{{ $message ?? 'Unable to confirm payment status yet. If you completed payment, it may take a moment to update.' }}</p>
        @endif
    </div>
</body>
</html>
