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

    /** Absolute path to generated PDF (preferred for attachment). */
    public ?string $pdfPath;

    public function __construct($bookingData, $isAdmin = false, $sendToBooker = false, ?string $pdfPath = null)
    {
        $this->bookingData = (array) $bookingData;
        $this->isAdmin = $isAdmin;
        $this->sendToBooker = $sendToBooker;
        $this->pdfPath = $pdfPath;
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
        $path = $this->pdfPath;
        if ($path === null || $path === '' || ! is_file($path)) {
            $id = (string) ($this->bookingData['booking_id'] ?? 'booking');
            $path = public_path('pdfs/' . $id . '.pdf');
        }

        if (! is_file($path)) {
            return [];
        }

        $basename = basename($path);
        if ($basename === '' || $basename === '.' || $basename === '..') {
            $basename = ($this->bookingData['booking_id'] ?? 'booking') . '.pdf';
        }

        return [
            Attachment::fromPath($path)
                ->as($basename)
                ->withMime('application/pdf'),
        ];
    }
}
