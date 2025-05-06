<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PopularHotels extends Model
{
    use HasFactory;
    protected $table="popular__destinations_hotels";
    protected $fillable=[
        "from",
        "from_code",
        "dis",
    ];
    
}
