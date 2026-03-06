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