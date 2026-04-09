<?php

namespace App\Jobs;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CreateBookingDocs
{
    use Dispatchable, Queueable, SerializesModels;

    public $bookingData;

    public $customBookingId;

    /** Absolute path to the PDF written for this booking (used for email attachment). */
    private ?string $resolvedPdfPath = null;

    public function __construct($bookingData, $customBookingId)
    {
        $this->bookingData = $bookingData;
        $this->customBookingId = $customBookingId;
    }

    public function handle()
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(120);
        }

        $this->writeBookingPdfIfPossible();

        $adminEmail = config('mail.admin_email', env('ADMIN_EMAIL_ADDRESS'));

        $recipients = [
            ['email' => $this->bookingData['email'], 'isAdmin' => false, 'isBooker' => false],
        ];

        if (! empty($adminEmail)) {
            $recipients[] = ['email' => trim($adminEmail), 'isAdmin' => true, 'isBooker' => false];
        }

        if (! empty($this->bookingData['isBookingForOthers']) && ! empty($this->bookingData['booker_email'])
            && $this->bookingData['booker_email'] !== $this->bookingData['email']) {
            $recipients[] = [
                'email' => $this->bookingData['booker_email'],
                'isAdmin' => false,
                'isBooker' => true,
            ];
        }

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient['email'])->send(new \App\Mail\Booking(
                    $this->bookingData,
                    $recipient['isAdmin'],
                    $recipient['isBooker'],
                    $this->resolvedPdfPath
                ));
                Log::info('Booking confirmation email sent', [
                    'to' => $recipient['email'],
                    'booking_id' => $this->bookingData['booking_id'] ?? $this->customBookingId,
                    'is_admin' => $recipient['isAdmin'],
                ]);
            } catch (\Throwable $e) {
                Log::error('CreateBookingDocs mail failed', [
                    'to' => $recipient['email'],
                    'booking_id' => $this->bookingData['booking_id'] ?? $this->customBookingId,
                    'message' => $e->getMessage(),
                    'exception' => $e,
                ]);
            }
        }
    }

    /**
     * PDF is optional for email: if generation fails, confirmation still sends (without attachment).
     */
    private function writeBookingPdfIfPossible(): void
    {
        try {
            $pdfsDirectory = public_path('pdfs');

            if (! file_exists($pdfsDirectory)) {
                mkdir($pdfsDirectory, 0777, true);
            }

            $filename = (string) $this->customBookingId . '.pdf';
            $fullPath = $pdfsDirectory . DIRECTORY_SEPARATOR . $filename;

            $pdf = Pdf::loadView('pdfs.booking', ['bookingData' => $this->bookingData]);
            $binary = $pdf->output();
            file_put_contents($fullPath, $binary);

            if (is_file($fullPath) && filesize($fullPath) > 0) {
                $this->resolvedPdfPath = $fullPath;
            }
        } catch (\Throwable $e) {
            Log::error('CreateBookingDocs PDF generation failed (emails will still be attempted without attachment)', [
                'booking_id' => $this->customBookingId,
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);
        }
    }
}
