<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model
{
    protected $fillable = [
        'event_id',
        'event_type',
        'event_created_at',
        'status',
        'error_message',
        'attempts',
        'stripe_session_id',
        'payment_status',
        'payload',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
        'event_created_at' => 'datetime',
    ];

    /**
     * Bootstrap status class.
     */
    public function statusClass(): string
    {
        return match ($this->status) {
            'processed' => 'success',
            'failed' => 'danger',
            'unmatched' => 'secondary',
            'received' => 'warning',
            default => 'dark',
        };
    }
}