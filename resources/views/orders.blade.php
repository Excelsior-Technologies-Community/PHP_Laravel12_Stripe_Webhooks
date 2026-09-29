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
                        Search, filter, sort and export Stripe orders.
                    </p>

                </div>

                <div>

                    <a
                        href="{{ route('orders.export', request()->query()) }}"
                        class="btn btn-success me-2"
                    >
                        Export CSV
                    </a>

                    <a
                        href="{{ route('checkout') }}"
                        class="btn btn-primary"
                    >
                        New Checkout
                    </a>

                </div>

            </div>

            <form
                method="GET"
                action="{{ route('orders') }}"
                class="row g-3 mb-4"
            >

                <div class="col-md-3">

                    <label class="form-label">
                        Search
                    </label>

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="ID, product, email..."
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
                            All
                        </option>

                        @foreach([
                            'paid' => 'Paid',
                            'pending' => 'Pending',
                            'cancelled' => 'Cancelled',
                            'failed' => 'Failed',
                            'refunded' => 'Refunded',
                        ] as $value => $label)

                            <option
                                value="{{ $value }}"
                                {{ request('status') === $value ? 'selected' : '' }}
                            >
                                {{ $label }}
                            </option>

                        @endforeach

                    </select>

                </div>

                <div class="col-md-2">

                    <label class="form-label">
                        Min Amount
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="min_amount"
                        class="form-control"
                        placeholder="0.00"
                        value="{{ request('min_amount') }}"
                    >

                </div>

                <div class="col-md-2">

                    <label class="form-label">
                        Max Amount
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="max_amount"
                        class="form-control"
                        placeholder="1000.00"
                        value="{{ request('max_amount') }}"
                    >

                </div>

                <div class="col-md-3">

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

                <div class="col-md-3">

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

                <div class="col-md-3">

                    <label class="form-label">
                        Sort By
                    </label>

                    <select
                        name="sort"
                        class="form-select"
                    >

                        @foreach([
                            'created_at' => 'Created Date',
                            'id' => 'Order ID',
                            'product_name' => 'Product',
                            'amount' => 'Amount',
                            'payment_status' => 'Status',
                        ] as $value => $label)

                            <option
                                value="{{ $value }}"
                                {{ $sort === $value ? 'selected' : '' }}
                            >
                                {{ $label }}
                            </option>

                        @endforeach

                    </select>

                </div>

                <div class="col-md-3">

                    <label class="form-label">
                        Direction
                    </label>

                    <select
                        name="direction"
                        class="form-select"
                    >

                        <option
                            value="desc"
                            {{ $direction === 'desc' ? 'selected' : '' }}
                        >
                            Descending
                        </option>

                        <option
                            value="asc"
                            {{ $direction === 'asc' ? 'selected' : '' }}
                        >
                            Ascending
                        </option>

                    </select>

                </div>

                <div class="col-md-3 d-flex align-items-end">

                    <button
                        type="submit"
                        class="btn btn-dark w-100"
                    >
                        Apply Filters
                    </button>

                </div>

                <div class="col-md-3 d-flex align-items-end">

                    <a
                        href="{{ route('orders') }}"
                        class="btn btn-outline-secondary w-100"
                    >
                        Clear
                    </a>

                </div>

            </form>

            @if($orders->count())

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-light">

                            <tr>

                                <th>ID</th>
                                <th>Product</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Action</th>

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
                                        {{ $order->created_at->format('d M Y H:i') }}
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