<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookedhotels extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bookedhotels';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'hotel_id',
        'user_name',
        'user_number',
        'hotel_name',
        'location',
        'address',
        'check_in_date',
        'check_out_date',
        'room_type',
        'price',
        'date_of_booking',
        'initial_response',
        'refund',
        'response',
        'tokenid',
        'traceid',
        'bookingId',
        'pnr',
        'enduserip',
        'hotel_booking_status',
        'confirmation_no',
        'net_amount',
        'last_cancellation_date',
        'star_rating',
        'address_line1',
        'city',
        'country_code',
        'no_of_rooms',
        'booking_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'last_cancellation_date' => 'date',
        'booking_date' => 'datetime',
        'price' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'refund' => 'boolean',
        'star_rating' => 'integer',
        'no_of_rooms' => 'integer',
    ];

    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'user_id', 'id')->withDefault([
    //         'name' => 'Deleted User',
    //         'number' => null,
    //     ]);
    // }
}