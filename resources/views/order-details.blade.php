<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Order #{{ $order->id }}</title>

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

        .info-box {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 18px;
            height: 100%;
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
            href="{{ route('orders') }}"
            class="btn btn-outline-light btn-sm"
        >
            Orders
        </a>

    </div>

</nav>

<div class="container py-4">

    <div class="card main-card">

        <div class="card-body p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <h3 class="fw-bold mb-1">
                        Order #{{ $order->id }}
                    </h3>

                    <p class="text-muted mb-0">
                        Complete Stripe payment information.
                    </p>

                </div>

                <span
                    class="badge bg-{{ $order->statusClass() }} fs-6"
                >
                    {{ ucfirst($order->payment_status) }}
                </span>

            </div>

            <div class="row g-4">

                <div class="col-md-4">

                    <div class="info-box">

                        <small class="text-muted">
                            Product
                        </small>

                        <h5 class="mt-2">
                            {{ $order->product_name }}
                        </h5>

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="info-box">

                        <small class="text-muted">
                            Amount
                        </small>

                        <h5 class="mt-2">
                            {{ $order->formattedAmount() }}
                        </h5>

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="info-box">

                        <small class="text-muted">
                            Customer Email
                        </small>

                        <h5 class="mt-2 text-break">
                            {{ $order->customer_email ?? 'Not available' }}
                        </h5>

                    </div>

                </div>

                <div class="col-md-6">

                    <div class="info-box">

                        <small class="text-muted">
                            Stripe Checkout Session
                        </small>

                        <div class="mt-2 text-break">
                            {{ $order->stripe_session_id ?? 'Not available' }}
                        </div>

                    </div>

                </div>

                <div class="col-md-6">

                    <div class="info-box">

                        <small class="text-muted">
                            Payment Intent
                        </small>

                        <div class="mt-2 text-break">
                            {{ $order->payment_intent_id ?? 'Not available' }}
                        </div>

                    </div>

                </div>

                <div class="col-md-6">

                    <div class="info-box">

                        <small class="text-muted">
                            Paid At
                        </small>

                        <div class="mt-2">

                            @if($order->paid_at)

                                {{ $order->paid_at->format('d M Y H:i:s') }}

                            @else

                                Not paid

                            @endif

                        </div>

                    </div>

                </div>

                <div class="col-md-6">

                    <div class="info-box">

                        <small class="text-muted">
                            Created At
                        </small>

                        <div class="mt-2">
                            {{ $order->created_at->format('d M Y H:i:s') }}
                        </div>

                    </div>

                </div>

                @if($order->failure_reason)

                    <div class="col-12">

                        <div class="alert alert-danger">

                            <strong>
                                Payment Failure Reason:
                            </strong>

                            {{ $order->failure_reason }}

                        </div>

                    </div>

                @endif

            </div>

            <div class="mt-4">

                <a
                    href="{{ route('orders') }}"
                    class="btn btn-dark"
                >
                    Back to Orders
                </a>

            </div>

        </div>

    </div>

</div>

</body>

</html>