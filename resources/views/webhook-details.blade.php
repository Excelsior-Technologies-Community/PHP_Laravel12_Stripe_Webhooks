<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Webhook Event Details</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            background: #f5f7fb;
        }

        .main-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,.07);
        }

        pre {
            background: #212529;
            color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            max-height: 600px;
            overflow: auto;
        }

    </style>

</head>

<body>

<nav class="navbar navbar-dark bg-dark">

    <div class="container">

        <a
            href="{{ route('dashboard') }}"
            class="navbar-brand"
        >
            Stripe Dashboard
        </a>

        <a
            href="{{ route('webhook.events') }}"
            class="btn btn-outline-light btn-sm"
        >
            Webhook History
        </a>

    </div>

</nav>

<div class="container py-4">

    <div class="card main-card">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <h3 class="fw-bold">
                        Webhook Event Details
                    </h3>

                    <p class="text-muted mb-0">
                        Detailed Stripe webhook information.
                    </p>

                </div>

                <a
                    href="{{ route('webhook.events') }}"
                    class="btn btn-secondary"
                >
                    Back
                </a>

            </div>

            <div class="row g-4 mb-4">

                <div class="col-md-6">

                    <div class="border rounded p-3">

                        <small class="text-muted">
                            Event ID
                        </small>

                        <div class="fw-semibold text-break">
                            {{ $webhookEvent->event_id }}
                        </div>

                    </div>

                </div>

                <div class="col-md-6">

                    <div class="border rounded p-3">

                        <small class="text-muted">
                            Event Type
                        </small>

                        <div class="fw-semibold">
                            {{ $webhookEvent->event_type }}
                        </div>

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="border rounded p-3">

                        <small class="text-muted">
                            Processing Status
                        </small>

                        <div class="mt-1">

                            @if($webhookEvent->status === 'processed')

                                <span class="badge bg-success">
                                    Processed
                                </span>

                            @elseif($webhookEvent->status === 'failed')

                                <span class="badge bg-danger">
                                    Failed
                                </span>

                            @else

                                <span class="badge bg-warning text-dark">
                                    Received
                                </span>

                            @endif

                        </div>

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="border rounded p-3">

                        <small class="text-muted">
                            Payment Status
                        </small>

                        <div class="mt-1">

                            @if($webhookEvent->payment_status === 'paid')

                                <span class="badge bg-success">
                                    Paid
                                </span>

                            @else

                                <span class="text-muted">
                                    Not available
                                </span>

                            @endif

                        </div>

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="border rounded p-3">

                        <small class="text-muted">
                            Processed At
                        </small>

                        <div class="fw-semibold">

                            @if($webhookEvent->processed_at)

                                {{ $webhookEvent->processed_at->format('d M Y H:i:s') }}

                            @else

                                <span class="text-muted">
                                    Not processed
                                </span>

                            @endif

                        </div>

                    </div>

                </div>

            </div>

            <div class="mb-4">

                <h5 class="fw-bold">
                    Stripe Session ID
                </h5>

                <div class="border rounded p-3 text-break">

                    {{ $webhookEvent->stripe_session_id ?? 'Not available' }}

                </div>

            </div>

            <div>

                <h5 class="fw-bold mb-3">
                    Webhook Payload
                </h5>

                <pre>{{ json_encode($webhookEvent->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>

            </div>

        </div>

    </div>

</div>

</body>

</html>