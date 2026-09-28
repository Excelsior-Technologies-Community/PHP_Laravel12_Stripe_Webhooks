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

    <div class="card shadow-sm border-0 text-center mx-auto"
         style="max-width: 600px;">

        <div class="card-body p-5">

            <div class="display-4 mb-3">
                ✓
            </div>

            <h2 class="text-success">
                Payment Successful
            </h2>

            <p class="text-muted">
                Your Stripe payment was completed successfully.
            </p>

            <div class="mt-4">

                <a
                    href="{{ route('dashboard') }}"
                    class="btn btn-dark"
                >
                    Go to Dashboard
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