<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class PaymentController extends Controller
{
    /**
     * Create Stripe Checkout Session.
     */
    public function checkout()
    {
        // Set Stripe secret key
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Create order in database
        $order = Order::create([
            'product_name' => 'Test Product',
            'amount' => 1000,
            'payment_status' => 'pending',
        ]);

        // Create Stripe Checkout Session
        $session = Session::create([
            'payment_method_types' => ['card'],

            'mode' => 'payment',

            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',

                        'product_data' => [
                            'name' => $order->product_name,
                        ],

                        'unit_amount' => $order->amount,
                    ],

                    'quantity' => 1,
                ],
            ],

            // Connect Stripe Checkout Session with Laravel Order
            'metadata' => [
                'order_id' => $order->id,
            ],

            // Send Stripe Checkout Session ID to Laravel
            'success_url' => url(
                '/success?session_id={CHECKOUT_SESSION_ID}'
            ),

            'cancel_url' => url(
                '/cancel?session_id={CHECKOUT_SESSION_ID}'
            ),
        ]);

        // Save Stripe Session ID
        $order->update([
            'stripe_session_id' => $session->id,
        ]);

        // Redirect customer to Stripe Checkout
        return redirect($session->url);
    }

    /**
     * Handle successful Stripe payment.
     */
    public function success()
    {
        $sessionId = request('session_id');

        $order = null;

        if ($sessionId) {
            // Set Stripe secret key
            Stripe::setApiKey(env('STRIPE_SECRET'));

            // Retrieve the exact Stripe Checkout Session
            $session = Session::retrieve($sessionId);

            // Find Laravel order using Stripe Session ID
            $order = Order::where(
                'stripe_session_id',
                $session->id
            )->first();

            // Mark order as paid
            if (
                $order &&
                $session->payment_status === 'paid'
            ) {
                $order->update([
                    'payment_status' => 'paid',
                ]);
            }
        }

        return view('success', compact('order'));
    }

    /**
     * Handle cancelled Stripe payment.
     */
    public function cancel()
    {
        $sessionId = request('session_id');

        $order = null;

        if ($sessionId) {
            $order = Order::where(
                'stripe_session_id',
                $sessionId
            )->first();

            // Mark pending order as cancelled
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
