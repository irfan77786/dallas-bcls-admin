<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingPaymentLinkMail extends Mailable
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
            subject: 'Payment link for reservation #' . $bookingId
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-payment-link',
            with: ['payload' => $this->payload]
        );
    }
}
