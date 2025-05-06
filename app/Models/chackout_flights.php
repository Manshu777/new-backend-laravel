<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class chackout_flights extends Model
{
    protected $primarykey="id";
    // protected $primaryKey="id";
public $incrementing=false;   
protected  $keyType="string";

use HasUlids;


protected $fillable=[
"user_id","flight_info","price"
];


protected $casts = [
    "flight_info" => "array"
];
}
