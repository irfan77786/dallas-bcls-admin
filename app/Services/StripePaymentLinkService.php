<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class StripePaymentLinkService
{
    public function __construct()
    {
        $secret = config('services.stripe.secret');
        if (! $secret) {
            throw new \RuntimeException('Stripe is not configured. Add STRIPE_SECRET to your .env file.');
        }

        Stripe::setApiKey($secret);
    }

    /**
     * Create a one-time Stripe Checkout Session for the booking amount.
     *
     * @return array{session_id: string, url: string, amount: float}
     *
     * @throws ApiErrorException
     */
    public function createCheckoutSession(Booking $booking, string $customerEmail, ?string $customerName = null): array
    {
        $amount = round((float) $booking->total_price, 2);
        if ($amount < 0.50) {
            throw new \InvalidArgumentException('Booking total must be at least $0.50 to create a Stripe payment link.');
        }

        $amountInCents = (int) round($amount * 100);
        $publicId = (string) ($booking->booking_id ?: $booking->id);
        $productName = 'Reservation #' . $publicId;
        if ($customerName) {
            $productName .= ' — ' . $customerName;
        }

        $session = Session::create([
            'mode' => 'payment',
            'customer_email' => $customerEmail,
            'client_reference_id' => (string) $booking->id,
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => $amountInCents,
                    'product_data' => [
                        'name' => $productName,
                        'description' => $this->bookingDescription($booking),
                    ],
                ],
            ]],
            'metadata' => [
                'booking_db_id' => (string) $booking->id,
                'booking_id' => $publicId,
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'booking_db_id' => (string) $booking->id,
                    'booking_id' => $publicId,
                ],
            ],
            'success_url' => route('stripe.payment-link.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('stripe.payment-link.cancel', ['booking' => $booking->id]),
            'expires_at' => now()->addHours(23)->timestamp,
        ]);

        if (empty($session->id) || empty($session->url)) {
            throw new \RuntimeException('Stripe did not return a checkout session URL.');
        }

        $booking->stripe_checkout_session_id = $session->id;
        $booking->stripe_payment_link_url = $session->url;
        $booking->save();

        $this->upsertPendingPayment($booking, $session->id, $amount);

        return [
            'session_id' => $session->id,
            'url' => $session->url,
            'amount' => $amount,
        ];
    }

    /**
     * Mark booking Paid when Checkout Session is complete (webhook or success page).
     */
    public function markBookingPaidFromSession(Session $session): ?Booking
    {
        $booking = $this->resolveBookingFromSession($session);
        if (! $booking) {
            Log::warning('Stripe payment link: booking not found for session', [
                'session_id' => $session->id,
                'client_reference_id' => $session->client_reference_id ?? null,
                'metadata' => $session->metadata ?? null,
            ]);

            return null;
        }

        $alreadyPaid = strtolower((string) $booking->payment_status) === 'paid';
        if ($alreadyPaid && $session->payment_status === 'paid') {
            return $booking;
        }

        if (($session->status ?? '') !== 'complete' && ($session->payment_status ?? '') !== 'paid') {
            return $booking;
        }

        $paymentIntentId = is_string($session->payment_intent)
            ? $session->payment_intent
            : ($session->payment_intent->id ?? null);

        $amount = isset($session->amount_total)
            ? round(((int) $session->amount_total) / 100, 2)
            : round((float) $booking->total_price, 2);

        DB::transaction(function () use ($booking, $session, $paymentIntentId, $amount) {
            $booking->payment_status = 'Paid';
            $booking->stripe_checkout_session_id = $session->id;
            if (! empty($session->url)) {
                $booking->stripe_payment_link_url = $session->url;
            }
            $booking->save();

            $payment = $booking->payments()
                ->where(function ($q) use ($session, $paymentIntentId) {
                    $q->where('transaction_id', $session->id);
                    if ($paymentIntentId) {
                        $q->orWhere('transaction_id', $paymentIntentId);
                    }
                })
                ->latest('id')
                ->first();

            if ($payment) {
                $payment->update([
                    'payment_method' => 'stripe_checkout',
                    'payment_status' => 'Paid',
                    'transaction_id' => $paymentIntentId ?: $session->id,
                    'amount' => $amount,
                ]);
            } else {
                Payment::create([
                    'booking_id' => $booking->id,
                    'payment_method' => 'stripe_checkout',
                    'payment_status' => 'Paid',
                    'transaction_id' => $paymentIntentId ?: $session->id,
                    'amount' => $amount,
                ]);
            }
        });

        return $booking->fresh();
    }

    public function retrieveSession(string $sessionId): Session
    {
        return Session::retrieve($sessionId);
    }

    private function resolveBookingFromSession(Session $session): ?Booking
    {
        $dbId = $session->client_reference_id
            ?: ($session->metadata->booking_db_id ?? null);

        if ($dbId) {
            $booking = Booking::find($dbId);
            if ($booking) {
                return $booking;
            }
        }

        if (! empty($session->id)) {
            return Booking::where('stripe_checkout_session_id', $session->id)->first();
        }

        return null;
    }

    private function upsertPendingPayment(Booking $booking, string $sessionId, float $amount): void
    {
        $existing = $booking->payments()
            ->where(function ($q) use ($sessionId) {
                $q->where('transaction_id', $sessionId)
                    ->orWhere(function ($inner) {
                        $inner->where('payment_method', 'stripe_checkout')
                            ->where('payment_status', 'Pending');
                    });
            })
            ->latest('id')
            ->first();

        if ($existing) {
            $existing->update([
                'payment_method' => 'stripe_checkout',
                'payment_status' => 'Pending',
                'transaction_id' => $sessionId,
                'amount' => $amount,
            ]);

            return;
        }

        Payment::create([
            'booking_id' => $booking->id,
            'payment_method' => 'stripe_checkout',
            'payment_status' => 'Pending',
            'transaction_id' => $sessionId,
            'amount' => $amount,
        ]);
    }

    private function bookingDescription(Booking $booking): string
    {
        $parts = [];
        if ($booking->pickup_location) {
            $parts[] = 'Pickup: ' . \Illuminate\Support\Str::limit($booking->pickup_location, 80);
        }
        if ($booking->pickup_date) {
            $parts[] = 'Date: ' . $booking->pickup_date;
        }

        return implode(' | ', $parts) ?: 'Limousine reservation payment';
    }
}
