<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class topPort extends Model
{
    use HasFactory;
    protected $fillable = ['top_airports', 'country'];

    protected $casts = [
        'top_airports' => 'array',
    ];
}
