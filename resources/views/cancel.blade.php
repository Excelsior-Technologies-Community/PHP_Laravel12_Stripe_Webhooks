<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Payment Cancelled</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<div class="container py-5">

    <div class="card shadow-sm border-0 text-center mx-auto"
         style="max-width: 600px;">

        <div class="card-body p-5">

            <div class="display-4 mb-3">
                !
            </div>

            <h2 class="text-danger">
                Payment Cancelled
            </h2>

            <p class="text-muted">
                The Stripe Checkout payment was cancelled.
            </p>

            <div class="mt-4">

                <a
                    href="{{ route('checkout') }}"
                    class="btn btn-primary"
                >
                    Try Again
                </a>

                <a
                    href="{{ route('dashboard') }}"
                    class="btn btn-outline-dark"
                >
                    Dashboard
                </a>

            </div>

        </div>

    </div>

</div>

</body>

</html>