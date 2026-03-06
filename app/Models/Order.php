<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'product_name',
        'amount',
        'stripe_session_id',
        'payment_status'
    ];
}