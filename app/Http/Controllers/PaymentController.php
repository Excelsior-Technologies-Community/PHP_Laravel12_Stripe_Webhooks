<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class PaymentController extends Controller
{
    /**
     * Create Stripe Checkout Session.
     */
    public function checkout()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $order = Order::create([
            'product_name' => 'Test Product',
            'customer_email' => null,
            'amount' => 1000,
            'currency' => 'usd',
            'payment_status' => 'pending',
        ]);

        $session = Session::create([
            'payment_method_types' => ['card'],

            'mode' => 'payment',

            'line_items' => [
                [
                    'price_data' => [
                        'currency' => $order->currency,

                        'product_data' => [
                            'name' => $order->product_name,
                        ],

                        'unit_amount' => $order->amount,
                    ],

                    'quantity' => 1,
                ],
            ],

            'metadata' => [
                'order_id' => $order->id,
            ],

            'success_url' => url(
                '/success?session_id={CHECKOUT_SESSION_ID}'
            ),

            'cancel_url' => url(
                '/cancel?session_id={CHECKOUT_SESSION_ID}'
            ),
        ]);

        $order->update([
            'stripe_session_id' => $session->id,
            'payment_intent_id' => $session->payment_intent,
        ]);

        return redirect($session->url);
    }

    /**
     * Successful payment.
     */
    public function success(Request $request)
    {
        $sessionId = $request->session_id;

        $order = null;

        if ($sessionId) {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            $session = Session::retrieve($sessionId);

            $order = Order::where(
                'stripe_session_id',
                $session->id
            )->first();

            if ($order) {
                $updates = [
                    'payment_intent_id' => $session->payment_intent,
                ];

                if (
                    !empty($session->customer_details) &&
                    !empty($session->customer_details->email)
                ) {
                    $updates['customer_email'] =
                        $session->customer_details->email;
                }

                if ($session->payment_status === 'paid') {
                    $updates['payment_status'] = 'paid';
                    $updates['paid_at'] = now();
                }

                $order->update($updates);
            }
        }

        return view('success', compact('order'));
    }

    /**
     * Cancelled payment.
     */
    public function cancel(Request $request)
    {
        $sessionId = $request->session_id;

        $order = null;

        if ($sessionId) {
            $order = Order::where(
                'stripe_session_id',
                $sessionId
            )->first();

            if (
                $order &&
                $order->payment_status === 'pending'
            ) {
                $order->update([
                    'payment_status' => 'cancelled',
                ]);
            }
        }

        return view('cancel', compact('order'));
    }
}