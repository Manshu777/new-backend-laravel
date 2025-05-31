<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookflights extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bookflights';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'user_name',
        'user_number',
        'flight_name',
        'flight_number',
        'departure_from',
        'arrival_to',
        'flight_date',
        'date_of_booking',
        'return_date',
        'initial_response',
        'refund',
        'response',
        
        'token',
        'trace_id',
        'user_ip',
        'pnr',
        'booking_id',
        'username',
        'phone_number',
        'pdf_path',
'ticket_status',
'airline_code',
'departure_time',
'arrival_time',
'duration',
'fare',
'currency',

'commission_earned',
        'segments',

    ];
    

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'flight_date' => 'datetime',
        'date_of_booking' => 'datetime',
        'return_date' => 'datetime',
        'refund' => 'boolean',
        'segments' => 'array',
    ];

    /**
     * Relationship to the user (apkatripusers).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->withDefault([
            'name' => 'Deleted User',
            'number' => null,
        ]);
    }
}
