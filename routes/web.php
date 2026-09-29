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
| Dashboard
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

Route::get(
    '/orders/export',
    [PaymentDashboardController::class, 'exportOrders']
)->name('orders.export');

Route::get(
    '/orders/{order}',
    [PaymentDashboardController::class, 'orderDetails']
)->name('orders.details');

/*
|--------------------------------------------------------------------------
| Webhooks
|--------------------------------------------------------------------------
*/

Route::get(
    '/webhook-events',
    [PaymentDashboardController::class, 'webhookEvents']
)->name('webhook.events');

Route::get(
    '/webhook-events/{webhookEvent}/download',
    [PaymentDashboardController::class, 'downloadWebhook']
)->name('webhook.download');

Route::get(
    '/webhook-events/{webhookEvent}',
    [PaymentDashboardController::class, 'webhookDetails']
)->name('webhook.details');

/*
|--------------------------------------------------------------------------
| Stripe Webhook
|--------------------------------------------------------------------------
*/

Route::stripeWebhooks('stripe/webhook');