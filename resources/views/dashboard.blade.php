<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Stripe Payment Dashboard</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            background: #f5f7fb;
        }

        .navbar-brand {
            font-weight: 700;
        }

        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,.07);
        }

        .stat-number {
            font-size: 30px;
            font-weight: 700;
        }

        .section-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,.07);
        }

        .table th {
            white-space: nowrap;
        }

        .session-id {
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

    </style>

</head>

<body>

<nav class="navbar navbar-dark bg-dark">

    <div class="container">

        <a
            class="navbar-brand"
            href="{{ route('dashboard') }}"
        >
            Stripe Payment Dashboard
        </a>

        <div>

            <a
                href="{{ route('orders') }}"
                class="btn btn-outline-light btn-sm me-2"
            >
                Orders
            </a>

            <a
                href="{{ route('webhook.events') }}"
                class="btn btn-outline-light btn-sm me-2"
            >
                Webhooks
            </a>

            <a
                href="{{ route('checkout') }}"
                class="btn btn-primary btn-sm"
            >
                Test Checkout
            </a>

        </div>

    </div>

</nav>

<div class="container py-4">

    <div class="mb-4">

        <h2 class="fw-bold">
            Payment Overview
        </h2>

        <p class="text-muted mb-0">
            Monitor Stripe payments and webhook activity.
        </p>

    </div>

    <!-- Payment Statistics -->

    <div class="row g-4 mb-4">

        <div class="col-md-3">

            <div class="card stat-card p-3">

                <div class="text-muted">
                    Total Orders
                </div>

                <div class="stat-number">
                    {{ $totalOrders }}
                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card stat-card p-3">

                <div class="text-muted">
                    Paid Orders
                </div>

                <div class="stat-number text-success">
                    {{ $paidOrders }}
                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card stat-card p-3">

                <div class="text-muted">
                    Pending Orders
                </div>

                <div class="stat-number text-warning">
                    {{ $pendingOrders }}
                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card stat-card p-3">

                <div class="text-muted">
                    Cancelled Orders
                </div>

                <div class="stat-number text-danger">
                    {{ $cancelledOrders }}
                </div>

            </div>

        </div>

    </div>

    <!-- Revenue & Webhook Statistics -->

    <div class="row g-4 mb-4">

        <div class="col-md-3">

            <div class="card stat-card p-3">

                <div class="text-muted">
                    Total Revenue
                </div>

                <div class="stat-number">
                    ${{ number_format($totalRevenue / 100, 2) }}
                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card stat-card p-3">

                <div class="text-muted">
                    Total Webhooks
                </div>

                <div class="stat-number">
                    {{ $totalWebhooks }}
                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card stat-card p-3">

                <div class="text-muted">
                    Processed Webhooks
                </div>

                <div class="stat-number text-success">
                    {{ $processedWebhooks }}
                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card stat-card p-3">

                <div class="text-muted">
                    Failed Webhooks
                </div>

                <div class="stat-number text-danger">
                    {{ $failedWebhooks }}
                </div>

            </div>

        </div>

    </div>

    <!-- Recent Orders -->

    <div class="card section-card mb-4">

        <div class="card-header bg-white py-3">

            <div class="d-flex justify-content-between">

                <h5 class="mb-0">
                    Recent Orders
                </h5>

                <a
                    href="{{ route('orders') }}"
                    class="btn btn-sm btn-dark"
                >
                    View All
                </a>

            </div>

        </div>

        <div class="card-body">

            @if($recentOrders->count())

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>

                            <tr>

                                <th>ID</th>
                                <th>Product</th>
                                <th>Amount</th>
                                <th>Stripe Session</th>
                                <th>Status</th>
                                <th>Date</th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach($recentOrders as $order)

                                <tr>

                                    <td>
                                        #{{ $order->id }}
                                    </td>

                                    <td>
                                        {{ $order->product_name }}
                                    </td>

                                    <td>
                                        ${{ number_format($order->amount / 100, 2) }}
                                    </td>

                                    <td>

                                        @if($order->stripe_session_id)

                                            <div class="session-id">
                                                {{ $order->stripe_session_id }}
                                            </div>

                                        @else

                                            <span class="text-muted">
                                                Not available
                                            </span>

                                        @endif

                                    </td>

                                    <td>

                                        @if($order->payment_status === 'paid')

                                            <span class="badge bg-success">
                                                Paid
                                            </span>

                                        @elseif($order->payment_status === 'cancelled')

                                            <span class="badge bg-danger">
                                                Cancelled
                                            </span>

                                        @else

                                            <span class="badge bg-warning text-dark">
                                                Pending
                                            </span>

                                        @endif

                                    </td>

                                    <td>
                                        {{ $order->created_at->format('d M Y H:i') }}
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="text-center text-muted py-4">
                    No orders found.
                </div>

            @endif

        </div>

    </div>

    <!-- Recent Webhooks -->

    <div class="card section-card">

        <div class="card-header bg-white py-3">

            <div class="d-flex justify-content-between">

                <h5 class="mb-0">
                    Recent Webhook Events
                </h5>

                <a
                    href="{{ route('webhook.events') }}"
                    class="btn btn-sm btn-dark"
                >
                    View All
                </a>

            </div>

        </div>

        <div class="card-body">

            @if($recentWebhooks->count())

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>

                            <tr>

                                <th>Event</th>
                                <th>Status</th>
                                <th>Session ID</th>
                                <th>Received</th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach($recentWebhooks as $webhook)

                                <tr>

                                    <td>
                                        {{ $webhook->event_type }}
                                    </td>

                                    <td>

                                        @if($webhook->status === 'processed')

                                            <span class="badge bg-success">
                                                Processed
                                            </span>

                                        @elseif($webhook->status === 'failed')

                                            <span class="badge bg-danger">
                                                Failed
                                            </span>

                                        @else

                                            <span class="badge bg-warning text-dark">
                                                Received
                                            </span>

                                        @endif

                                    </td>

                                    <td>

                                        <div class="session-id">
                                            {{ $webhook->stripe_session_id ?? '-' }}
                                        </div>

                                    </td>

                                    <td>
                                        {{ $webhook->created_at->format('d M Y H:i') }}
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="text-center text-muted py-4">
                    No webhook events found.
                </div>

            @endif

        </div>

    </div>

</div>

</body>

</html>