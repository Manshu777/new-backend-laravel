<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holidayspackage extends Model
{
    use HasFactory;


    protected $table = 'holidayspackages';


    protected $fillable = [
        'name',
        'location',
        'duration',
        'traveloptions',
        'highlights',
        'inclusions',
        'exclusions',
        'rating',
        'reviews',
        'images',
        'cancellationPolicy',
        'itinerary',
        'additionalInfo',
    ];


    protected $casts = [
        'traveloptions' => 'array',
        'highlights' => 'array',
        'inclusions' => 'array',
        'exclusions' => 'array',
        'reviews' => 'array',
        'images' => 'array',
        'cancellationPolicy' => 'array',
        'itinerary' => 'array',
        'additionalInfo' => 'array',
    ];
}
