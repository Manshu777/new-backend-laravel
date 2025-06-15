<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DestinationHotel extends Model
{
    protected $table = 'destinations_hotels';

    protected $fillable = [
        'destination_id',
        'hotel_name',
        'city_name',
        'state_province',
        'country_code',
        'country_name',
        'type',
        'created_at',
        'updated_at',
    ];
}
