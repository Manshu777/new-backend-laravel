<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Hoteldetails extends Model
{
    use HasFactory;

    protected $table = 'hotel_details';

    protected $fillable = [
        'property_name',
        'hotel_des',
        'hotel_img',
        'rating',
        'slug',
        'built_year',
        'accepting_since',
        'email',
        'number',
        'land_line',
        'address',
        'lat',
        'lang',
        'house_no',
        'locality',
        'pincode',
        'country',
        'state',
        'city',
        'terms',
        "price",
        'hotel_id',
    ];

    protected $casts = [
        'hotel_img' => 'array',
    ];

    protected $dates = [
        'built_year',
        'accepting_since',
    ];

    /**
     * Relationship with Hotel model.
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }

    /**
     * Relationship with RoomRegs.
     */
    public function roomRegs()
    {
        return $this->hasMany(RoomReg::class, 'hotel_details_id');
    }

    // protected static function booted()
    // {
    //     static::addGlobalScope('hotel', function (Builder $builder) {
    //         if (auth()->check()) {
    //             $builder->where('hotel_id', auth()->user()->id);
    //         }
    //     });
    // }
}
