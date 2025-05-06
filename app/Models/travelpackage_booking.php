<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class travelpackage_booking extends Model
{
    protected $fillable=[
        "name","email","mobile_number","user_id","package_id","package_name","date","age","passanger"
            ];
}
