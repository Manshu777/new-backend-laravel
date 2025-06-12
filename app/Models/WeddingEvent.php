<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeddingEvent extends Model
{
    use HasFactory;
    protected $table = 'weddings';

    protected $fillable = [
        'host_name',
        'email',
        'phone',
        'city',
        'state',
        'address',
        'bride_name',
        'groom_name',
        'wedding_title',
        'wedding_description',
        'start_date',
        'end_date',
        'venue',
        'guests',
        'languages',
        'experience_type',
        'price_per_guest',
        'consent',
    ];
}