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

    public function __construct(
        public WebhookCall $webhookCall
    ) {
    }

    /**
     * Execute job.
     */
    public function handle(): void
    {
        $payload = $this->webhookCall->payload;

        $eventId = $payload['id'] ?? null;

        $eventType = $payload['type'] ?? null;

        $eventCreatedAt = null;

        if (!empty($payload['created'])) {
            $eventCreatedAt = date(
                'Y-m-d H:i:s',
                $payload['created']
            );
        }

        if (!$eventId || !$eventType) {
            return;
        }

        $object = $payload['data']['object'] ?? [];

        $sessionId = $object['id'] ?? null;

        $paymentStatus =
            $object['payment_status'] ?? null;

        $paymentIntentId =
            $object['payment_intent'] ?? null;

        $orderId =
            $object['metadata']['order_id'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | Create webhook history
        |--------------------------------------------------------------------------
        */

        $webhookEvent = WebhookEvent::updateOrCreate(
            [
                'event_id' => $eventId,
            ],
            [
                'event_type' => $eventType,
                'event_created_at' => $eventCreatedAt,
                'status' => 'received',
                'attempts' => 1,
                'stripe_session_id' => $sessionId,
                'payment_status' => $paymentStatus,
                'payload' => $payload,
                'error_message' => null,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Find order
        |--------------------------------------------------------------------------
        */

        $order = null;

        if ($orderId) {
            $order = Order::find($orderId);
        }

        if (!$order && $sessionId) {
            $order = Order::where(
                'stripe_session_id',
                $sessionId
            )->first();
        }

        /*
        |--------------------------------------------------------------------------
        | Payment Intent events
        |--------------------------------------------------------------------------
        */

        if (
            in_array($eventType, [
                'payment_intent.payment_failed',
                'payment_intent.succeeded',
            ])
        ) {
            $paymentIntentId = $object['id'] ?? null;

            if ($paymentIntentId) {
                $order = Order::where(
                    'payment_intent_id',
                    $paymentIntentId
                )->first();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | No matching order
        |--------------------------------------------------------------------------
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
        | Checkout completed
        |--------------------------------------------------------------------------
        */

        if ($eventType === 'checkout.session.completed') {
            $customerEmail =
                $object['customer_details']['email']
                ?? null;

            $updates = [
                'payment_intent_id' => $paymentIntentId,
            ];

            if ($customerEmail) {
                $updates['customer_email'] =
                    $customerEmail;
            }

            if ($paymentStatus === 'paid') {
                $updates['payment_status'] = 'paid';
                $updates['paid_at'] = now();
            }

            $order->update($updates);

            $webhookEvent->update([
                'status' =>
                    $paymentStatus === 'paid'
                        ? 'processed'
                        : 'failed',

                'payment_status' => $paymentStatus,

                'processed_at' => now(),
            ]);

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Checkout expired
        |--------------------------------------------------------------------------
        */

        if ($eventType === 'checkout.session.expired') {
            $order->update([
                'payment_status' => 'cancelled',
            ]);

            $webhookEvent->update([
                'status' => 'processed',
                'payment_status' => 'cancelled',
                'processed_at' => now(),
            ]);

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Payment failed
        |--------------------------------------------------------------------------
        */

        if ($eventType === 'payment_intent.payment_failed') {
            $failureReason =
                $object['last_payment_error']['message']
                ?? 'Payment failed';

            $order->update([
                'payment_status' => 'failed',
                'payment_intent_id' => $paymentIntentId,
                'failure_reason' => $failureReason,
            ]);

            $webhookEvent->update([
                'status' => 'processed',
                'payment_status' => 'failed',
                'error_message' => $failureReason,
                'processed_at' => now(),
            ]);

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Payment succeeded
        |--------------------------------------------------------------------------
        */

        if ($eventType === 'payment_intent.succeeded') {
            $order->update([
                'payment_status' => 'paid',
                'payment_intent_id' => $paymentIntentId,
                'paid_at' => now(),
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
        | Refund
        |--------------------------------------------------------------------------
        */

        if ($eventType === 'charge.refunded') {
            $order->update([
                'payment_status' => 'refunded',
            ]);

            $webhookEvent->update([
                'status' => 'processed',
                'payment_status' => 'refunded',
                'processed_at' => now(),
            ]);

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Unknown event
        |--------------------------------------------------------------------------
        */

        $webhookEvent->update([
            'status' => 'processed',
            'processed_at' => now(),
        ]);
    }
}