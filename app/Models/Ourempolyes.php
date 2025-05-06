<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ourempolyes extends Model
{
    protected $table="customerfeedbacks";
    protected $guarded=[];
    protected $casts=[
        "rating"=>"float"
    ];

}
