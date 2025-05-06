<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class roomreg extends Model
{
    use HasFactory;

    protected $table = 'room_regs';

    protected $fillable = [
        'hotel_details_id',
        'room_type',
        'size',
        'bed_type',
        'price',
        'max_occupancy',
        'room_ava',
        'features',
        'room_des',
        'additional_serv',
        'image',
        'hotel_reg_new_id',
    ];

    protected $casts = [
        'features' => 'array',
        'image' => 'array',
        'additional_serv' => 'array',
    ];

    /**
     * Relationship with HotelDetails.
     */
    // public function hotelDetails()
    // {
    //     return $this->belongsTo(HotelDetails::class, 'hotel_details_id');
    // }



    
    // protected static function booted()
    // {
    //     static::addGlobalScope('hotel', function (Builder $builder) {
    //         if (auth()->check()) {

    //             $builder->where('hotel_details_id', auth()->user()->hotel_details_id);
    //         }
    //     });
    // }
}
