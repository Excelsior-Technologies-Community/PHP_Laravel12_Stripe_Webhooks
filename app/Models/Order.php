<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'product_name',
        'customer_email',
        'amount',
        'currency',
        'stripe_session_id',
        'payment_intent_id',
        'payment_status',
        'failure_reason',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'paid_at' => 'datetime',
    ];

    /**
     * Amount formatted as currency.
     */
    public function formattedAmount(): string
    {
        return strtoupper($this->currency ?? 'USD')
            . ' '
            . number_format($this->amount / 100, 2);
    }

    /**
     * Bootstrap-style status class.
     */
    public function statusClass(): string
    {
        return match ($this->payment_status) {
            'paid' => 'success',
            'pending' => 'warning',
            'cancelled' => 'secondary',
            'failed' => 'danger',
            'refunded' => 'info',
            default => 'dark',
        };
    }
}