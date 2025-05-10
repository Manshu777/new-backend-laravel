<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
      protected $fillable = [
        'city_name',
        'country_code',
        'country_name',
        'destination_id',
        'state_province',
        'type',
    ];
}
