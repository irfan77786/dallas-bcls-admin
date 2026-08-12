<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingPaymentSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $payload,
        public bool $isAdmin = false
    ) {
    }

    public function envelope(): Envelope
    {
        $bookingId = $this->payload['booking_id'] ?? '';
        $prefix = $this->isAdmin ? '[Admin] ' : '';

        return new Envelope(
            subject: $prefix . 'Payment received — Reservation #' . $bookingId
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-payment-success',
            with: [
                'payload' => $this->payload,
                'isAdmin' => $this->isAdmin,
            ]
        );
    }
}
