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
}