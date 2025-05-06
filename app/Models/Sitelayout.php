<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sitelayout extends Model
{
    
    protected $fillable=[
        "banner_image",
        "image1",
        "image2",
        "image3"
    ];

protected $caste=[
    "banner_image"=>"array",
];


}
