<?php

namespace App\Http\Controllers;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Models\Order;

class PaymentController extends Controller
{
    public function checkout()
    {
        // Set Stripe secret key
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Create order in database
        $order = Order::create([
            'product_name' => 'Test Product',
            'amount' => 1000
        ]);

        // Create Stripe checkout session
        $session = Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Test Product'
                    ],
                    'unit_amount' => 1000
                ],
                'quantity' => 1
            ]],
            // Redirect after successful payment
            'success_url' => url('/success'),

            // Redirect if payment cancelled
            'cancel_url' => url('/cancel'),
        ]);

        // Save Stripe session ID
        $order->update([
            'stripe_session_id' => $session->id
        ]);

        // Redirect user to Stripe checkout page
        return redirect($session->url);
    }
}