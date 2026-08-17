<?php

namespace App\Jobs;

use App\Mail\BookingPaymentSuccessMail;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentSuccessEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $bookingId,
        public float $amount,
        public ?string $checkoutEmail = null
    ) {
    }

    public function handle(): void
    {
        $booking = Booking::with(['passengers', 'booker'])->find($this->bookingId);
        if (! $booking) {
            Log::warning('SendPaymentSuccessEmails skipped — booking not found', [
                'booking_id' => $this->bookingId,
            ]);

            return;
        }

        $passenger = $booking->passengers->first();
        $customerName = $passenger
            ? trim($passenger->first_name . ' ' . $passenger->last_name)
            : 'Customer';

        $pickupTime = $booking->pickup_time;
        if (is_object($pickupTime) && method_exists($pickupTime, 'format')) {
            $pickupTime = $pickupTime->format('H:i');
        } else {
            $pickupTime = substr((string) $pickupTime, 0, 5);
        }

        $payload = [
            'booking_id' => $booking->booking_id ?: (string) $booking->id,
            'customer_name' => $customerName,
            'amount' => $this->amount,
            'pickup_date' => $booking->pickup_date
                ? \Carbon\Carbon::parse($booking->pickup_date)->format('F j, Y')
                : null,
            'pickup_time' => $pickupTime ?: null,
            'pickup_location' => $booking->pickup_location,
            'dropoff_location' => $booking->dropoff_location,
        ];

        $recipients = [];
        $customerEmail = trim((string) ($this->checkoutEmail ?? ''));
        if ($customerEmail === '' && $passenger) {
            $customerEmail = trim((string) ($passenger->email ?? ''));
        }
        if ($customerEmail !== '' && filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            $recipients[] = $customerEmail;
        }

        $adminEmail = trim((string) config('mail.admin_email', env('ADMIN_EMAIL_ADDRESS')));
        if ($adminEmail !== '' && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $recipients[] = $adminEmail;
        }

        $recipients = array_values(array_unique(array_map('strtolower', $recipients)));

        if ($recipients === []) {
            Log::warning('SendPaymentSuccessEmails: no valid recipients', [
                'booking_id' => $booking->booking_id,
            ]);

            return;
        }

        foreach ($recipients as $email) {
            try {
                Mail::to($email)->send(new BookingPaymentSuccessMail($payload));
                Log::info('Payment success email sent', [
                    'to' => $email,
                    'booking_id' => $booking->booking_id,
                ]);
            } catch (\Throwable $e) {
                Log::error('Payment success email failed', [
                    'to' => $email,
                    'booking_id' => $booking->booking_id,
                    'message' => $e->getMessage(),
                ]);
                report($e);
            }
        }
    }
}
