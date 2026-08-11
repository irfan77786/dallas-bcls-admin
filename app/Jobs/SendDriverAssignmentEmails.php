<?php

namespace App\Jobs;

use App\Mail\DriverAssignmentMail;
use App\Models\Booking;
use App\Services\BookingEmailPayloadBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDriverAssignmentEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $bookingId
    ) {
    }

    public function handle(): void
    {
        $booking = Booking::with([
            'vehicle',
            'passengers',
            'booker',
            'breakdown',
            'accountSnapshot',
            'driver',
        ])->find($this->bookingId);

        if (! $booking || ! $booking->driver) {
            Log::warning('SendDriverAssignmentEmails skipped — booking or driver missing', [
                'booking_id' => $this->bookingId,
            ]);

            return;
        }

        try {
            $bookingData = BookingEmailPayloadBuilder::build($booking);
        } catch (\Throwable $e) {
            Log::error('SendDriverAssignmentEmails payload failed', [
                'booking_id' => $this->bookingId,
                'message' => $e->getMessage(),
            ]);
            report($e);

            return;
        }

        $recipients = [];

        $passengerEmail = trim((string) ($bookingData['email'] ?? ''));
        if ($passengerEmail !== '' && filter_var($passengerEmail, FILTER_VALIDATE_EMAIL)) {
            $recipients[] = ['email' => $passengerEmail, 'isAdmin' => false];
        }

        $adminEmail = trim((string) config('mail.admin_email', env('ADMIN_EMAIL_ADDRESS')));
        if ($adminEmail !== '' && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $recipients[] = ['email' => $adminEmail, 'isAdmin' => true];
        }

        // Also notify booker when booking-for-others and email differs.
        if (! empty($bookingData['isBookingForOthers'])) {
            $bookerEmail = trim((string) ($bookingData['booker_email'] ?? ''));
            if (
                $bookerEmail !== ''
                && filter_var($bookerEmail, FILTER_VALIDATE_EMAIL)
                && strcasecmp($bookerEmail, $passengerEmail) !== 0
            ) {
                $recipients[] = ['email' => $bookerEmail, 'isAdmin' => false];
            }
        }

        if ($recipients === []) {
            Log::warning('SendDriverAssignmentEmails: no valid recipients', [
                'booking_id' => $booking->booking_id,
            ]);

            return;
        }

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient['email'])->send(
                    new DriverAssignmentMail($bookingData, $recipient['isAdmin'])
                );
                Log::info('Driver assignment email sent', [
                    'to' => $recipient['email'],
                    'booking_id' => $booking->booking_id,
                    'is_admin' => $recipient['isAdmin'],
                ]);
            } catch (\Throwable $e) {
                Log::error('Driver assignment email failed', [
                    'to' => $recipient['email'],
                    'booking_id' => $booking->booking_id,
                    'message' => $e->getMessage(),
                ]);
                report($e);
            }
        }
    }
}
