<?php

namespace App\Jobs;

use App\Models\Order;
use Spatie\StripeWebhooks\StripeWebhookCall;

class HandleCheckoutSessionCompleted
{

    public function handle(StripeWebhookCall $webhookCall)
    {

        $payload = $webhookCall->payload;

        $session = $payload['data']['object'];

        $sessionId = $session['id'];

        $order = Order::where('stripe_session_id', $sessionId)->first();

        if ($order) {

            $order->update([
                'payment_status' => 'paid'
            ]);

        }

    }

}