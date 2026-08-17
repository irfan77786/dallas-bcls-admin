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
        public array $payload
    ) {
    }

    public function envelope(): Envelope
    {
        $bookingId = $this->payload['booking_id'] ?? '';

        return new Envelope(
            subject: 'Payment received — Reservation #' . $bookingId
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-payment-success',
            with: ['payload' => $this->payload]
        );
    }
}
