<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class HotelCity extends Model
{
    // hotelcities;
        protected $table="hotelcities";
    protected $fillable = ['code', 'name', 'country_code', 'created_at', 'updated_at'];
}