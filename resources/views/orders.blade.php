<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Payment Orders</title>

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

        .session-id {
            max-width: 200px;
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

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <h3 class="fw-bold mb-1">
                        Payment Orders
                    </h3>

                    <p class="text-muted mb-0">
                        Search and filter Stripe payment orders.
                    </p>

                </div>

                <a
                    href="{{ route('checkout') }}"
                    class="btn btn-primary"
                >
                    New Checkout
                </a>

            </div>

            <!-- Search & Filters -->

            <form
                method="GET"
                action="{{ route('orders') }}"
                class="row g-3 mb-4"
            >

                <div class="col-md-4">

                    <label class="form-label">
                        Search
                    </label>

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Order ID, product or Stripe session..."
                        value="{{ request('search') }}"
                    >

                </div>

                <div class="col-md-2">

                    <label class="form-label">
                        Status
                    </label>

                    <select
                        name="status"
                        class="form-select"
                    >

                        <option value="">
                            All Statuses
                        </option>

                        <option
                            value="paid"
                            {{ request('status') === 'paid' ? 'selected' : '' }}
                        >
                            Paid
                        </option>

                        <option
                            value="pending"
                            {{ request('status') === 'pending' ? 'selected' : '' }}
                        >
                            Pending
                        </option>

                        <option
                            value="cancelled"
                            {{ request('status') === 'cancelled' ? 'selected' : '' }}
                        >
                            Cancelled
                        </option>

                    </select>

                </div>

                <div class="col-md-2">

                    <label class="form-label">
                        From Date
                    </label>

                    <input
                        type="date"
                        name="from_date"
                        class="form-control"
                        value="{{ request('from_date') }}"
                    >

                </div>

                <div class="col-md-2">

                    <label class="form-label">
                        To Date
                    </label>

                    <input
                        type="date"
                        name="to_date"
                        class="form-control"
                        value="{{ request('to_date') }}"
                    >

                </div>

                <div class="col-md-2 d-flex align-items-end">

                    <button
                        type="submit"
                        class="btn btn-dark w-100"
                    >
                        Search
                    </button>

                </div>

            </form>

            <div class="mb-3">

                <a
                    href="{{ route('orders') }}"
                    class="btn btn-sm btn-outline-secondary"
                >
                    Clear Filters
                </a>

            </div>

            <!-- Orders -->

            @if($orders->count())

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-light">

                            <tr>

                                <th>ID</th>
                                <th>Product</th>
                                <th>Amount</th>
                                <th>Stripe Session</th>
                                <th>Status</th>
                                <th>Created</th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach($orders as $order)

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

                                            <div
                                                class="session-id"
                                                title="{{ $order->stripe_session_id }}"
                                            >
                                                {{ $order->stripe_session_id }}
                                            </div>

                                        @else

                                            <span class="text-muted">
                                                -
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

                <!-- Pagination -->

                <div class="mt-4">

                    {{ $orders->links() }}

                </div>

            @else

                <div class="alert alert-info text-center">
                    No payment orders found.
                </div>

            @endif

        </div>

    </div>

</div>

</body>

</html>