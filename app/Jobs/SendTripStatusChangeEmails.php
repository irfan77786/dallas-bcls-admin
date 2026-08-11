<?php

namespace App\Jobs;

use App\Http\Controllers\DispatchController;
use App\Mail\TripStatusChangeMail;
use App\Models\Booking;
use App\Services\BookingEmailPayloadBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTripStatusChangeEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $bookingId,
        public ?string $previousStatus = null,
        public ?string $newStatus = null
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

        if (! $booking) {
            Log::warning('SendTripStatusChangeEmails skipped — booking missing', [
                'booking_id' => $this->bookingId,
            ]);

            return;
        }

        try {
            $bookingData = BookingEmailPayloadBuilder::build($booking);
        } catch (\Throwable $e) {
            Log::error('SendTripStatusChangeEmails payload failed', [
                'booking_id' => $this->bookingId,
                'message' => $e->getMessage(),
            ]);
            report($e);

            return;
        }

        $options = DispatchController::tripStatusOptions();
        $previousKey = strtolower(trim((string) $this->previousStatus));
        $newKey = strtolower(trim((string) ($this->newStatus ?: $booking->trip_status)));

        $bookingData['trip_status'] = $newKey;
        $bookingData['trip_status_label'] = $options[$newKey] ?? ($booking->trip_status ?: 'Updated');
        $bookingData['previous_trip_status'] = $previousKey !== '' ? $previousKey : null;
        $bookingData['previous_trip_status_label'] = ($previousKey !== '' && isset($options[$previousKey]))
            ? $options[$previousKey]
            : ($previousKey !== '' ? $previousKey : '—');

        $recipients = [];

        $passengerEmail = trim((string) ($bookingData['email'] ?? ''));
        if ($passengerEmail !== '' && filter_var($passengerEmail, FILTER_VALIDATE_EMAIL)) {
            $recipients[] = ['email' => $passengerEmail, 'isAdmin' => false];
        }

        $adminEmail = trim((string) config('mail.admin_email', env('ADMIN_EMAIL_ADDRESS')));
        if ($adminEmail !== '' && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $recipients[] = ['email' => $adminEmail, 'isAdmin' => true];
        }

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
            Log::warning('SendTripStatusChangeEmails: no valid recipients', [
                'booking_id' => $booking->booking_id,
            ]);

            return;
        }

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient['email'])->send(
                    new TripStatusChangeMail($bookingData, $recipient['isAdmin'])
                );
                Log::info('Trip status change email sent', [
                    'to' => $recipient['email'],
                    'booking_id' => $booking->booking_id,
                    'status' => $bookingData['trip_status_label'],
                    'is_admin' => $recipient['isAdmin'],
                ]);
            } catch (\Throwable $e) {
                Log::error('Trip status change email failed', [
                    'to' => $recipient['email'],
                    'booking_id' => $booking->booking_id,
                    'message' => $e->getMessage(),
                ]);
                report($e);
            }
        }
    }
}
