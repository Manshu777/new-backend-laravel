<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cruise extends Model
{
    use HasFactory;
    protected $fillable=[
"name",
"email",
"request_date",
"pickup_des",
"drop_des",
"booking_date",
"additional_notes",


    ];




    
}
