<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TripStatusChangeMail extends Mailable
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
        $status = $this->bookingData['trip_status_label'] ?? 'Updated';
        $prefix = $this->isAdmin ? '[Admin] ' : '';

        return new Envelope(
            subject: $prefix . 'Trip status: ' . $status . ' — Conf#' . $bookingId
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.trip-status-change',
            with: [
                'bookingData' => $this->bookingData,
                'isAdmin' => $this->isAdmin,
            ]
        );
    }
}
