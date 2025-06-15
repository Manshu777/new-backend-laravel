<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DestinationCity extends Model
{
    protected $table = 'destinations_cities';

    protected $fillable = [
        'destination_id',
        'city_name',
        'state_province',
        'country_code',
        'country_name',
        'type',
        'created_at',
        'updated_at',
    ];
}
