<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RazorpayOrder extends Model
{
    protected $fillable = [
        'order_id',
        'booking_pnr',
        'amount',
        'currency',
        'receipt',
        'status',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

}
