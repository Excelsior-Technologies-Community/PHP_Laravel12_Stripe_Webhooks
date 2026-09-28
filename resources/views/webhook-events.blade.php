<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Stripe Webhook Events</title>

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

        .event-id {
            max-width: 230px;
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
            href="{{ route('dashboard') }}"
            class="navbar-brand"
        >
            Stripe Dashboard
        </a>

        <a
            href="{{ route('dashboard') }}"
            class="btn btn-outline-light btn-sm"
        >
            Dashboard
        </a>

    </div>

</nav>

<div class="container py-4">

    <div class="card main-card">

        <div class="card-body">

            <div class="mb-4">

                <h3 class="fw-bold mb-1">
                    Webhook Event History
                </h3>

                <p class="text-muted mb-0">
                    View and inspect Stripe webhook events received by Laravel.
                </p>

            </div>

            <!-- Filters -->

            <form
                method="GET"
                action="{{ route('webhook.events') }}"
                class="row g-3 mb-4"
            >

                <div class="col-md-5">

                    <label class="form-label">
                        Search
                    </label>

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Event ID, event type or session ID..."
                        value="{{ request('search') }}"
                    >

                </div>

                <div class="col-md-3">

                    <label class="form-label">
                        Processing Status
                    </label>

                    <select
                        name="status"
                        class="form-select"
                    >

                        <option value="">
                            All Statuses
                        </option>

                        <option
                            value="received"
                            {{ request('status') === 'received' ? 'selected' : '' }}
                        >
                            Received
                        </option>

                        <option
                            value="processed"
                            {{ request('status') === 'processed' ? 'selected' : '' }}
                        >
                            Processed
                        </option>

                        <option
                            value="failed"
                            {{ request('status') === 'failed' ? 'selected' : '' }}
                        >
                            Failed
                        </option>

                    </select>

                </div>

                <div class="col-md-2 d-flex align-items-end">

                    <button
                        type="submit"
                        class="btn btn-dark w-100"
                    >
                        Search
                    </button>

                </div>

                <div class="col-md-2 d-flex align-items-end">

                    <a
                        href="{{ route('webhook.events') }}"
                        class="btn btn-outline-secondary w-100"
                    >
                        Clear
                    </a>

                </div>

            </form>

            @if($webhookEvents->count())

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-light">

                            <tr>

                                <th>Event ID</th>
                                <th>Event Type</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Received</th>
                                <th>Action</th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach($webhookEvents as $event)

                                <tr>

                                    <td>

                                        <div
                                            class="event-id"
                                            title="{{ $event->event_id }}"
                                        >
                                            {{ $event->event_id }}
                                        </div>

                                    </td>

                                    <td>
                                        {{ $event->event_type }}
                                    </td>

                                    <td>

                                        @if($event->status === 'processed')

                                            <span class="badge bg-success">
                                                Processed
                                            </span>

                                        @elseif($event->status === 'failed')

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

                                        @if($event->payment_status === 'paid')

                                            <span class="badge bg-success">
                                                Paid
                                            </span>

                                        @else

                                            <span class="text-muted">
                                                -
                                            </span>

                                        @endif

                                    </td>

                                    <td>
                                        {{ $event->created_at->format('d M Y H:i:s') }}
                                    </td>

                                    <td>

                                        <a
                                            href="{{ route('webhook.details', $event) }}"
                                            class="btn btn-sm btn-outline-dark"
                                        >
                                            View Details
                                        </a>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

                <div class="mt-4">

                    {{ $webhookEvents->links() }}

                </div>

            @else

                <div class="alert alert-info text-center">
                    No webhook events found.
                </div>

            @endif

        </div>

    </div>

</div>

</body>

</html>