<?php

return [

    'signing_secret' => env('STRIPE_WEBHOOK_SECRET'),

    'jobs' => [
        'checkout.session.completed' =>
            \App\Jobs\HandleCheckoutSessionCompleted::class,
    ],

    'profile' =>
        \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,

    'verify_signature' => env(
        'STRIPE_SIGNATURE_VERIFY',
        true
    ),

];