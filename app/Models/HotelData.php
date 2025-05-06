<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelData extends Model
{
    protected $table = 'hotel_data';
    protected $fillable = ['city_code', 'hotel_code', 'hotel_details', 'search_results'];
    protected $casts = [
        'hotel_details' => 'array',
        'search_results' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
