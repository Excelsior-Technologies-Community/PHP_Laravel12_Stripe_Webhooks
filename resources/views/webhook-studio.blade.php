<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Webhook Live Replay Simulator & Signature Inspector</title>

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

        .studio-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .sig-box {
            background: #0f172a;
            color: #38bdf8;
            font-family: 'Courier New', Courier, monospace;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            word-break: break-all;
        }

        .table td, .table th {
            vertical-align: middle;
        }
    </style>
</head>

<body>

    <div class="container-fluid py-4">

        {{-- NAVIGATION HEADER --}}
        <div class="studio-header d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <span class="fs-2 text-warning">⚡</span>
                <div>
                    <h3 class="fw-bold mb-0 text-white">Webhook Live Replay Simulator & Signature Inspector</h3>
                    <p class="mb-0 text-slate-300 small">Re-trigger Stripe webhook events, verify signatures & inspect raw payloads</p>
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
                <a href="{{ route('webhook.studio') }}" class="btn btn-warning btn-sm fw-bold">
                    <i class="bi bi-lightning-fill"></i> Webhook Studio
                </a>
                <a href="{{ route('revenue.analytics') }}" class="btn btn-outline-info btn-sm">
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

        <div class="row g-4 mb-4">
            {{-- LEFT PANEL: SIMULATE STRIPE WEBHOOK EVENT FORM --}}
            <div class="col-lg-6">
                <div class="studio-card p-4 h-100">
                    <h5 class="fw-bold mb-3 border-bottom pb-2 text-dark">
                        <i class="bi bi-play-circle-fill text-primary me-2"></i> Stripe Webhook Event Simulator
                    </h5>

                    <form action="{{ route('webhook.simulate') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Stripe Event Type</label>
                                <select class="form-select" name="event_type" required>
                                    @foreach($supportedEvents as $type => $label)
                                    <option value="{{ $type }}">{{ $label }} ({{ $type }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Customer Email</label>
                                <input type="email" class="form-control" name="customer_email" value="customer.test@example.com" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Transaction Amount ($)</label>
                                <input type="number" class="form-control" name="amount" value="49.99" step="0.01" min="1" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Generated Stripe-Signature Key</label>
                                <div class="sig-box">
                                    t={{ time() }},v1={{ bin2hex(random_bytes(16)) }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 shadow">
                                <i class="bi bi-send-fill me-1"></i> Dispatch Simulated Webhook
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- RIGHT PANEL: STRIPE SIGNATURE INSPECTOR & DEBUGGER --}}
            <div class="col-lg-6">
                <div class="studio-card p-4 h-100">
                    <h5 class="fw-bold mb-3 border-bottom pb-2 text-dark">
                        <i class="bi bi-shield-lock-fill text-success me-2"></i> Stripe Signature & Header Debugger
                    </h5>

                    <div class="mb-3">
                        <small class="text-muted fw-semibold">Webhook Secret Key (whsec):</small>
                        <div class="input-group mt-1">
                            <input type="text" class="form-control font-monospace small" value="{{ $signatureSecret }}" readonly id="secretInput">
                            <button class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText('{{ $signatureSecret }}')">
                                <i class="bi bi-clipboard"></i> Copy
                            </button>
                        </div>
                    </div>

                    <div class="bg-light p-3 rounded-3 border mb-3">
                        <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-check-all text-success"></i> Signature Verification Rules:</h6>
                        <ul class="small mb-0 text-secondary ps-3">
                            <li>Timestamp tolerance: <strong>300 seconds</strong> (Replay Attack Prevention)</li>
                            <li>HMAC-SHA256 signature matching verified</li>
                            <li>Header payload format: <code>t={timestamp},v1={hash}</code></li>
                        </ul>
                    </div>

                    <div class="d-flex align-items-center justify-content-between p-3 bg-success-subtle text-success rounded-3">
                        <div>
                            <i class="bi bi-shield-check fs-4 me-2"></i>
                            <strong class="align-middle">Signature Verification Status: ACTIVE & VERIFIED</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RECENT WEBHOOK EVENTS & REPLAY ENGINE TABLE --}}
        <div class="row">
            <div class="col-12">
                <div class="studio-card p-4">
                    <h5 class="fw-bold mb-3 text-dark">
                        <i class="bi bi-arrow-repeat text-warning me-2"></i> Webhook Event Replay & Retry Engine
                    </h5>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Event ID</th>
                                    <th>Event Type</th>
                                    <th>Status</th>
                                    <th>Simulated</th>
                                    <th>Retries</th>
                                    <th>Dispatched At</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($webhookEvents as $event)
                                <tr>
                                    <td class="font-monospace fw-bold">{{ $event->event_id }}</td>
                                    <td>
                                        <span class="badge bg-secondary font-monospace">{{ $event->event_type }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $event->statusClass() }} px-2 py-1">
                                            {{ strtoupper($event->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($event->is_simulated)
                                        <span class="badge bg-warning text-dark"><i class="bi bi-cpu"></i> Sandbox</span>
                                        @else
                                        <span class="badge bg-light text-dark border">Live Stripe</span>
                                        @endif
                                    </td>
                                    <td><span class="fw-bold">{{ $event->retry_count ?? $event->attempts }}</span></td>
                                    <td class="small text-muted">{{ $event->created_at ? $event->created_at->format('d M Y H:i:s') : 'N/A' }}</td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="{{ route('webhook.details', $event->id) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-eye"></i> Details
                                            </a>
                                            <a href="{{ route('webhook.download', $event->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-download"></i> JSON
                                            </a>
                                            <form action="{{ route('webhook.replay', $event->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-arrow-clockwise"></i> Replay Event
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No webhook events logged yet. Dispatch a simulated event above to begin testing.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $webhookEvents->links() }}
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
