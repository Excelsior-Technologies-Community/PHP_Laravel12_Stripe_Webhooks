<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\WebhookEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\WebhookClient\Models\WebhookCall;

class HandleCheckoutSessionCompleted implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public WebhookCall $webhookCall
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $payload = $this->webhookCall->payload;

        /*
        |--------------------------------------------------------------------------
        | Get Stripe event information
        |--------------------------------------------------------------------------
        */

        $eventId = $payload['id'] ?? null;

        $eventType = $payload['type']
            ?? 'checkout.session.completed';

        $session = $payload['data']['object'] ?? [];

        $sessionId = $session['id'] ?? null;

        $stripePaymentStatus = $session['payment_status'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | Get Laravel Order ID from Stripe metadata
        |--------------------------------------------------------------------------
        */

        $orderId = $session['metadata']['order_id'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | Stop if Stripe event ID is missing
        |--------------------------------------------------------------------------
        */

        if (!$eventId) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Create or update webhook event history
        |--------------------------------------------------------------------------
        */

        $webhookEvent = WebhookEvent::updateOrCreate(
            [
                'event_id' => $eventId,
            ],
            [
                'event_type' => $eventType,
                'status' => 'received',
                'stripe_session_id' => $sessionId,
                'payment_status' => $stripePaymentStatus,
                'payload' => $payload,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Stop if Checkout Session ID is missing
        |--------------------------------------------------------------------------
        */

        if (!$sessionId) {
            $webhookEvent->update([
                'status' => 'failed',
                'processed_at' => now(),
            ]);

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Find Laravel order using metadata
        |--------------------------------------------------------------------------
        */

        $order = null;

        if ($orderId) {
            $order = Order::find($orderId);
        }

        /*
        |--------------------------------------------------------------------------
        | Fallback: Find order using Stripe Session ID
        |--------------------------------------------------------------------------
        */

        if (!$order) {
            $order = Order::where(
                'stripe_session_id',
                $sessionId
            )->first();
        }

        /*
        |--------------------------------------------------------------------------
        | Stripe event received but no matching Laravel order
        |--------------------------------------------------------------------------
        |
        | This can happen with Stripe CLI fixture events.
        | The webhook itself is valid, but it does not belong to
        | any order created by this Laravel application.
        |
        */

        if (!$order) {
            $webhookEvent->update([
                'status' => 'unmatched',
                'processed_at' => now(),
            ]);

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Mark Laravel order as paid
        |--------------------------------------------------------------------------
        */

        if ($stripePaymentStatus === 'paid') {
            $order->update([
                'payment_status' => 'paid',
            ]);

            $webhookEvent->update([
                'status' => 'processed',
                'payment_status' => 'paid',
                'processed_at' => now(),
            ]);

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Payment was not marked as paid
        |--------------------------------------------------------------------------
        */

        $webhookEvent->update([
            'status' => 'failed',
            'payment_status' => $stripePaymentStatus,
            'processed_at' => now(),
        ]);
    }
}