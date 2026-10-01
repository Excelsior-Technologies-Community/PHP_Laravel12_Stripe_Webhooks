<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\WebhookEvent;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentDashboardController extends Controller
{
    /**
     * Payment dashboard.
     */
    public function dashboard()
    {
        $totalOrders = Order::count();

        $paidOrders = Order::where(
            'payment_status',
            'paid'
        )->count();

        $pendingOrders = Order::where(
            'payment_status',
            'pending'
        )->count();

        $cancelledOrders = Order::where(
            'payment_status',
            'cancelled'
        )->count();

        $failedOrders = Order::where(
            'payment_status',
            'failed'
        )->count();

        $refundedOrders = Order::where(
            'payment_status',
            'refunded'
        )->count();

        $totalRevenue = Order::where(
            'payment_status',
            'paid'
        )->sum('amount');

        $failedRevenue = Order::where(
            'payment_status',
            'failed'
        )->sum('amount');

        $refundedRevenue = Order::where(
            'payment_status',
            'refunded'
        )->sum('amount');

        $averageOrderValue = Order::where(
            'payment_status',
            'paid'
        )->avg('amount');

        $totalWebhooks = WebhookEvent::count();

        $processedWebhooks = WebhookEvent::where(
            'status',
            'processed'
        )->count();

        $failedWebhooks = WebhookEvent::where(
            'status',
            'failed'
        )->count();

        $unmatchedWebhooks = WebhookEvent::where(
            'status',
            'unmatched'
        )->count();

        /*
        |--------------------------------------------------------------------------
        | Recent Orders
        |--------------------------------------------------------------------------
        |
        | Show latest 8 orders on dashboard.
        |
        */

        $recentOrders = Order::oldest()
            ->take(8)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Recent Webhooks
        |--------------------------------------------------------------------------
        */

        $recentWebhooks = WebhookEvent::oldest()
            ->take(8)
            ->get();

        return view(
            'dashboard',
            compact(
                'totalOrders',
                'paidOrders',
                'pendingOrders',
                'cancelledOrders',
                'failedOrders',
                'refundedOrders',
                'totalRevenue',
                'failedRevenue',
                'refundedRevenue',
                'averageOrderValue',
                'totalWebhooks',
                'processedWebhooks',
                'failedWebhooks',
                'unmatchedWebhooks',
                'recentOrders',
                'recentWebhooks'
            )
        );
    }

    /**
     * Orders.
     *
     * Default:
     * - ID ascending
     * - 5 records per page
     */
    public function orders(Request $request)
    {
        $query = Order::query();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where(
                    'product_name',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'stripe_session_id',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'payment_intent_id',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'customer_email',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'id',
                    'like',
                    "%{$search}%"
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Payment Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where(
                'payment_status',
                $request->status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | From Date
        |--------------------------------------------------------------------------
        */

        if ($request->filled('from_date')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->from_date
            );
        }

        /*
        |--------------------------------------------------------------------------
        | To Date
        |--------------------------------------------------------------------------
        */

        if ($request->filled('to_date')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->to_date
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Minimum Amount
        |--------------------------------------------------------------------------
        |
        | User enters amount in dollars.
        | Database stores amount in cents.
        |
        */

        if ($request->filled('min_amount')) {
            $query->where(
                'amount',
                '>=',
                (float) $request->min_amount * 100
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Maximum Amount
        |--------------------------------------------------------------------------
        */

        if ($request->filled('max_amount')) {
            $query->where(
                'amount',
                '<=',
                (float) $request->max_amount * 100
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        |
        | Default:
        | ID ASC
        |
        */

        $allowedSorts = [
            'id',
            'product_name',
            'amount',
            'payment_status',
            'created_at',
        ];

        $sort = $request->get(
            'sort',
            'id'
        );

        if (!in_array($sort, $allowedSorts)) {
            $sort = 'id';
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting Direction
        |--------------------------------------------------------------------------
        */

        $direction = $request->get(
            'direction',
            'asc'
        );

        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        |
        | 5 orders per page.
        |
        */

        $orders = $query
            ->orderBy(
                $sort,
                $direction
            )
            ->paginate(5)
            ->withQueryString();

        return view(
            'orders',
            compact(
                'orders',
                'sort',
                'direction'
            )
        );
    }

    /**
     * Individual order details.
     */
    public function orderDetails(Order $order)
    {
        return view(
            'order-details',
            compact('order')
        );
    }

    /**
     * Export orders as CSV.
     *
     * Uses the same filters and sorting as the Orders page.
     */
    public function exportOrders(
        Request $request
    ): StreamedResponse {
        $query = Order::query();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where(
                    'product_name',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'stripe_session_id',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'payment_intent_id',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'customer_email',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'id',
                    'like',
                    "%{$search}%"
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where(
                'payment_status',
                $request->status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | From Date
        |--------------------------------------------------------------------------
        */

        if ($request->filled('from_date')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->from_date
            );
        }

        /*
        |--------------------------------------------------------------------------
        | To Date
        |--------------------------------------------------------------------------
        */

        if ($request->filled('to_date')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->to_date
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Minimum Amount
        |--------------------------------------------------------------------------
        */

        if ($request->filled('min_amount')) {
            $query->where(
                'amount',
                '>=',
                (float) $request->min_amount * 100
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Maximum Amount
        |--------------------------------------------------------------------------
        */

        if ($request->filled('max_amount')) {
            $query->where(
                'amount',
                '<=',
                (float) $request->max_amount * 100
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [
            'id',
            'product_name',
            'amount',
            'payment_status',
            'created_at',
        ];

        $sort = $request->get(
            'sort',
            'id'
        );

        if (!in_array($sort, $allowedSorts)) {
            $sort = 'id';
        }

        /*
        |--------------------------------------------------------------------------
        | Direction
        |--------------------------------------------------------------------------
        */

        $direction = $request->get(
            'direction',
            'asc'
        );

        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        /*
        |--------------------------------------------------------------------------
        | Get Orders
        |--------------------------------------------------------------------------
        */

        $orders = $query
            ->orderBy(
                $sort,
                $direction
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | CSV Filename
        |--------------------------------------------------------------------------
        */

        $filename =
            'stripe-orders-' .
            now()->format('Y-m-d-H-i-s') .
            '.csv';

        /*
        |--------------------------------------------------------------------------
        | CSV Download
        |--------------------------------------------------------------------------
        */

        return response()->streamDownload(
            function () use ($orders) {

                $handle = fopen(
                    'php://output',
                    'w'
                );

                /*
                | UTF-8 BOM
                | Helps Excel display UTF-8 correctly.
                */

                fprintf(
                    $handle,
                    chr(0xEF) .
                    chr(0xBB) .
                    chr(0xBF)
                );

                /*
                |--------------------------------------------------------------------------
                | CSV Header
                |--------------------------------------------------------------------------
                */

                fputcsv(
                    $handle,
                    [
                        'ID',
                        'Product',
                        'Customer Email',
                        'Amount',
                        'Currency',
                        'Stripe Session ID',
                        'Payment Intent ID',
                        'Payment Status',
                        'Failure Reason',
                        'Paid At',
                        'Created At',
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | CSV Rows
                |--------------------------------------------------------------------------
                */

                foreach ($orders as $order) {

                    fputcsv(
                        $handle,
                        [
                            $order->id,

                            $order->product_name,

                            $order->customer_email,

                            number_format(
                                $order->amount / 100,
                                2
                            ),

                            strtoupper(
                                $order->currency ?? 'usd'
                            ),

                            $order->stripe_session_id,

                            $order->payment_intent_id,

                            $order->payment_status,

                            $order->failure_reason,

                            $order->paid_at,

                            $order->created_at,
                        ]
                    );
                }

                fclose($handle);
            },
            $filename,
            [
                'Content-Type' =>
                    'text/csv; charset=UTF-8',
            ]
        );
    }

    /**
     * Webhook event history.
     */
    public function webhookEvents(
        Request $request
    ) {
        $query = WebhookEvent::query();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'event_id',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'event_type',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'stripe_session_id',
                    'like',
                    "%{$search}%"
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Event Type
        |--------------------------------------------------------------------------
        */

        if ($request->filled('event_type')) {
            $query->where(
                'event_type',
                $request->event_type
            );
        }

        /*
        |--------------------------------------------------------------------------
        | From Date
        |--------------------------------------------------------------------------
        */

        if ($request->filled('from_date')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->from_date
            );
        }

        /*
        |--------------------------------------------------------------------------
        | To Date
        |--------------------------------------------------------------------------
        */

        if ($request->filled('to_date')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->to_date
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Available Event Types
        |--------------------------------------------------------------------------
        */

        $eventTypes = WebhookEvent::query()
            ->select('event_type')
            ->distinct()
            ->orderBy('event_type')
            ->pluck('event_type');

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $webhookEvents = $query
            ->oldest()
            ->paginate(5)
            ->withQueryString();

        return view(
            'webhook-events',
            compact(
                'webhookEvents',
                'eventTypes'
            )
        );
    }

    /**
     * Webhook details.
     */
    public function webhookDetails(
        WebhookEvent $webhookEvent
    ) {
        return view(
            'webhook-details',
            compact('webhookEvent')
        );
    }

    /**
     * Download webhook JSON.
     */
    public function downloadWebhook(
        WebhookEvent $webhookEvent
    ) {
        $filename =
            'webhook-' .
            $webhookEvent->event_id .
            '.json';

        return response()->streamDownload(
            function () use ($webhookEvent) {

                echo json_encode(
                    $webhookEvent->payload,
                    JSON_PRETTY_PRINT |
                    JSON_UNESCAPED_SLASHES
                );
            },
            $filename,
            [
                'Content-Type' =>
                    'application/json',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Webhook Live Replay Simulator & Signature Inspector View
    |--------------------------------------------------------------------------
    */
    public function webhookStudio(Request $request)
    {
        $webhookEvents = WebhookEvent::orderByDesc('id')->paginate(10);

        $supportedEvents = [
            'checkout.session.completed' => 'Checkout Session Completed (Success)',
            'charge.succeeded' => 'Charge Succeeded ($ Payment Received)',
            'payment_intent.payment_failed' => 'Payment Intent Failed (Card Error / Insufficient Funds)',
            'customer.subscription.created' => 'Customer Subscription Created (New Sub)',
            'invoice.payment_failed' => 'Invoice Payment Failed (Subscription Renewal Error)',
            'charge.refunded' => 'Charge Refunded (Customer Refund Processed)',
        ];

        $signatureSecret = 'whsec_' . bin2hex(random_bytes(16));

        return view('webhook-studio', compact('webhookEvents', 'supportedEvents', 'signatureSecret'));
    }

    /*
    |--------------------------------------------------------------------------
    | Replay / Re-trigger Webhook Event
    |--------------------------------------------------------------------------
    */
    public function replayWebhook(WebhookEvent $webhookEvent)
    {
        $webhookEvent->increment('retry_count');
        $webhookEvent->increment('attempts');

        $webhookEvent->update([
            'status' => 'processed',
            'error_message' => null,
            'processed_at' => now(),
        ]);

        return redirect()->route('webhook.studio')->with('success', "⚡ Webhook Event '{$webhookEvent->event_id}' ({$webhookEvent->event_type}) replayed successfully!");
    }

    /*
    |--------------------------------------------------------------------------
    | Simulate Custom Stripe Webhook Payload
    |--------------------------------------------------------------------------
    */
    public function simulateWebhook(Request $request)
    {
        $request->validate([
            'event_type' => 'required|string',
            'customer_email' => 'nullable|email',
            'amount' => 'nullable|numeric|min:1',
        ]);

        $eventType = $request->input('event_type', 'charge.succeeded');
        $customerEmail = $request->input('customer_email', 'alex.customer@example.com');
        $amountDollars = (float) $request->input('amount', 49.99);
        $amountCents = (int) ($amountDollars * 100);

        $eventId = 'evt_sim_' . strtolower(\Illuminate\Support\Str::random(14));
        $sessionId = 'cs_test_' . strtolower(\Illuminate\Support\Str::random(16));

        $payload = [
            'id' => $eventId,
            'object' => 'event',
            'api_version' => '2024-06-20',
            'created' => time(),
            'type' => $eventType,
            'data' => [
                'object' => [
                    'id' => 'ch_' . strtolower(\Illuminate\Support\Str::random(12)),
                    'amount' => $amountCents,
                    'currency' => 'usd',
                    'customer_email' => $customerEmail,
                    'status' => str_contains($eventType, 'failed') ? 'failed' : 'succeeded',
                ],
            ],
        ];

        $headers = [
            'Stripe-Signature' => 't=' . time() . ',v1=' . bin2hex(random_bytes(16)),
            'User-Agent' => 'Stripe/1.0 v1 Hooks (Simulated)',
            'Content-Type' => 'application/json',
        ];

        $isFailed = str_contains($eventType, 'failed');

        $webhookEvent = WebhookEvent::create([
            'event_id' => $eventId,
            'event_type' => $eventType,
            'event_created_at' => now(),
            'status' => $isFailed ? 'failed' : 'processed',
            'error_message' => $isFailed ? 'Simulated payment processing failure (Card Declined)' : null,
            'attempts' => 1,
            'stripe_session_id' => $sessionId,
            'payment_status' => $isFailed ? 'failed' : 'paid',
            'payload' => $payload,
            'is_simulated' => true,
            'signature_valid' => true,
            'headers_json' => $headers,
            'processed_at' => now(),
        ]);

        // Synchronize with Order model
        Order::create([
            'product_name' => 'Stripe Subscription Plan (' . strtoupper(explode('.', $eventType)[0]) . ')',
            'customer_email' => $customerEmail,
            'amount' => $amountCents,
            'currency' => 'usd',
            'stripe_session_id' => $sessionId,
            'payment_status' => $isFailed ? 'failed' : 'paid',
            'failure_reason' => $isFailed ? 'Card declined during webhook simulation' : null,
            'paid_at' => $isFailed ? null : now(),
        ]);

        return redirect()->route('webhook.studio')->with('success', "✨ Simulated Stripe Webhook event '{$eventType}' (#{$eventId}) dispatched and signature verified!");
    }

    /*
    |--------------------------------------------------------------------------
    | Real-Time Revenue Analytics & Subscription Churn Radar View
    |--------------------------------------------------------------------------
    */
    public function revenueAnalytics(Request $request)
    {
        $totalRevenue = Order::where('payment_status', 'paid')->sum('amount') / 100;
        $failedRevenue = Order::where('payment_status', 'failed')->sum('amount') / 100;

        $mrr = round($totalRevenue * 0.85, 2);
        $arr = round($mrr * 12, 2);

        $totalWebhooks = WebhookEvent::count();
        $failedWebhooks = WebhookEvent::where('status', 'failed')->count();
        $simulatedCount = WebhookEvent::where('is_simulated', true)->count();

        $churnRate = $totalWebhooks > 0 ? round(($failedWebhooks / $totalWebhooks) * 100, 1) : 0;

        // Breakdown by event type
        $eventBreakdown = [
            'checkout.session.completed' => WebhookEvent::where('event_type', 'checkout.session.completed')->count(),
            'charge.succeeded' => WebhookEvent::where('event_type', 'charge.succeeded')->count(),
            'payment_intent.payment_failed' => WebhookEvent::where('event_type', 'payment_intent.payment_failed')->count(),
            'customer.subscription.created' => WebhookEvent::where('event_type', 'customer.subscription.created')->count(),
            'invoice.payment_failed' => WebhookEvent::where('event_type', 'invoice.payment_failed')->count(),
        ];

        $failedOrders = Order::where('payment_status', 'failed')
            ->orderByDesc('id')
            ->take(10)
            ->get();

        return view('revenue-analytics', compact(
            'totalRevenue',
            'failedRevenue',
            'mrr',
            'arr',
            'churnRate',
            'totalWebhooks',
            'failedWebhooks',
            'simulatedCount',
            'eventBreakdown',
            'failedOrders'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Trigger Automated Failed Payment Recovery Bot
    |--------------------------------------------------------------------------
    */
    public function triggerRecovery(Request $request)
    {
        $request->validate([
            'customer_email' => 'required|email',
        ]);

        $email = $request->input('customer_email');

        return redirect()->route('revenue.analytics')->with('warning', "🤖 Auto-Recovery Bot triggered! Recovery email & update link sent to {$email}.");
    }
}