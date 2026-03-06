<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\PaymentController;

// Home route
Route::get('/', function () {
    return "Stripe Payment Demo";
});

// Checkout route to create Stripe session
Route::get('/checkout', [PaymentController::class, 'checkout']);

// Success page after payment
Route::get('/success', function () {
    return "Payment Successful";
});

// Cancel page if payment is cancelled
Route::get('/cancel', function () {
    return "Payment Cancelled";
});

// Stripe webhook endpoint
Route::post('/stripe/webhook', function (Request $request) {

    // Log Stripe webhook payload
    \Log::info('Stripe Webhook Received', $request->all());

    // Return success response
    return response()->json([
        'status' => 'success'
    ]);

});