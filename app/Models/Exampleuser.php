<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exampleuser extends Model
{
    use HasFactory;

    protected $fillable = ['country','top_airports'];

    protected $casts = [
        'top_airports' => 'array',
    ];
}
