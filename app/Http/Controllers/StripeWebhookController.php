<?php

namespace App\Http\Controllers;

use App\Services\StripePaymentLinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            if ($webhookSecret) {
                $event = Webhook::constructEvent($payload, (string) $sigHeader, $webhookSecret);
            } else {
                // Local/dev fallback when STRIPE_WEBHOOK_SECRET is not set.
                $event = json_decode($payload);
                if (json_last_error() !== JSON_ERROR_NONE || empty($event->type)) {
                    return response()->json(['error' => 'Invalid payload'], 400);
                }
                Log::warning('Stripe webhook processed without signature verification (STRIPE_WEBHOOK_SECRET missing).');
            }
        } catch (UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $type = is_object($event) ? ($event->type ?? null) : ($event['type'] ?? null);

        if (in_array($type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            $sessionObject = is_object($event)
                ? ($event->data->object ?? null)
                : ($event['data']['object'] ?? null);

            if ($sessionObject) {
                try {
                    $service = new StripePaymentLinkService();
                    // Re-fetch to get a real Stripe\Checkout\Session instance when possible.
                    $sessionId = is_object($sessionObject)
                        ? ($sessionObject->id ?? null)
                        : ($sessionObject['id'] ?? null);

                    if ($sessionId) {
                        $session = $service->retrieveSession($sessionId);
                        $service->markBookingPaidFromSession($session);
                    }
                } catch (\Throwable $e) {
                    Log::error('Stripe webhook payment link handling failed', [
                        'type' => $type,
                        'message' => $e->getMessage(),
                    ]);
                    report($e);

                    return response()->json(['error' => 'Handler failed'], 500);
                }
            }
        }

        return response()->json(['received' => true]);
    }
}
