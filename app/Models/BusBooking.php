<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusBooking extends Model
{

        protected $table = 'booking_confirmations';

         protected $fillable = [
        'trace_id',
        'booking_status',
        'invoice_amount',
        'invoice_number',
        'bus_id',
        'ticket_no',
        'travel_operator_pnr',
        'passenger_details',
    ];

    protected $casts = [
        'passenger_details' => 'array',
    ];
    
}
