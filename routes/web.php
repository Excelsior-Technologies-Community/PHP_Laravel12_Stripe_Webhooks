<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentDashboardController;

/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/dashboard');
});

/*
|--------------------------------------------------------------------------
| Stripe Checkout
|--------------------------------------------------------------------------
*/

Route::get(
    '/checkout',
    [PaymentController::class, 'checkout']
)->name('checkout');

/*
|--------------------------------------------------------------------------
| Payment Result
|--------------------------------------------------------------------------
*/


Route::get(
    '/success',
    [PaymentController::class, 'success']
)->name('payment.success');

Route::get(
    '/cancel',
    [PaymentController::class, 'cancel']
)->name('payment.cancel');



/*
|--------------------------------------------------------------------------
| Payment Dashboard
|--------------------------------------------------------------------------
*/

Route::get(
    '/dashboard',
    [PaymentDashboardController::class, 'dashboard']
)->name('dashboard');

/*
|--------------------------------------------------------------------------
| Orders
|--------------------------------------------------------------------------
*/

Route::get(
    '/orders',
    [PaymentDashboardController::class, 'orders']
)->name('orders');

/*
|--------------------------------------------------------------------------
| Webhook Events
|--------------------------------------------------------------------------
*/

Route::get(
    '/webhook-events',
    [PaymentDashboardController::class, 'webhookEvents']
)->name('webhook.events');

/*
|--------------------------------------------------------------------------
| Webhook Details
|--------------------------------------------------------------------------
*/

Route::get(
    '/webhook-events/{webhookEvent}',
    [PaymentDashboardController::class, 'webhookDetails']
)->name('webhook.details');

/*
|--------------------------------------------------------------------------
| Stripe Webhook
|--------------------------------------------------------------------------
|
| IMPORTANT:
| This must use Spatie's Stripe webhook route.
| Do not use Route::post() with a closure here.
|
*/

Route::stripeWebhooks('stripe/webhook');