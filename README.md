# PHP_Laravel12_Stripe_Webhooks

![Laravel](https://img.shields.io/badge/Laravel-12-red)
![PHP](https://img.shields.io/badge/PHP-8%2B-blue)
![Stripe](https://img.shields.io/badge/Stripe-Payments-purple)
![License](https://img.shields.io/badge/license-MIT-green)

---

# Overview

This project demonstrates how to integrate **Stripe Checkout and Webhooks** into a **Laravel 12 application**.

The system allows users to complete payments through Stripe and enables Laravel to receive webhook notifications when payment events occur.

### Key Capabilities

1. Open a Stripe Checkout page.
2. Complete a payment using Stripe.
3. Receive webhook events from Stripe.
4. Process webhook requests inside Laravel.

---

# Features

* Stripe Checkout integration
* Stripe Webhook listener
* Secure payment processing
* Laravel backend event handling
* Test payment support
* Stripe CLI testing

---


# Folder Structure

```
app
 ├── Http
 │    └── Controllers
 │         └── PaymentController.php
 ├── Models
 │    └── Order.php
 └── Jobs

config
 └── stripe-webhooks.php

routes
 └── web.php

database
 └── migrations
```

---

# 1. Create Laravel Project

Install Laravel using Composer.

```
composer create-project laravel/laravel laravel-stripe-webhook
```

Run the development server:

```
php artisan serve
```

Open the application:

```
http://127.0.0.1:8000
```

---

# 2. Install Packages

Install Stripe SDK and the Webhook handler package.

### Stripe PHP SDK

```
composer require stripe/stripe-php
```

### Spatie Stripe Webhooks

```
composer require spatie/laravel-stripe-webhooks
```

These packages provide:

• Stripe API integration
• Webhook verification
• Webhook job processing

---

# 3. Publish Stripe Webhook Configuration

Publish the webhook configuration file.

```
php artisan vendor:publish --provider="Spatie\StripeWebhooks\StripeWebhooksServiceProvider"
```

This creates:

```
config/stripe-webhooks.php
```

---

# 4. Configure Webhook Settings

Open the configuration file:

```
config/stripe-webhooks.php
```

```php
<?php

return [

    // Stripe webhook signing secret
    'signing_secret' => env('STRIPE_WEBHOOK_SECRET'),

    // Default job for other Stripe events
    'default_job' => '',

    // Map Stripe events to Laravel jobs
    'jobs' => [

        'checkout_session_completed' => App\Jobs\HandleCheckoutSessionCompleted::class,

    ],

    // Model used to store webhook calls
    'model' => \Spatie\WebhookClient\Models\WebhookCall::class,

    // Determines if webhook calls should be stored and processed
    'profile' => \Spatie\StripeWebhooks\StripeWebhookProfile::class,

    // Queue connection for webhook processing
    'connection' => env('STRIPE_WEBHOOK_CONNECTION'),

    // Queue name for webhook processing
    'queue' => env('STRIPE_WEBHOOK_QUEUE'),

    // Verify Stripe signature
    'verify_signature' => env('STRIPE_SIGNATURE_VERIFY', true),

];
```

---

# 5. Configure Environment Variables

Open the `.env` file and add Stripe keys.

```
STRIPE_KEY=pk_test_51T7vgH92VuKVcTwySTo4iKa1itAMYlMTf0kxWRXMLE2bUcCGFt5z4HEFqkO1
STRIPE_SECRET=sk_test_51T7vgH92VuKVcTwyzusezOGOIRZ7fnclnCWy5IbV40B4XmFLtUjZvKpet
STRIPE_WEBHOOK_SECRET=whsec_06bf2f8a539849106c1fa2b7d22faab8169ddda33ad0f510aff6
```

You can get these keys from:

Stripe Dashboard → Developers → API Keys

<img width="1906" height="947" alt="Screenshot 2026-03-05 165555" src="https://github.com/user-attachments/assets/a8d6c0b9-6b5d-42f3-95da-2c604a0deddd" />

---

# 6. Create Order Model

Create a model with migration.

```
php artisan make:model Order -m
```

---

# 7. Update Migration

Open:

```
database/migrations/create_orders_table.php
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->integer('amount');
            $table->string('stripe_session_id')->nullable();
            $table->string('payment_status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
```

Run migration:

```
php artisan migrate
```

---

# 8. Update Order Model

Open:

```
app/Models/Order.php
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'product_name',
        'amount',
        'stripe_session_id',
        'payment_status'
    ];
}
```

---

# 9. Create Payment Controller

Create controller.

```
php artisan make:controller PaymentController
```

File:

```
app/Http/Controllers/PaymentController.php
```

```php
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
```

---

# 10. Create Webhook Job

Create a job that will handle the Stripe webhook event.

Run the command:

```
php artisan make:job HandleCheckoutSessionCompleted
```

Open:

```
app/Jobs/HandleCheckoutSessionCompleted.php
```

```php
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
```

Explanation
This job processes the Stripe webhook event when a checkout session is completed.

---

# 11. Configure Routes

Open:

```
routes/web.php
```

```php
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
```

---

# 12. Disable CSRF for Webhook (Laravel 12)

Open:

```
bootstrap/app.php
```

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
```

---

# 13. Install Stripe CLI

Download Stripe CLI from:

```
https://stripe.com/docs/stripe-cli
```

Install using winget (Windows):

```
winget install Stripe.StripeCLI
```

Check installation:

```
stripe --version
```

---

# 14. Login to Stripe CLI

Run:

```
stripe login
```

A browser will open for authentication.

<img width="1286" height="957" alt="Screenshot 2026-03-06 160103" src="https://github.com/user-attachments/assets/8ef8779b-ee88-41c7-a598-042f3d53a9fe" />

---

# 15. Start Webhook Listener

Run:

```
stripe listen --forward-to http://127.0.0.1:8000/stripe/webhook
```

<img width="1279" height="90" alt="Screenshot 2026-03-06 170632" src="https://github.com/user-attachments/assets/0ea3e195-c715-4417-ab72-13f8169e01e6" />

Add the generated webhook secret to `.env`.

---

# 16. Test Webhook Event

Open a new terminal.

Run:

```
stripe trigger checkout.session.completed
```

Expected output:

```
POST /stripe/webhook
200 OK
```
<img width="1680" height="388" alt="Screenshot 2026-03-06 164422" src="https://github.com/user-attachments/assets/4ed915c6-6f0d-4d91-9768-a1a0f8da80f5" />

---

# 17. Test Checkout Payment

Open browser:

```
http://127.0.0.1:8000/checkout
```

Stripe checkout page will open.

Use test card:

```
Card Number: 4242 4242 4242 4242
Expiry: Any future date
CVC: Any 3 digits
```

<img width="1156" height="928" alt="Screenshot 2026-03-06 163255" src="https://github.com/user-attachments/assets/1e4e5090-b678-4f3a-ae07-a04b51e8108e" />


After completing the payment through Stripe Checkout, the user is redirected to the success page.

```
http://127.0.0.1:8000/success
```

The application displays the following message:

```
Payment Successful
```
<img width="383" height="111" alt="Screenshot 2026-03-06 163315" src="https://github.com/user-attachments/assets/fa0e8073-5da8-4eda-9ac2-e24757499e90" />

---

# 18. Payment Flow

```
User opens /checkout
        ↓
Stripe Checkout Page
        ↓
User completes payment
        ↓
Stripe sends webhook event
        ↓
Laravel receives webhook
        ↓
Webhook processed successfully
```

---

# Final Result

The system now supports:

• Stripe Checkout
• Payment processing
• Webhook events
• Laravel backend handling

This completes the Laravel 12 Stripe Checkout and Webhook Integration.

---

# Technologies Used

• Laravel 12
• Stripe Checkout
• Stripe CLI
• Stripe Webhooks

