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
        "user_id",
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
    ];

    
    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'price' => 'decimal:2',
        'refund' => 'boolean',
       
    ];

    
    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'user_id', 'id')->withDefault([
    //         'name' => 'Deleted User',
    //         'number' => null,
    //     ]);
    // }
}
