<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Booking extends Mailable
{
    use Queueable, SerializesModels;

    public $bookingData;

    public $isAdmin;

    public $sendToBooker;

    public function __construct($bookingData, $isAdmin = false, $sendToBooker = false)
    {
        $this->bookingData = (array) $bookingData;
        $this->isAdmin = $isAdmin;
        $this->sendToBooker = $sendToBooker;
    }

    public function envelope()
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

        return new Envelope(
            subject: 'Conf#' . ($this->bookingData['booking_id'] ?? '') . ' For ' . ($this->bookingData['customer_name'] ?? $this->bookingData['passenger_name'] ?? 'Customer') . ' [' . $pickupDateTime . ']',
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.booking',
            with: [
                'bookingData' => $this->bookingData,
                'isAdmin' => $this->isAdmin,
                'sendToBooker' => $this->sendToBooker,
            ]
        );
    }

    public function attachments()
    {
        $path = public_path('pdfs/' . $this->bookingData['booking_id'] . '.pdf');
        if (! is_file($path)) {
            return [];
        }

        return [
            Attachment::fromPath($path)
                ->as($this->bookingData['booking_id'] . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
