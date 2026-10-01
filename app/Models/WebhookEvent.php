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
        'is_simulated',
        'retry_count',
        'signature_valid',
        'headers_json',
    ];

    protected $casts = [
        'payload' => 'array',
        'headers_json' => 'array',
        'processed_at' => 'datetime',
        'event_created_at' => 'datetime',
        'is_simulated' => 'boolean',
        'signature_valid' => 'boolean',
    ];

    /**
     * Bootstrap status class.
     */
    public function statusClass(): string
    {
        return match ($this->status) {
            'processed', 'replayed' => 'success',
            'failed' => 'danger',
            'unmatched' => 'secondary',
            'received', 'simulated' => 'warning',
            default => 'dark',
        };
    }
}