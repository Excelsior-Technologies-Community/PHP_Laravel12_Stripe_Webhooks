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
            font-size: 28px;
            font-weight: 700;
        }

        .section-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,.07);
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

        <p class="text-muted">
            Monitor Stripe payments and webhook activity.
        </p>

    </div>

    <div class="row g-4 mb-4">

        @foreach([
            ['Total Orders', $totalOrders, 'dark'],
            ['Paid Orders', $paidOrders, 'success'],
            ['Pending Orders', $pendingOrders, 'warning'],
            ['Cancelled Orders', $cancelledOrders, 'secondary'],
            ['Failed Orders', $failedOrders, 'danger'],
            ['Refunded Orders', $refundedOrders, 'info'],
            ['Processed Webhooks', $processedWebhooks, 'success'],
            ['Failed Webhooks', $failedWebhooks, 'danger'],
        ] as $stat)

            <div class="col-md-3">

                <div class="card stat-card p-3">

                    <div class="text-muted">
                        {{ $stat[0] }}
                    </div>

                    <div class="stat-number text-{{ $stat[2] }}">
                        {{ $stat[1] }}
                    </div>

                </div>

            </div>

        @endforeach

    </div>

    <div class="row g-4 mb-4">

        <div class="col-md-4">

            <div class="card stat-card p-4">

                <div class="text-muted">
                    Total Revenue
                </div>

                <div class="stat-number text-success">
                    ${{ number_format($totalRevenue / 100, 2) }}
                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card stat-card p-4">

                <div class="text-muted">
                    Average Paid Order
                </div>

                <div class="stat-number">
                    ${{ number_format(($averageOrderValue ?? 0) / 100, 2) }}
                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card stat-card p-4">

                <div class="text-muted">
                    Unmatched Webhooks
                </div>

                <div class="stat-number text-secondary">
                    {{ $unmatchedWebhooks }}
                </div>

            </div>

        </div>

    </div>

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
                                <th>Customer</th>
                                <th>Amount</th>
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
                                        {{ $order->customer_email ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $order->formattedAmount() }}
                                    </td>

                                    <td>

                                        <span
                                            class="badge bg-{{ $order->statusClass() }}"
                                        >
                                            {{ ucfirst($order->payment_status) }}
                                        </span>

                                    </td>

                                    <td>

                                        <a
                                            href="{{ route('orders.details', $order) }}"
                                            class="btn btn-sm btn-outline-dark"
                                        >
                                            Details
                                        </a>

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

    <div class="card section-card">

        <div class="card-header bg-white py-3">

            <div class="d-flex justify-content-between">

                <h5 class="mb-0">
                    Recent Webhooks
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
                                <th>Payment</th>
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

                                        <span
                                            class="badge bg-{{ $webhook->statusClass() }}"
                                        >
                                            {{ ucfirst($webhook->status) }}
                                        </span>

                                    </td>

                                    <td>
                                        {{ $webhook->payment_status ?? '-' }}
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