<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DriverAssignmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $bookingData,
        public bool $isAdmin = false
    ) {
    }

    public function envelope(): Envelope
    {
        $bookingId = $this->bookingData['booking_id'] ?? '';
        $driverName = $this->bookingData['driver']['name'] ?? 'Driver';
        $prefix = $this->isAdmin ? '[Admin] ' : '';

        return new Envelope(
            subject: $prefix . 'Driver assigned — Conf#' . $bookingId . ' (' . $driverName . ')'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.driver-assignment',
            with: [
                'bookingData' => $this->bookingData,
                'isAdmin' => $this->isAdmin,
            ]
        );
    }
}
