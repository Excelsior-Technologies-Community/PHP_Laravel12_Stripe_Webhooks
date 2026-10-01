<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Real-Time Revenue Analytics & Subscription Churn Radar</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1e293b;
        }

        .studio-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #334155 100%);
            border-radius: 16px;
            padding: 24px;
            color: #ffffff;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
        }

        .metric-card {
            border: none;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease;
        }

        .metric-card:hover {
            transform: translateY(-3px);
        }

        .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .card-custom {
            border: none;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .progress-bar-custom {
            height: 10px;
            border-radius: 6px;
        }
    </style>
</head>

<body>

    <div class="container-fluid py-4">

        {{-- NAVIGATION HEADER --}}
        <div class="studio-header d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <span class="fs-2 text-info">📈</span>
                <div>
                    <h3 class="fw-bold mb-0 text-white">Real-Time Revenue Analytics & Subscription Churn Radar</h3>
                    <p class="mb-0 text-slate-300 small">Stripe revenue tracking, webhook event breakdown & failed payment recovery bot</p>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="{{ route('orders') }}" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-cart"></i> Orders
                </a>
                <a href="{{ route('webhook.events') }}" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-journal-text"></i> Webhooks
                </a>
                <a href="{{ route('webhook.studio') }}" class="btn btn-outline-warning btn-sm">
                    <i class="bi bi-lightning-fill"></i> Webhook Studio
                </a>
                <a href="{{ route('revenue.analytics') }}" class="btn btn-info btn-sm fw-bold">
                    <i class="bi bi-graph-up-arrow"></i> Revenue Analytics
                </a>
            </div>
        </div>

        {{-- ALERTS --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show rounded-3 shadow-sm mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{-- METRIC STAT CARDS --}}
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card metric-card p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-semibold small">Monthly Recurring (MRR)</span>
                            <h2 class="fw-bold mb-0 mt-1 text-success">${{ number_format($mrr, 2) }}</h2>
                        </div>
                        <div class="icon-box bg-success-subtle text-success">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card metric-card p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-semibold small">Annual Run-Rate (ARR)</span>
                            <h2 class="fw-bold mb-0 mt-1 text-primary">${{ number_format($arr, 2) }}</h2>
                        </div>
                        <div class="icon-box bg-primary-subtle text-primary">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card metric-card p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-semibold small">Failed Payment Revenue</span>
                            <h2 class="fw-bold mb-0 mt-1 text-danger">${{ number_format($failedRevenue, 2) }}</h2>
                        </div>
                        <div class="icon-box bg-danger-subtle text-danger">
                            <i class="bi bi-x-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card metric-card p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-semibold small">Subscription Churn Radar</span>
                            <h2 class="fw-bold mb-0 mt-1 text-warning">{{ $churnRate }}%</h2>
                        </div>
                        <div class="icon-box bg-warning-subtle text-warning">
                            <i class="bi bi-radar"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- EVENT BREAKDOWN & RECOVERY BOT --}}
        <div class="row g-4 mb-4">
            {{-- EVENT TYPE DISTRIBUTION --}}
            <div class="col-lg-6">
                <div class="card card-custom p-4 h-100">
                    <h5 class="fw-bold mb-3 text-dark">
                        <i class="bi bi-pie-chart-fill text-primary me-2"></i> Webhook Event Breakdown & Distribution
                    </h5>

                    <div class="d-flex flex-column gap-3">
                        <div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-semibold">Charge Succeeded (Payments)</span>
                                <span class="fw-bold text-success">{{ $eventBreakdown['charge.succeeded'] }} events</span>
                            </div>
                            <div class="progress progress-bar-custom">
                                <div class="progress-bar bg-success" style="width: {{ $totalWebhooks > 0 ? round(($eventBreakdown['charge.succeeded']/$totalWebhooks)*100) : 0 }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-semibold">Checkout Session Completed</span>
                                <span class="fw-bold text-primary">{{ $eventBreakdown['checkout.session.completed'] }} events</span>
                            </div>
                            <div class="progress progress-bar-custom">
                                <div class="progress-bar bg-primary" style="width: {{ $totalWebhooks > 0 ? round(($eventBreakdown['checkout.session.completed']/$totalWebhooks)*100) : 0 }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-semibold">Customer Subscriptions Created</span>
                                <span class="fw-bold text-info">{{ $eventBreakdown['customer.subscription.created'] }} events</span>
                            </div>
                            <div class="progress progress-bar-custom">
                                <div class="progress-bar bg-info" style="width: {{ $totalWebhooks > 0 ? round(($eventBreakdown['customer.subscription.created']/$totalWebhooks)*100) : 0 }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-semibold">Payment Intent Failed</span>
                                <span class="fw-bold text-danger">{{ $eventBreakdown['payment_intent.payment_failed'] }} events</span>
                            </div>
                            <div class="progress progress-bar-custom">
                                <div class="progress-bar bg-danger" style="width: {{ $totalWebhooks > 0 ? round(($eventBreakdown['payment_intent.payment_failed']/$totalWebhooks)*100) : 0 }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-semibold">Invoice Payment Failed (Renewal Error)</span>
                                <span class="fw-bold text-warning">{{ $eventBreakdown['invoice.payment_failed'] }} events</span>
                            </div>
                            <div class="progress progress-bar-custom">
                                <div class="progress-bar bg-warning" style="width: {{ $totalWebhooks > 0 ? round(($eventBreakdown['invoice.payment_failed']/$totalWebhooks)*100) : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- AUTOMATED FAILED PAYMENT RECOVERY BOT --}}
            <div class="col-lg-6">
                <div class="card card-custom p-4 h-100">
                    <h5 class="fw-bold mb-3 text-dark">
                        <i class="bi bi-robot text-danger me-2"></i> Automated Failed Payment Recovery Bot
                    </h5>

                    <div class="bg-light p-3 rounded-3 border mb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Bot Status: ACTIVE (Smart Dunning Engine)</h6>
                                <small class="text-muted">Auto-sends card update links & retry triggers to customers with failed payments</small>
                            </div>
                            <span class="badge bg-success">ONLINE</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Failure Reason</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($failedOrders as $fo)
                                <tr>
                                    <td class="small">{{ $fo->customer_email ?? 'customer@example.com' }}</td>
                                    <td class="fw-bold text-danger">${{ number_format($fo->amount / 100, 2) }}</td>
                                    <td><span class="badge bg-danger text-wrap">{{ $fo->failure_reason ?? 'Card Declined' }}</span></td>
                                    <td class="text-end">
                                        <form action="{{ route('revenue.triggerRecovery') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="customer_email" value="{{ $fo->customer_email }}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                🤖 Send Link
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No failed payments detected. All customer charges are healthy!</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
