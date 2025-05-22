<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tbohotelcodelist extends Model
{
    protected $fillable = [
        'hotel_code',
        'hotel_name',
        'latitude',
        'longitude',
        'hotel_rating',
        'address',
        'country_name',
        'country_code',
        'city_name',
        'expires_at',
    ];
}
