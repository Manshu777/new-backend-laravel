<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RazorpayTransaction extends Model
{
    protected $fillable = [
        'order_id',
        'transaction_id',
        'amount',
        'currency',
        'receipt',
        'user_name',
        'user_email',
        'user_phone',
        'status',
    ];

    protected $casts = [
        'amount' => 'float',
    ];
}
