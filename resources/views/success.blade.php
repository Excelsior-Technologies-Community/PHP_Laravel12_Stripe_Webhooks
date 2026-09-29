<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Payment Successful</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<div class="container py-5">

    <div
        class="card shadow-sm border-0 mx-auto"
        style="max-width: 650px;"
    >

        <div class="card-body p-5 text-center">

            <div class="display-4 mb-3">
                ✓
            </div>

            <h2 class="text-success">
                Payment Successful
            </h2>

            <p class="text-muted">
                Your Stripe payment was completed successfully.
            </p>

            @if($order)

                <div class="alert alert-success text-start mt-4">

                    <strong>
                        Order #{{ $order->id }}
                    </strong>

                    <br>

                    Product:
                    {{ $order->product_name }}

                    <br>

                    Amount:
                    {{ $order->formattedAmount() }}

                    @if($order->customer_email)

                        <br>

                        Email:
                        {{ $order->customer_email }}

                    @endif

                </div>

            @endif

            <div class="mt-4">

                <a
                    href="{{ route('dashboard') }}"
                    class="btn btn-dark"
                >
                    Dashboard
                </a>

                <a
                    href="{{ route('orders') }}"
                    class="btn btn-outline-dark"
                >
                    View Orders
                </a>

            </div>

        </div>

    </div>

</div>

</body>

</html>