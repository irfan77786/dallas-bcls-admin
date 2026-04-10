<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingReservationComposerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $bookingData,
        public bool $isAdmin,
        public bool $sendToBooker,
        public ?string $pdfPath
    ) {
    }

    public function envelope(): Envelope
    {
        $date = $this->bookingData['pickup_date'] ?? '';
        $time = $this->bookingData['pickup_time'] ?? '';
        if (is_object($time) && method_exists($time, 'format')) {
            $time = $time->format('H:i:s');
        }
        $time = is_string($time) ? $time : (string) $time;

        try {
            $pickupDateTime = \Carbon\Carbon::parse(trim($date . ' ' . $time))->format('F j, Y \a\t g:i A');
        } catch (\Throwable $e) {
            $pickupDateTime = trim($date . ' ' . $time) ?: 'TBD';
        }

        $prefix = $this->isAdmin ? '[Admin] ' : '';
        $subject = $prefix . 'Conf#' . ($this->bookingData['booking_id'] ?? '') . ' For '
            . ($this->bookingData['customer_name'] ?? $this->bookingData['passenger_name'] ?? 'Customer')
            . ' [' . $pickupDateTime . ']';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $view = $this->isAdmin
            ? 'emails.booking-reservation-admin'
            : 'emails.booking-reservation-customer';

        return new Content(
            view: $view,
            with: [
                'bookingData' => $this->bookingData,
                'sendToBooker' => $this->sendToBooker,
            ]
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if ($this->pdfPath === null || $this->pdfPath === '' || ! is_file($this->pdfPath)) {
            return [];
        }

        $basename = basename($this->pdfPath);
        if ($basename === '' || $basename === '.' || $basename === '..') {
            $basename = ($this->bookingData['booking_id'] ?? 'booking') . '.pdf';
        }

        return [
            Attachment::fromPath($this->pdfPath)
                ->as($basename)
                ->withMime('application/pdf'),
        ];
    }
}
