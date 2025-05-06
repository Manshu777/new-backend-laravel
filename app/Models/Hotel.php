<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Hotel extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'hotels';

    protected $fillable = [
        'name',
        'email',
        'password',
        "slug",
        'phone',
        'useractive',
        'is_active', 
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Relationship with HotelDetails model.
     */
    public function hotelDetails()
    {
        return $this->hasMany(HotelDetails::class, 'hotel_id');
    }
}
