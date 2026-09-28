<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\WebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentDashboardController extends Controller
{
    /**
     * Payment dashboard
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

        $totalRevenue = Order::where(
            'payment_status',
            'paid'
        )->sum('amount');

        $totalWebhooks = WebhookEvent::count();

        $processedWebhooks = WebhookEvent::where(
            'status',
            'processed'
        )->count();

        $failedWebhooks = WebhookEvent::where(
            'status',
            'failed'
        )->count();

        $recentOrders = Order::latest()
            ->take(8)
            ->get();

        $recentWebhooks = WebhookEvent::latest()
            ->take(8)
            ->get();

        return view('dashboard', compact(
            'totalOrders',
            'paidOrders',
            'pendingOrders',
            'cancelledOrders',
            'totalRevenue',
            'totalWebhooks',
            'processedWebhooks',
            'failedWebhooks',
            'recentOrders',
            'recentWebhooks'
        ));
    }

    /**
     * Search, filter and paginate orders.
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
                    'id',
                    'like',
                    "%{$search}%"
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Status filter
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
        | Date filtering
        |--------------------------------------------------------------------------
        */

        if ($request->filled('from_date')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->from_date
            );
        }

        if ($request->filled('to_date')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->to_date
            );
        }

        $orders = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('orders', compact('orders'));
    }

    /**
     * Webhook event history.
     */
    public function webhookEvents(Request $request)
    {
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
        | Status filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        $webhookEvents = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view(
            'webhook-events',
            compact('webhookEvents')
        );
    }

    /**
     * Display individual webhook details.
     */
    public function webhookDetails(WebhookEvent $webhookEvent)
    {
        return view(
            'webhook-details',
            compact('webhookEvent')
        );
    }
}