<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TBOHotelCode extends Model
{
     protected $table = 't_b_o_hotel_codes';

    protected $fillable = [
        'city_code',
        'hotel_code',
        'hotel_name',
        'latitude',
        'longitude',
        'hotel_rating',
        'address',
        'country_name',
        'country_code',
        'city_name',
    ];
}
